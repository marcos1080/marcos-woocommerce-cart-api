<?php

class Category {
    function __construct($id, $parent, $name, $slug, $count) {
        $this->id = $id;
        $this->parentId = $parent;
        $this->name = $name;
        $this->slug = $slug;
        $this->count = $count;
        $this->children = array();
    }

    public function add($category) {
        array_push($this->children, $category);
    }
}