<?php
/**
 * Plugin Name: Marcos Woocommerce Client Connection Rest API Plugin
 * Description: Adds Rest API endpoints to retrieve client side data from Woocommerce.
 * Author: Mark Stuart
 * License: Apache2
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'MARCOS_WC_CLIENT_PLUGIN_FILE' ) ) {
    define( 'MARCOS_WC_CLIENT_PLUGIN_FILE', __FILE__ );
}

if ( !function_exists( 'marcos_woocommerce_client_rest_api_init' )) {
    function marcos_woocommerce_client_rest_api_init() {
        require_once dirname( MARCOS_WC_CLIENT_PLUGIN_FILE ) . '/includes/class-controller.php';
        $class = new Marcos_WC_REST_Client_Controller();
        add_filter( 'rest_api_init', array( $class, 'register_routes' ) );
    }
    add_action( 'init', 'marcos_woocommerce_client_rest_api_init' );
}