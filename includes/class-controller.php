<?php

include_once dirname( MARCOS_WC_CLIENT_PLUGIN_FILE ) . '/includes/woocommerce_API_connector.php';

class Marcos_WC_REST_Client_Controller {
	/**
	 * You can extend this class with
	 * WP_REST_Controller / WC_REST_Controller / WC_REST_Products_V2_Controller / WC_REST_CRUD_Controller etc.
	 * Found in packages/woocommerce-rest-api/src/Controllers/
	 */
	protected $namespace = 'wc/client/v1';

	function __construct() {
        $this->woocommerce_API = new WoocommerceAPIInterface();
    }

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/product',
			array(
				'methods' 				=> WP_REST_Server::READABLE,
				'callback' 				=> array( $this, 'get_product' ),
			)
		);
		register_rest_route(
			$this->namespace,
			'/products',
			array(
				'methods' 				=> WP_REST_Server::READABLE,
				'callback' 				=> array( $this, 'get_products' ),
			)
		);
	}

	public function get_product() {
		if ( isset( $_GET['slug'] ) ) {
			$data = $this->woocommerce_API->get("/products?slug={$_GET['slug']}")['body'];

			if ($data && count($data)) {
				$product = $data[0];
				return array(
					'id'				=> $product->id,
					'name'				=> $product->name,
					'slug'				=> $product->slug,
					'images'			=> $product->images,
					'price'				=> $product->price,
					'price_html'		=> $product->price_html,
					'description'		=> $product->description,
					'stock_quantity'	=> $product->stock_quantity,
					'stock_status'		=> $product->stock_status,
				);
			}
		}
		
		return new WP_Error( 'no_product', __('No product found'), array( 'status' => 404 ) );
	}

	public function get_products() {
		$page = isset( $_GET['page'] ) ? $_GET['page'] : 1;
		$params = ["page={$page}"];

		if ( isset( $_GET['per_page'] ) ) {
			array_push($params, "per_page={$_GET['per_page']}");
		}

		if ( isset( $_GET['orderby'] ) ) {
			array_push($params, "orderby={$_GET['orderby']}");
		}

		if ( isset( $_GET['order'] ) ) {
			array_push($params, "order={$_GET['order']}");
		}

		$categories = $this->woocommerce_API->get('/products/categories')['body'];
		if ( isset( $_GET['category'] ) ) {
			$category_slugs = explode(',', $_GET['category']);
			$category_ids = array();

			foreach ($categories as $category) {
				if (in_array($category->slug, $category_slugs)) {
					array_push($category_ids, $category->id);
				}
			}

			array_push($params, "category=".implode(',', $category_ids));
		}

		if ( isset( $_GET['include'] ) ) {
			array_push($params, "include={$_GET['include']}");
		}

		$endpoint = implode(['/products', implode($params, "&")], "?");
		$data = $this->woocommerce_API->get($endpoint);

		$return_products = [];
		foreach ($data['body'] as $key => $product) {
			array_push($return_products, array(
				'id'				=> $product->id,
				'name'				=> $product->name,
				'slug'				=> $product->slug,
				'images'			=> $product->images,
				'price'				=> $product->price,
				'price_html'		=> $product->price_html,
				'description'		=> $product->description,
				'stock_quantity'	=> $product->stock_quantity,
				'stock_status'		=> $product->stock_status,
			));
		}

		return array(
			'items'				=> $return_products,
			'categories'		=> $this->buildCategoriesObject($categories),
			'total'				=> $data['headers']['X-WP-Total'],
			'total-pages'		=> $data['headers']['X-WP-TotalPages'],
		);
	}

	private function buildCategoriesObject($data) {
		$categories = array();

		foreach ($data as $category) {
			$categories[$category->id] = array(
				'id'				=> $category->id,
				'parent'			=> $category->parent,
				'name'				=> $category->name,
				'slug'				=> $category->slug,
				'count'				=> $category->count,
			);
		}

		$nested_categories = array();

		foreach ($categories as $category) {
			if ($category['parent'] == 0) {
				$this->addChildrenToCategories($category, $categories);
				array_push($nested_categories, $category);
			}
		}

		return $nested_categories;
	}

	private function addChildrenToCategories(&$current_category, $indexedCategories) {
		$children = array();

		foreach ($indexedCategories as $category) {
			if ($category['parent'] == $current_category['id']) {
				$this->addChildrenToCategories($category, $indexedCategories);
				array_push($children, $category);
			}
		}

		$current_category += ['children' => $children];
	}
}