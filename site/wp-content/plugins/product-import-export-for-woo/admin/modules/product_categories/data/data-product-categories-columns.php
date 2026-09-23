<?php

if (!defined('WPINC')) {
    exit;
}

return apply_filters('taxonomies_csv_product_post_columns', array(
    'term_id' => 'term_id',
    'name' => 'name',
    'slug' => 'slug',
    'description' => 'description',
    'display_type' => 'display_type',
    'parent' => 'parent',
	'parent_slug' => 'parent_slug',
    'thumbnail' => 'thumbnail',
        ));
