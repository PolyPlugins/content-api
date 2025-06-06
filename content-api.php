<?php

/**
 * Plugin Name: Content API
 * Plugin URI: https://www.polyplugins.com/contact/
 * Description: Adds various endpoints to create content
 * Version: 1.0.11
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Author: Poly Plugins
 * Author URI: https://www.polyplugins.com
 * License: GPL3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

namespace PolyPlugins;

use Exception;
use WC_Product_Attribute;
use WC_Product_Simple;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class Content_API
{
  const REQUIRED_FIELDS = array('title', 'post_type', 'content', 'categories', 'tags', 'yoast');

  private $options;
  private $error = '';
  
  /**
   * Init
   *
   * @return void
   */
  public function init() {
    add_action('rest_api_init', array($this, 'register_endpoints'));
    add_action('admin_menu', array($this, 'register_settings_page'));
		add_action('admin_init', array($this, 'settings_page_init'));
    add_action('admin_notices', array($this, 'maybe_display_last_accessed_notice'));
  }
  
  /**
   * Register Endpoints
   *
   * @return void
   */
  public function register_endpoints() {
    register_rest_route('content-api/v1', '/post/', array(
      'methods' => 'POST',
      'callback' => array($this, 'create_post'),
      'permission_callback' => array($this, 'has_permission'), // Add your permission callback for security
    ));

    register_rest_route('content-api/v1', '/product/', array(
      'methods' => 'GET',
      'callback' => array($this, 'get_product'),
      'permission_callback' => array($this, 'has_permission'), // Add your permission callback for security
    ));

    register_rest_route('content-api/v1', '/product/', array(
      'methods' => 'PATCH',
      'callback' => array($this, 'update_product'),
      'permission_callback' => array($this, 'has_permission'), // Add your permission callback for security
    ));

    register_rest_route('content-api/v1', '/product/', array(
      'methods' => 'POST',
      'callback' => array($this, 'create_product'),
      'permission_callback' => array($this, 'has_permission'), // Add your permission callback for security
    ));

    register_rest_route('content-api/v1', '/product-category/', array(
      'methods' => 'GET',
      'callback' => array($this, 'get_product_category'),
      'permission_callback' => array($this, 'has_permission'), // Add your permission callback for security
    ));

    register_rest_route('content-api/v1', '/product-category/', array(
      'methods' => 'PATCH',
      'callback' => array($this, 'update_product_category'),
      'permission_callback' => array($this, 'has_permission'), // Add your permission callback for security
    ));

    register_rest_route('content-api/v1', '/product-category/', array(
      'methods' => 'POST',
      'callback' => array($this, 'create_product_category'),
      'permission_callback' => array($this, 'has_permission'), // Add your permission callback for security
    ));

    register_rest_route('content-api/v1', '/product-ids/', array(
      'methods' => 'GET',
      'callback' => array($this, 'get_all_product_ids'),
      'permission_callback' => array($this, 'has_permission'), // Add your permission callback for security
    ));

    register_rest_route('content-api/v1', '/product-categories/', array(
      'methods' => 'GET',
      'callback' => array($this, 'get_all_product_categories'),
      'permission_callback' => array($this, 'has_permission'), // Add your permission callback for security
    ));

    register_rest_route('content-api/v1', '/product/categories/', array(
      'methods' => 'GET',
      'callback' => array($this, 'get_product_categories'),
      'permission_callback' => array($this, 'has_permission'), // Add your permission callback for security
    ));

    register_rest_route('content-api/v1', '/product/categories/', array(
      'methods' => 'PATCH',
      'callback' => array($this, 'update_product_categories'),
      'permission_callback' => array($this, 'has_permission'), // Add your permission callback for security
    ));

    register_rest_route('content-api/v1', '/terms/', array(
      'methods' => 'GET',
      'callback' => array($this, 'get_terms'),
      'permission_callback' => array($this, 'has_permission'), // Add your permission callback for security
    ));

    register_rest_route('content-api/v1', '/terms/', array(
      'methods' => array('PATCH', 'PUT'),
      'callback' => array($this, 'update_terms'),
      'permission_callback' => array($this, 'has_permission'), // Add your permission callback for security
    ));

    register_rest_route('content-api/v1', '/attributes/', array(
      'methods' => 'GET',
      'callback' => array($this, 'get_attributes'),
      'permission_callback' => array($this, 'has_permission'), // Add your permission callback for security
    ));

    register_rest_route('content-api/v1', '/attributes/', array(
      'methods' => array('PATCH', 'PUT'),
      'callback' => array($this, 'update_attributes'),
      'permission_callback' => array($this, 'has_permission'), // Add your permission callback for security
    ));

    register_rest_route('content-api/v1', '/product/attributes/', array(
      'methods' => 'GET',
      'callback' => array($this, 'get_product_attributes'),
      'permission_callback' => array($this, 'has_permission'), // Add your permission callback for security
    ));

    register_rest_route('content-api/v1', '/product-brand/', array(
      'methods' => 'GET',
      'callback' => array($this, 'get_brand'),
      'permission_callback' => array($this, 'has_permission'), // Add your permission callback for security
    ));

    register_rest_route('content-api/v1', '/product-brand/', array(
      'methods' => 'PATCH',
      'callback' => array($this, 'update_brand'),
      'permission_callback' => array($this, 'has_permission'), // Add your permission callback for security
    ));

    register_rest_route('content-api/v1', '/product-brand/', array(
      'methods' => 'POST',
      'callback' => array($this, 'create_brand'),
      'permission_callback' => array($this, 'has_permission'), // Add your permission callback for security
    ));

    register_rest_route('content-api/v1', '/taxonomy/brand/', array(
      'methods' => 'GET',
      'callback' => array($this, 'get_brand'),
      'permission_callback' => array($this, 'has_permission'), // Add your permission callback for security
    ));

    register_rest_route('content-api/v1', '/taxonomy/brand/', array(
      'methods' => 'PATCH',
      'callback' => array($this, 'update_brand'),
      'permission_callback' => array($this, 'has_permission'), // Add your permission callback for security
    ));

    register_rest_route('content-api/v1', '/taxonomy/brand/', array(
      'methods' => 'POST',
      'callback' => array($this, 'create_brand'),
      'permission_callback' => array($this, 'has_permission'), // Add your permission callback for security
    ));

    register_rest_route('content-api/v1', '/product/brands/', array(
      'methods' => 'GET',
      'callback' => array($this, 'get_product_brands'),
      'permission_callback' => array($this, 'has_permission'), // Add your permission callback for security
    ));

    register_rest_route('content-api/v1', '/product/brands/', array(
      'methods' => 'PATCH',
      'callback' => array($this, 'update_product_brands'),
      'permission_callback' => array($this, 'has_permission'), // Add your permission callback for security
    ));
  }
  
  /**
   * Create post
   *
   * @param  mixed $request
   * @return void
   */
  public function create_post(WP_REST_Request $request) {
    $this->options = get_option('content_api_options_polyplugins');

    // Get the JSON data from the request
    $fields = $request->get_json_params();

    if ($this->data_missing($fields)) {
      return new WP_Error('data_missing', $this->get_error(), array('status' => 400));
    }

    $title          = isset($fields['title']) ? sanitize_text_field($fields['title']) : '';
    $content        = isset($fields['content']) ? wp_kses_post($fields['content']) : '';
    $featured_image = isset($fields['featured_image']) ? sanitize_url($fields['featured_image']) : '';
    $images         = isset($fields['images']) ? array_map('sanitize_url', $fields['images']) : '';
    $categories     = isset($fields['categories']) ? array_map('sanitize_text_field', $fields['categories']) : '';
    $tags           = isset($fields['tags']) ? array_map('sanitize_text_field', $fields['tags']) : '';
    $yoast          = isset($fields['yoast']) ? $fields['yoast'] : '';
    $post_type      = isset($fields['post_type']) ? sanitize_text_field($fields['post_type']) : 'post';

    // Prepare the post array
    $post_data = array(
      'post_title'   => $title,
      'post_content' => $this->replace_image_variables($content, $images),
      'post_status'  => 'draft',
      'post_type'    => $post_type,
      'post_author'  => 1,
    );

    // Insert the post into the database
    $post_id = wp_insert_post($post_data);

    // Now handle the images
    $media_library_urls = array();

    if (!empty($images)) {
      foreach ($images as $image_url) {
        // Check if the URL is already from the media library (starts with WordPress upload directory URL)
        $upload_dir = wp_get_upload_dir();

        if (strpos($image_url, $upload_dir['baseurl']) === false) {
          // If not from the media library, upload the image to the media library and attach it to the post
          $image_id = $this->upload_image_to_media_library($image_url, $post_id);

          if ($image_id && !is_wp_error($image_id)) {
            // Get the media library URL for the uploaded image
            $new_image_url = wp_get_attachment_url($image_id);
            if ($new_image_url) {
              $media_library_urls[] = $new_image_url;
            }
          }
        } else {
          // Already in the media library, so just use the existing URL
          $media_library_urls[] = $image_url;
        }
      }
    }

    // Now replace the $content images with media library URLs
    $content_with_media_urls = $this->replace_image_variables($content, $media_library_urls);

    // Update the post content with images replaced
    $post_data['post_content'] = $content_with_media_urls;

    if (is_wp_error($post_id)) {
      return new WP_Error('post_creation_failed', 'Failed to create post', array('status' => 500));
    }

    // Handle featured image
    if (!empty($featured_image)) {
      $image_id = $this->upload_image_to_media_library($featured_image, $post_id);
      
      if ($image_id && !is_wp_error($image_id)) {
        set_post_thumbnail($post_id, $image_id);
      }
    }

    // Ensure categories exist and get their IDs
    $category_ids = array();

    if ($post_type !== 'page') {
      foreach ($categories as $category_name) {
        $category_id = $this->maybe_create_category($category_name);

        if ($category_id) {
          $category_ids[] = $category_id;
        }
      }
    }

    // Assign categories to the post
    if (!empty($category_ids)) {
      wp_set_post_categories($post_id, $category_ids);
    }

    // Assign tags
    if (!empty($tags)) {
      wp_set_post_tags($post_id, $tags);
    }

    // Assign Yoast Meta Title
    if (isset($yoast['title'])) {
      update_post_meta($post_id, '_yoast_wpseo_title', sanitize_text_field($yoast['title']));
    }

    // Assign Yoast Meta Description
    if (isset($yoast['description'])) {
      update_post_meta($post_id, '_yoast_wpseo_metadesc', sanitize_text_field($yoast['description']));
    }

    // Assign Yoast Facebook Open Graph Title
    if (isset($yoast['premium']['social_appearance']['title'])) {
      update_post_meta($post_id, '_yoast_wpseo_opengraph-title', sanitize_text_field($yoast['premium']['social_appearance']['title']));
    }

    // Assign Yoast Facebook Open Graph Description
    if (isset($yoast['premium']['social_appearance']['description'])) {
      update_post_meta($post_id, '_yoast_wpseo_opengraph-description', sanitize_text_field($yoast['premium']['social_appearance']['description']));
    }

    // Assign Yoast Facebook Open Graph Image
    if (isset($yoast['premium']['social_appearance']['image'])) {
      update_post_meta($post_id, '_yoast_wpseo_opengraph-image', sanitize_url($yoast['premium']['social_appearance']['image']));
    }

    // Assign Yoast Facebook Open Graph Title
    if (isset($yoast['premium']['x']['title'])) {
      update_post_meta($post_id, '_yoast_wpseo_twitter-title', sanitize_text_field($yoast['premium']['x']['title']));
    }

    // Assign Yoast Facebook Open Graph Description
    if (isset($yoast['premium']['x']['description'])) {
      update_post_meta($post_id, '_yoast_wpseo_twitter-description', sanitize_text_field($yoast['premium']['x']['description']));
    }

    // Assign Yoast Facebook Open Graph Image
    if (isset($yoast['premium']['x']['image'])) {
      update_post_meta($post_id, '_yoast_wpseo_twitter-image', sanitize_url($yoast['premium']['x']['image']));
    }

    // Return success response
    return new WP_REST_Response(array(
      'success' => true,
      'post_id' => $post_id,
    ), 201);
  }
  
  /**
   * Get product
   *
   * @param  mixed $request
   * @return void
   */
  public function get_product(WP_REST_Request $request) {
    $this->options = get_option('content_api_options_polyplugins');

    
    $fields                    = $request->get_params();
    $product_id                = isset($fields['product_id']) && is_numeric($fields['product_id']) ? absint($fields['product_id']) : '';
    $sku                       = isset($fields['sku']) ? sanitize_text_field($fields['sku']) : '';
    $missing_description       = isset($fields['missing_description']) ? true : false;
    $missing_description_limit = isset($fields['missing_description_limit']) ? absint($fields['missing_description_limit']) : 100;

    if ($missing_description) {

      $product_ids = $this->get_product_ids_with_missing_descriptions($missing_description_limit);

      if (!is_array($product_ids) && count($product_ids) == 0) {
        return new WP_Error('no_products', "No products missing descriptions", array('status' => 404));
      }

      $products = array();

      foreach ($product_ids as $product_id) {
        $sku = get_post_meta($product_id, '_sku', true);
  
        if (!empty($sku)) {
          $products[] = array(
            'product_id' => $product_id,
            'sku'        => sanitize_text_field($sku)
          );
        }
      }

      return new WP_REST_Response($products, 200);
    }

    if (!$product_id && !$sku) {
      return new WP_Error('missing_identifier', 'Product ID or SKU is required', array('status' => 400));
    }

    if ($product_id) {
      if (!is_numeric($product_id)) {
        return new WP_Error('product_id_invalid', 'Product ID is invalid', array('status' => 400));
      }
    }

    if ($sku) {
      if ($sku !== sanitize_text_field($sku)) {
        return new WP_Error('sku_invalid', 'SKU is invalid', array('status' => 400));
      }
    }

    if ($product_id && $sku) {
      return new WP_Error('conflicting_identifiers', 'Both Product ID and SKU are provided. Please provide only one.', array('status' => 400));
    }

    // If SKU is provided, try to get product by SKU
    if ($sku) {
      $product_id_by_sku = wc_get_product_id_by_sku(sanitize_text_field($sku));

      if ($product_id_by_sku) {
        $product = wc_get_product($product_id_by_sku);
      } else {
        return new WP_Error('product_not_found', 'Product not found with provided SKU', array('status' => 404));
      }
    } elseif ($product_id) {
      $product = wc_get_product($product_id);
    }
    
    if (!isset($product) || !$product) {
      return new WP_Error('product_not_found', 'Product not found', array('status' => 404));
    }

    // Prepare product data response
    $product_id        = $product->get_id();
    $name              = $product->get_name();
    $status            = $product->get_status();
    $slug              = $product->get_slug();
    $description       = $product->get_description();
    $short_description = $product->get_short_description();
    $regular_price     = $product->get_regular_price();
    $sale_price        = $product->get_sale_price();
    $map_price         = $product->get_meta('_map_price');
    $cost              = $product->get_meta('_cost');
    $sku               = $product->get_sku();
    $upc               = $product->get_meta('_global_unique_id');
    $weight            = $product->get_weight();
    $stock_status      = $product->get_stock_status();
    $manage_stock      = $product->get_manage_stock();
    $stock_quantity    = $product->get_stock_quantity();
    $tags              = wp_get_post_terms($product->get_id(), 'product_tag', array('fields' => 'names'));
    $categories        = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'names'));
    $attributes   = array();
    $raw_attributes    = $product->get_attributes();
    $images            = array();
    $gallery_ids       = $product->get_gallery_image_ids();
    $yoast             = array(
      "title"       => sanitize_text_field($product->get_meta('_yoast_wpseo_title')),
      "description" => sanitize_text_field($product->get_meta('_yoast_wpseo_metadesc')),
      "premium"     => array(
        "social_appearance" => array(
          "title"       => sanitize_text_field($product->get_meta('_yoast_wpseo_opengraph-title')),
          "description" => sanitize_text_field($product->get_meta('_yoast_wpseo_opengraph-description')),
          "image"       => sanitize_url($product->get_meta('_yoast_wpseo_opengraph-image')),
        ),
        "x" => array(
          "title"       => sanitize_text_field($product->get_meta('_yoast_wpseo_twitter-title')),
          "description" => sanitize_text_field($product->get_meta('_yoast_wpseo_twitter-description')),
          "image"       => sanitize_url($product->get_meta('_yoast_wpseo_twitter-image')),
        )
      )
    );

    foreach ($raw_attributes as $raw_attribute) {
      if ($raw_attribute->is_taxonomy()) {
        $terms = wp_get_post_terms($product->get_id(), $raw_attribute->get_name(), array('fields' => 'names'));

        $attributes[] = array(
          'name'   => $raw_attribute->get_name(),
          'value'  => $terms,
        );
      } else {
        $attributes[] = array(
          'name'   => $raw_attribute->get_name(),
          'value'  => $raw_attribute->get_options(),
        );
      }
    }

    foreach ($gallery_ids as $id) {
      $image = wp_get_attachment_image_src($id, 'full');
      if ($image) {
        $images[] = $image[0];
      }
    }

    $featured_image  = wp_get_attachment_image_src($product->get_image_id(), 'full');

    if ($featured_image) {
      array_unshift($images, $featured_image[0]);
    }

    $featured_image    = wp_get_attachment_url($product->get_image_id());
    
    $response_data     = array(
      'product_id'        => $product_id ? absint($product_id) : 0,
      'name'              => $name ? sanitize_text_field($name) : '',
      'status'            => $status ? sanitize_text_field($status) : '',
      'slug'              => $slug ? sanitize_title($slug) : '',
      'description'       => $description ? wp_kses_post($description) : '',
      'short_description' => $short_description ? wp_kses_post($short_description) : '',
      'price'             => $regular_price ? floatval($regular_price) : '',
      'sale_price'        => $sale_price ? floatval($sale_price) : '',
      'map_price'         => $map_price ? floatval($map_price) : '',
      'cost'              => $cost ? floatval($cost) : '',
      'sku'               => $sku ? sanitize_text_field($sku) : '',
      'upc'               => $upc ? sanitize_text_field($upc) : '',
      'weight'            => $weight ? floatval($weight) : '',
      'stock_status'      => $stock_status ? sanitize_text_field($stock_status) : '',
      'manage_stock'      => $manage_stock ? true : false,
      'stock_quantity'    => $stock_quantity ? absint($product->get_stock_quantity()) : 0,
      'tags'              => $tags ? array_map('sanitize_text_field', $tags) : array(),
      'categories'        => $categories ? array_map('sanitize_text_field', $categories) : array(),
      'attributes'        => $attributes,
      'images'            => $images ? array_map('esc_url_raw', $images) : array(),
      'featured_image'    => $featured_image ? esc_url_raw($featured_image) : '',
      'yoast'             => $yoast,
    );

    return new WP_REST_Response($response_data, 200);
  }
  
  /**
   * Update product
   *
   * @param  mixed $request
   * @return void
   */
  public function update_product(WP_REST_Request $request) {
    $this->options = get_option('content_api_options_polyplugins');
    
    $fields            = $request->get_json_params();
    $product_id        = isset($fields['product_id']) ? absint($fields['product_id']) : 0;
    $name              = isset($fields['name']) ? sanitize_text_field($fields['name']) : '';
    $status            = isset($fields['status']) ? sanitize_text_field($fields['status']) : '';
    $slug              = isset($fields['slug']) ? sanitize_title($fields['slug']) : '';
    $description       = isset($fields['description']) ? wp_kses_post($fields['description']) : '';
    $short_description = isset($fields['short_description']) ? wp_kses_post($fields['short_description']) : '';
    $price             = isset($fields['price']) ? floatval($fields['price']) : '';
    $sale_price        = isset($fields['sale_price']) ? floatval($fields['sale_price']) : '';
    $map_price         = isset($fields['map_price']) ? floatval($fields['map_price']) : '';
    $cost              = isset($fields['cost']) ? floatval($fields['cost']) : '';
    $sku               = isset($fields['sku']) ? sanitize_text_field($fields['sku']) : '';
    $upc               = isset($fields['upc']) ? sanitize_text_field($fields['upc']) : '';
    $weight            = isset($fields['weight']) ? floatval($fields['weight']) : '';
    $stock_status      = isset($fields['stock_status']) ? sanitize_text_field($fields['stock_status']) : '';
    $manage_stock      = isset($fields['manage_stock']) ? wc_string_to_bool($fields['manage_stock']) : null;
    $stock_quantity    = isset($fields['stock_quantity']) ? intval($fields['stock_quantity']) : false;
    $tags              = isset($fields['tags']) && is_array($fields['tags']) ? array_map('sanitize_text_field', $fields['tags']) : array();
    $categories        = isset($fields['categories']) && is_array($fields['categories']) ? array_map('sanitize_text_field', $fields['categories']) : array();
    $attributes        = isset($fields['attributes']) ? $fields['attributes'] : array();
    $featured_image    = isset($fields['featured_image']) ? sanitize_url($fields['featured_image']) : '';
    $images            = isset($fields['images']) && is_array($fields['images']) ? array_map('sanitize_url', $fields['images']) : array();
    $yoast             = isset($fields['yoast']) ? $fields['yoast'] : '';

    if (!$product_id && !$sku) {
      return new WP_Error('missing_identifier', 'Product ID or SKU is required', array('status' => 400));
    }

    if ($product_id) {
      if (!is_numeric($product_id)) {
        return new WP_Error('product_id_invalid', 'Product ID is invalid', array('status' => 400));
      }
    }

    if ($sku) {
      if ($sku !== sanitize_text_field($sku)) {
        return new WP_Error('sku_invalid', 'SKU is invalid', array('status' => 400));
      }
    }

    if ($sku && !$product_id) {
      $product_id_by_sku = wc_get_product_id_by_sku($sku);

      if ($product_id_by_sku) {
        $product = wc_get_product($product_id_by_sku);
      } else {
        return new WP_Error('product_not_found', 'Product not found with provided SKU', array('status' => 404));
      }
    } elseif ($product_id) {
      $product = wc_get_product($product_id);
    }
    
    if (!isset($product) || !$product) {
      return new WP_Error('product_not_found', 'Product not found', array('status' => 404));
    }

    if ($sku) {
      $product_id_by_sku = wc_get_product_id_by_sku($sku);

      if ($product_id_by_sku != $product->get_id()) {
        return new WP_Error('product_sku_exists', 'Product with provided SKU already exists', array('status' => 404));
      }
    }

    if ($slug) {
      // Check if slug already exists for a different product
      $existing_product_id = get_page_by_path($slug, OBJECT, get_post_types());

      if ($existing_product_id) {
        return new WP_Error('slug_exists', 'Slug already in use by another product', array('status' => 400));
      }
    }

    // Update product fields if provided
    if ($name) {
      $product->set_name($name);
    }

    if ($status) {
      $product->set_status($status);
    }

    if ($slug) {
      $product->set_slug($slug);
    }

    if ($description) {
      $product->set_description($description);
    }

    if ($short_description) {
      $product->set_short_description($short_description);
    }

    if ($price) {
      $product->set_regular_price($price);
    }

    if ($sale_price) {
      $product->set_sale_price($sale_price);
    }

    if ($map_price) {
      $product->update_meta_data('_map_price', $map_price);
    }

    if ($cost) {
      $product->update_meta_data('_cost', $cost);
    }

    if ($sku) {
      $product->set_sku($sku);
    }

    if ($upc) {
      if (!preg_match('/^[0-9\-]+$/', $upc)) {
        return new WP_Error('upc_malformed', 'UPC must contain only numbers and hyphens', array('status' => 500));
      }

      try {
        $product->update_meta_data('_global_unique_id', $upc);
      } catch (Exception $e) {
        return new WP_Error('product_exception', 'An error occurred when attempting to update UPC. It may be in use, please check your trash.', array('status' => 500));
      }
    }

    if ($weight) {
      $product->set_weight($weight);
    }

    if ($stock_status) {
      $product->set_stock_status($stock_status);
    }

    if ($manage_stock === true) {
      $product->set_manage_stock(true);
    } elseif ($manage_stock === false) {
      $product->set_manage_stock(false);
    }

    if ($stock_quantity >= 0 && $stock_quantity !== false) {
      $product->set_manage_stock(true);
      $product->set_stock_quantity($stock_quantity);
    }

    if ($tags) {
      wp_set_object_terms($product_id, $tags, 'product_tag');
    }

    if ($categories) {
      wp_set_object_terms($product_id, $categories, 'product_cat');
    }

    if (!empty($attributes) && is_array($attributes)) {
      $existing_attributes = $product->get_attributes();
      $attributes          = $this->create_or_update_product_attributes($attributes, $existing_attributes);
      
      if (is_wp_error($attributes)) {
        return $attributes;
      }
      
      // Update product attributes
      $product->set_attributes($attributes);
    }

    // Handle featured image
    if ($featured_image) {
      $image_id = $this->upload_image_to_media_library($featured_image, $product_id);

      if ($image_id && !is_wp_error($image_id)) {
        $product->set_image_id($image_id);
      }
    }

    // Handle gallery images
    if ($images) {
      $image_ids = array();
      foreach ($images as $image_url) {
        $image_id = $this->upload_image_to_media_library($image_url, $product_id);
        if ($image_id && !is_wp_error($image_id)) {
          $image_ids[] = $image_id;
        }
      }

      $product->set_gallery_image_ids($image_ids);
    }

    // Assign Yoast Meta Title
    if (isset($yoast['title'])) {
      $product->update_meta_data('_yoast_wpseo_title', sanitize_text_field($yoast['title']));
    }

    // Assign Yoast Meta Description
    if (isset($yoast['description'])) {
      $product->update_meta_data('_yoast_wpseo_metadesc', sanitize_text_field($yoast['description']));
    }

    // Assign Yoast Facebook Open Graph Title
    if (isset($yoast['premium']['social_appearance']['title'])) {
      $product->update_meta_data('_yoast_wpseo_opengraph-title', sanitize_text_field($yoast['premium']['social_appearance']['title']));
    }

    // Assign Yoast Facebook Open Graph Description
    if (isset($yoast['premium']['social_appearance']['description'])) {
      $product->update_meta_data('_yoast_wpseo_opengraph-description', sanitize_text_field($yoast['premium']['social_appearance']['description']));
    }

    // Assign Yoast Facebook Open Graph Image
    if (isset($yoast['premium']['social_appearance']['image'])) {
      $product->update_meta_data('_yoast_wpseo_opengraph-image', sanitize_url($yoast['premium']['social_appearance']['image']));
    }

    // Assign Yoast Facebook Open Graph Title
    if (isset($yoast['premium']['x']['title'])) {
      $product->update_meta_data('_yoast_wpseo_twitter-title', sanitize_text_field($yoast['premium']['x']['title']));
    }

    // Assign Yoast Facebook Open Graph Description
    if (isset($yoast['premium']['x']['description'])) {
      $product->update_meta_data('_yoast_wpseo_twitter-description', sanitize_text_field($yoast['premium']['x']['description']));
    }

    // Assign Yoast Facebook Open Graph Image
    if (isset($yoast['premium']['x']['image'])) {
      $product->update_meta_data('_yoast_wpseo_twitter-image', sanitize_url($yoast['premium']['x']['image']));
    }

    // Save the product
    try {
      $product_id = $product->save();
    } catch (Exception $e) {
      return new WP_Error('product_exception', 'Error saving product. This typically happens if you are trying to create a new product with a SKU already in use, please check your trash.', array('status' => 500));
    }

    if (!$product_id) {
      return new WP_Error('update_failed', 'Failed to update product', array('status' => 500));
    }

    // Return success response
    return new WP_REST_Response(array(
      'success' => true,
      'product_id' => $product_id,
      'message' => 'Product updated successfully'
    ), 200);
  }
  
  /**
   * Create product
   *
   * @param  mixed $request
   * @return void
   */
  public function create_product(WP_REST_Request $request) {
    $this->options = get_option('content_api_options_polyplugins');
    
    $fields            = $request->get_json_params();
    $name              = isset($fields['name']) ? sanitize_text_field($fields['name']) : '';
    $status            = isset($fields['status']) ? sanitize_text_field($fields['status']) : 'draft';
    $slug              = isset($fields['slug']) ? sanitize_title($fields['slug']) : '';
    $description       = isset($fields['description']) ? wp_kses_post($fields['description']) : '';
    $short_description = isset($fields['short_description']) ? wp_kses_post($fields['short_description']) : '';
    $price             = isset($fields['price']) ? floatval($fields['price']) : '';
    $sale_price        = isset($fields['sale_price']) ? floatval($fields['sale_price']) : '';
    $map_price         = isset($fields['map_price']) ? floatval($fields['map_price']) : '';
    $cost              = isset($fields['cost']) ? floatval($fields['cost']) : '';
    $sku               = isset($fields['sku']) ? sanitize_text_field($fields['sku']) : '';
    $upc               = isset($fields['upc']) ? sanitize_text_field($fields['upc']) : '';
    $weight            = isset($fields['weight']) ? floatval($fields['weight']) : '';
    $stock_status      = isset($fields['stock_status']) ? sanitize_text_field($fields['stock_status']) : '';
    $manage_stock      = isset($fields['manage_stock']) ? wc_string_to_bool($fields['manage_stock']) : null;
    $stock_quantity    = isset($fields['stock_quantity']) ? intval($fields['stock_quantity']) : false;
    $tags              = isset($fields['tags']) && is_array($fields['tags']) ? array_map('sanitize_text_field', $fields['tags']) : array();
    $categories        = isset($fields['categories']) && is_array($fields['categories']) ? array_map('sanitize_text_field', $fields['categories']) : array();
    $attributes        = isset($fields['attributes']) ? $fields['attributes'] : array();
    $featured_image    = isset($fields['featured_image']) ? sanitize_url($fields['featured_image']) : '';
    $images            = isset($fields['images']) && is_array($fields['images']) ? array_map('sanitize_url', $fields['images']) : array();
    $yoast             = isset($fields['yoast']) ? $fields['yoast'] : '';

    if ($sku) {
      if ($sku !== sanitize_text_field($sku)) {
        return new WP_Error('sku_invalid', 'SKU is invalid', array('status' => 400));
      }
    }

    if ($sku) {
      $product_id_by_sku = wc_get_product_id_by_sku($sku);

      if ($product_id_by_sku) {
        return new WP_Error('product_sku_exists', 'Product with provided SKU already exists', array('status' => 404));
      }
    }

    if ($slug) {
      // Check if slug already exists for a different product
      $existing_product_id = get_page_by_path($slug, OBJECT, get_post_types());

      if ($existing_product_id) {
        return new WP_Error('slug_exists', 'Slug already in use by another product', array('status' => 400));
      }
    }

    $product = new WC_Product_Simple();

    if ($name) {
      $product->set_name($name);
    }

    if ($status) {
      $product->set_status($status);
    }

    if ($slug) {
      $product->set_slug($slug);
    }

    if ($description) {
      $product->set_description($description);
    }

    if ($short_description) {
      $product->set_short_description($short_description);
    }

    if ($price) {
      $product->set_regular_price($price);
    }

    if ($sale_price) {
      $product->set_sale_price($sale_price);
    }

    if ($map_price) {
      $product->update_meta_data('_map_price', $map_price);
    }

    if ($cost) {
      $product->update_meta_data('_cost', $cost);
    }

    if ($sku) {
      $product->set_sku($sku);
    }

    if ($upc) {
      if (!preg_match('/^[0-9\-]+$/', $upc)) {
        return new WP_Error('upc_malformed', 'UPC must contain only numbers and hyphens', array('status' => 500));
      }

      try {
        $product->update_meta_data('_global_unique_id', $upc);
      } catch (Exception $e) {
        return new WP_Error('product_exception', 'An error occurred when attempting to update UPC. It may be in use, please check your trash.', array('status' => 500));
      }
    }

    if ($weight) {
      $product->set_weight($weight);
    }

    if ($stock_status) {
      $product->set_stock_status($stock_status);
    }

    if ($manage_stock === true) {
      $product->set_manage_stock(true);
    } elseif ($manage_stock === false) {
      $product->set_manage_stock(false);
    }

    if ($stock_quantity >= 0 && $stock_quantity !== false) {
      $product->set_manage_stock(true);
      $product->set_stock_quantity($stock_quantity);
    }

    // Assign Yoast Meta Title
    if (isset($yoast['title'])) {
      $product->update_meta_data('_yoast_wpseo_title', sanitize_text_field($yoast['title']));
    }

    // Assign Yoast Meta Description
    if (isset($yoast['description'])) {
      $product->update_meta_data('_yoast_wpseo_metadesc', sanitize_text_field($yoast['description']));
    }

    // Assign Yoast Facebook Open Graph Title
    if (isset($yoast['premium']['social_appearance']['title'])) {
      $product->update_meta_data('_yoast_wpseo_opengraph-title', sanitize_text_field($yoast['premium']['social_appearance']['title']));
    }

    // Assign Yoast Facebook Open Graph Description
    if (isset($yoast['premium']['social_appearance']['description'])) {
      $product->update_meta_data('_yoast_wpseo_opengraph-description', sanitize_text_field($yoast['premium']['social_appearance']['description']));
    }

    // Assign Yoast Facebook Open Graph Image
    if (isset($yoast['premium']['social_appearance']['image'])) {
      $product->update_meta_data('_yoast_wpseo_opengraph-image', sanitize_url($yoast['premium']['social_appearance']['image']));
    }

    // Assign Yoast Facebook Open Graph Title
    if (isset($yoast['premium']['x']['title'])) {
      $product->update_meta_data('_yoast_wpseo_twitter-title', sanitize_text_field($yoast['premium']['x']['title']));
    }

    // Assign Yoast Facebook Open Graph Description
    if (isset($yoast['premium']['x']['description'])) {
      $product->update_meta_data('_yoast_wpseo_twitter-description', sanitize_text_field($yoast['premium']['x']['description']));
    }

    // Assign Yoast Facebook Open Graph Image
    if (isset($yoast['premium']['x']['image'])) {
      $product->update_meta_data('_yoast_wpseo_twitter-image', sanitize_url($yoast['premium']['x']['image']));
    }

    // Save the product
    try {
      $product_id = $product->save();
    } catch (Exception $e) {
      return new WP_Error('product_exception', 'Error saving product. This typically happens if you are trying to create a new product with a SKU already in use, please check your trash.', array('status' => 500));
    }

    if ($tags) {
      wp_set_object_terms($product_id, $tags, 'product_tag');
    }

    if ($categories) {
      wp_set_object_terms($product_id, $categories, 'product_cat');
    }

    if (!empty($attributes) && is_array($attributes)) {
      $existing_attributes = $product->get_attributes();
      $attributes          = $this->create_or_update_product_attributes($attributes, $existing_attributes);

      if (is_wp_error($attributes)) {
        return $attributes;
      }

      // Update product attributes
      $product->set_attributes($attributes);
    }

    // Handle featured image
    if ($featured_image) {
      $image_id = $this->upload_image_to_media_library($featured_image, $product_id);

      if ($image_id && !is_wp_error($image_id)) {
        $product->set_image_id($image_id);
      }
    }

    // Handle gallery images
    if ($images) {
      $image_ids = array();
      foreach ($images as $image_url) {
        $image_id = $this->upload_image_to_media_library($image_url, $product_id);
        if ($image_id && !is_wp_error($image_id)) {
          $image_ids[] = $image_id;
        }
      }

      $product->set_gallery_image_ids($image_ids);
    }

    try {
      $product->save();
    } catch (Exception $e) {
      return new WP_Error('product_exception', 'An error occurred when attempting to save the product.', array('status' => 500));
    }

    if (!$product_id) {
      return new WP_Error('creation_failed', 'Failed to create product', array('status' => 500));
    }

    // Return success response
    return new WP_REST_Response(array(
      'success' => true,
      'product_id' => $product_id,
      'message' => 'Product created successfully'
    ), 200);
  }
  
  /**
   * Get all product categories
   *
   * @param  mixed $request
   * @return void
   */
  public function get_all_product_ids(WP_REST_Request $request) {
    $this->options = get_option('content_api_options_polyplugins');
    
    $fields = $request->get_params();
    $limit  = isset($fields['limit']) && is_numeric($fields['limit']) ? intval($fields['limit']) : -1;
    $status = isset($fields['status']) ? sanitize_text_field($fields['status']) : '';

    $args = array(
      'limit'  => $limit,
      'return' => 'ids'
    );

    if ($status) {
      $args['status'] = $status;
    }
    
    $products_ids = wc_get_products($args);

    return new WP_REST_Response(array(
      'success'     => true,
      'product_ids' => $products_ids
    ), 200);
  }
  
  /**
   * Get all product categories
   *
   * @param  mixed $request
   * @return void
   */
  public function get_all_product_categories(WP_REST_Request $request) {
    $this->options = get_option('content_api_options_polyplugins');
    
    $args = array(
      'taxonomy'   => 'product_cat',
      'hide_empty' => false,
      'orderby'    => 'name',
      'order'      => 'ASC',
    );

    $terms = get_terms($args);

    if (is_wp_error($terms)) {
      return new WP_Error('fetch_failed', 'Failed to fetch categories', array('status' => 500));
    }

    // Map terms by ID
    $term_map = array();
    foreach ($terms as $term) {
      $term_map[$term->term_id] = array(
        'id'       => $term->term_id,
        'name'     => $term->name,
        'slug'     => $term->slug,
        'children' => array(),
        'parent'   => $term->parent,
      );
    }

    // Build the category tree
    $tree = array();
    foreach ($term_map as $id => &$term) {
      if ($term['parent'] && isset($term_map[$term['parent']])) {
        $term_map[$term['parent']]['children'][] = &$term;
      } else {
        $tree[] = &$term;
      }
    }

    unset($term); // Break the reference

    // Prepare category data
    $category_data = array();
    foreach ($tree as $term) {
      $category_data[] = $this->build_category_hierarchy($term);
    }

    return new WP_REST_Response(array(
      'success'    => true,
      'categories' => $category_data
    ), 200);
  }
  
  /**
   * Get product category
   *
   * @param  mixed $request
   * @return void
   */
  public function get_product_category(WP_REST_Request $request) {
    $params   = $request->get_params();
    $taxonomy = isset($params['taxonomy']) ? sanitize_text_field($params['taxonomy']) : 'product_cat';
    $id       = isset($params['id']) ? absint($params['id']) : 0;
    $slug     = isset($params['slug']) ? sanitize_title($params['slug']) : '';
    $name     = isset($params['name']) ? sanitize_text_field($params['name']) : '';

    if ($id) {
      $term = get_term($id, $taxonomy);
    } elseif ($slug) {
      $term = get_term_by('slug', $slug, $taxonomy);
    } elseif ($name) {
      $term = get_term_by('name', $name, $taxonomy);
    }

    if (empty($term) || is_wp_error($term)) {
      return new WP_Error('no_product_category_found', "No category found under the " . $taxonomy . " taxonomy.", array('status' => 400));
    }

    $term_id               = $term->term_id;
    $product_category_data = (array) $term;

    // Get thumbnail ID and URL
    $thumbnail_id  = get_term_meta($term_id, 'thumbnail_id', true);
    $thumbnail_url = $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : '';

    if ($thumbnail_url) {
      $product_category_data['thumbnail'] = $thumbnail_url;
    }

    // Get Yoast SEO meta fields
    $yoast_option = get_option('wpseo_taxonomy_meta');
    $yoast_data   = [];

    if (isset($yoast_option[$taxonomy]) && isset($yoast_option[$taxonomy][$term_id])) {
      $yoast_raw = $yoast_option[$taxonomy][$term_id];
      $yoast_data = array(
        'title'       => $yoast_raw['wpseo_title'] ?? '',
        'description' => $yoast_raw['wpseo_desc'] ?? '',
        'premium' => array(
          'social_appearance' => array(
            'title'       => $yoast_raw['wpseo_opengraph-title'] ?? '',
            'description' => $yoast_raw['wpseo_opengraph-description'] ?? '',
            'image'       => $yoast_raw['wpseo_opengraph-image'] ?? '',
          ),
          'x' => array(
            'title'       => $yoast_raw['wpseo_twitter-title'] ?? '',
            'description' => $yoast_raw['wpseo_twitter-description'] ?? '',
            'image'       => $yoast_raw['wpseo_twitter-image'] ?? '',
          )
        )
      );
    }

    $product_category_data['yoast'] = $yoast_data;

    return new WP_REST_Response(array(
      'success' => true,
      'data' => $product_category_data,
    ), 201);
  }
  
  /**
   * Update product category
   *
   * @param  mixed $request
   * @return void
   */
  public function update_product_category(WP_REST_Request $request) {
    $fields      = $request->get_json_params();
    $id          = isset($fields['id']) ? absint($fields['id']) : 0;
    $name        = isset($fields['name']) ? sanitize_text_field($fields['name']) : '';
    $description = isset($fields['description']) ? wp_kses_post($fields['description']) : '';
    $slug        = isset($fields['slug']) ? sanitize_title($fields['slug']) : '';
    $parent_id   = isset($fields['parent']) ? absint($fields['parent']) : '';
    $taxonomy    = isset($fields['taxonomy']) ? sanitize_text_field($fields['taxonomy']) : 'product_cat';

    // Basic input validation
    if (!$id) {
      return new WP_Error('missing_identifier', 'Product category ID is required', array('status' => 400));
    }

    $term = get_term($id, $taxonomy);

    if (!$term || is_wp_error($term)) {
      return new WP_Error('category_not_found', 'Product category not found', array('status' => 404));
    }

    $term_id = $term->term_id;

    $term_args = [];

    if ($name) {
      $term_args['name'] = $name;
    }

    if ($description) {
      $term_args['description'] = $description;
    }

    if ($slug) {
      $term_args['slug'] = $slug;
    }

    if ($parent_id > 0) {
      $parent_term = get_term($parent_id, $taxonomy);

      if ($parent_term && !is_wp_error($parent_term)) {
        $term_args['parent'] = $parent_id;
      } else {
        return new WP_Error('invalid_parent', 'The specified parent product category does not exist.', array('status' => 400));
      }
    } elseif ($parent_id === 0) {
      $term_args['parent'] = 0; // Remove parent
    }

    if (!empty($term_args)) {
      $result = wp_update_term($term_id, $taxonomy, $term_args);
      
      if (is_wp_error($result)) {
        return new WP_Error('term_update_failed', 'Failed to update product category term', array('status' => 500));
      }
    }

    // Update Yoast fields
    if (isset($fields['yoast']) && is_array($fields['yoast'])) {
      $yoast_option = get_option('wpseo_taxonomy_meta');

      if (!isset($yoast_option[$taxonomy])) {
        $yoast_option[$taxonomy] = [];
      }

      if (!isset($yoast_option[$taxonomy][$term_id])) {
        $yoast_option[$taxonomy][$term_id] = [];
      }

      $yoast_input = $fields['yoast'];

      if (isset($yoast_input['title']) && $yoast_input['title']) {
        $yoast_option[$taxonomy][$term_id]['wpseo_title'] = $yoast_input['title'];
      }

      if (isset($yoast_input['description']) && $yoast_input['description']) {
        $yoast_option[$taxonomy][$term_id]['wpseo_desc'] = $yoast_input['description'];
      }

      if (isset($yoast_input['premium']['social_appearance']['title']) && $yoast_input['premium']['social_appearance']['title']) {
        $yoast_option[$taxonomy][$term_id]['wpseo_opengraph-title'] = $yoast_input['premium']['social_appearance']['title'];
      }
      
      if (isset($yoast_input['premium']['social_appearance']['description']) && $yoast_input['premium']['social_appearance']['description']) {
        $yoast_option[$taxonomy][$term_id]['wpseo_opengraph-description'] = $yoast_input['premium']['social_appearance']['description'];
      }
      
      if (isset($yoast_input['premium']['social_appearance']['image']) && $yoast_input['premium']['social_appearance']['image']) {
        $yoast_option[$taxonomy][$term_id]['wpseo_opengraph-image'] = $yoast_input['premium']['social_appearance']['image'];
      }
      
      if (isset($yoast_input['premium']['x']['title']) && $yoast_input['premium']['x']['title']) {
        $yoast_option[$taxonomy][$term_id]['wpseo_twitter-title'] = $yoast_input['premium']['x']['title'];
      }
      
      if (isset($yoast_input['premium']['x']['description']) && $yoast_input['premium']['x']['description']) {
        $yoast_option[$taxonomy][$term_id]['wpseo_twitter-description'] = $yoast_input['premium']['x']['description'];
      }
      
      if (isset($yoast_input['premium']['x']['image']) && $yoast_input['premium']['x']['image']) {
        $yoast_option[$taxonomy][$term_id]['wpseo_twitter-image'] = $yoast_input['premium']['x']['image'];
      }

      update_option('wpseo_taxonomy_meta', $yoast_option);
    }

    return new WP_REST_Response(array(
      'success'             => true,
      'product_category_id' => $term_id,
      'message'             => 'Product category updated successfully.'
    ), 200);
  }
  
  /**
   * Create product category
   *
   * @param  mixed $request
   * @return void
   */
  public function create_product_category(WP_REST_Request $request) {
    $fields      = $request->get_json_params();
    $name        = isset($fields['name']) ? sanitize_text_field($fields['name']) : '';
    $description = isset($fields['description']) ? wp_kses_post($fields['description']) : '';
    $slug        = isset($fields['slug']) ? sanitize_title($fields['slug']) : '';
    $parent_id   = isset($fields['parent']) ? absint($fields['parent']) : 0;
    $taxonomy    = isset($fields['taxonomy']) ? sanitize_text_field($fields['taxonomy']) : 'product_cat';

    // Validate name
    if (empty($name)) {
      return new WP_Error('missing_name', 'Product category name is required.', array('status' => 400));
    }

    $term_args = array(
      'description' => $description,
      'slug'        => $slug,
      'parent'      => 0
    );

    if ($parent_id > 0) {
      $parent_term = get_term($parent_id, $taxonomy);

      if ($parent_term && !is_wp_error($parent_term)) {
        $term_args['parent'] = $parent_id;
      } else {
        return new WP_Error('invalid_parent', 'The specified parent product category does not exist.', array('status' => 400));
      }
    }

    // Create the term
    $result = wp_insert_term($name, $taxonomy, $term_args);

    if (is_wp_error($result)) {
      return new WP_Error('term_creation_failed', $result->get_error_message(), array('status' => 500));
    }

    $term_id = $result['term_id'];

    // Update Yoast fields
    if (isset($fields['yoast']) && is_array($fields['yoast'])) {
      $yoast_option = get_option('wpseo_taxonomy_meta');

      if (!isset($yoast_option[$taxonomy])) {
        $yoast_option[$taxonomy] = array();
      }

      if (!isset($yoast_option[$taxonomy][$term_id])) {
        $yoast_option[$taxonomy][$term_id] = array();
      }

      $yoast_input = $fields['yoast'];

      if (!empty($yoast_input['title'])) {
        $yoast_option[$taxonomy][$term_id]['wpseo_title'] = $yoast_input['title'];
      }

      if (!empty($yoast_input['description'])) {
        $yoast_option[$taxonomy][$term_id]['wpseo_desc'] = $yoast_input['description'];
      }

      if (!empty($yoast_input['premium']['social_appearance']['title'])) {
        $yoast_option[$taxonomy][$term_id]['wpseo_opengraph-title'] = $yoast_input['premium']['social_appearance']['title'];
      }

      if (!empty($yoast_input['premium']['social_appearance']['description'])) {
        $yoast_option[$taxonomy][$term_id]['wpseo_opengraph-description'] = $yoast_input['premium']['social_appearance']['description'];
      }

      if (!empty($yoast_input['premium']['social_appearance']['image'])) {
        $yoast_option[$taxonomy][$term_id]['wpseo_opengraph-image'] = $yoast_input['premium']['social_appearance']['image'];
      }

      if (!empty($yoast_input['premium']['x']['title'])) {
        $yoast_option[$taxonomy][$term_id]['wpseo_twitter-title'] = $yoast_input['premium']['x']['title'];
      }

      if (!empty($yoast_input['premium']['x']['description'])) {
        $yoast_option[$taxonomy][$term_id]['wpseo_twitter-description'] = $yoast_input['premium']['x']['description'];
      }

      if (!empty($yoast_input['premium']['x']['image'])) {
        $yoast_option[$taxonomy][$term_id]['wpseo_twitter-image'] = $yoast_input['premium']['x']['image'];
      }

      update_option('wpseo_taxonomy_meta', $yoast_option);
    }

    return new WP_REST_Response(array(
      'success'             => true,
      'product_category_id' => $term_id,
      'message'             => 'Product category created successfully.'
    ), 201);
  }
  
  /**
   * Get product categories
   *
   * @param  mixed $request
   * @return void
   */
  public function get_product_categories(WP_REST_Request $request) {
    $this->options = get_option('content_api_options_polyplugins');
    
    $fields     = $request->get_json_params();
    $product_id = isset($fields['product_id']) ? absint($fields['product_id']) : 0;
    $sku        = isset($fields['sku']) ? sanitize_text_field($fields['sku']) : '';

    if (!$product_id && !$sku) {
      return new WP_Error('missing_identifier', 'Product ID or SKU is required', array('status' => 400));
    }

    if ($product_id) {
      if (!is_numeric($product_id)) {
        return new WP_Error('product_id_invalid', 'Product ID is invalid', array('status' => 400));
      }
    }

    if ($sku) {
      if ($sku !== sanitize_text_field($sku)) {
        return new WP_Error('sku_invalid', 'SKU is invalid', array('status' => 400));
      }
    }

    if ($product_id && $sku) {
      return new WP_Error('conflicting_identifiers', 'Both Product ID and SKU are provided. Please provide only one.', array('status' => 400));
    }

    if ($sku && !$product_id) {
      $product_id_by_sku = wc_get_product_id_by_sku($sku);

      if ($product_id_by_sku) {
        $product = wc_get_product($product_id_by_sku);
      } else {
        return new WP_Error('product_not_found', 'Product not found with provided SKU', array('status' => 404));
      }
    } 
    elseif ($product_id) {
      $product = wc_get_product($product_id);
    }
    
    if (!isset($product) || !$product) {
      return new WP_Error('product_not_found', 'Product not found', array('status' => 404));
    }

    $categories = wp_get_post_terms($product->get_id(), 'product_cat');

    // Map terms by ID
    $term_map = array();
    foreach ($categories as $term) {
      $term_map[$term->term_id] = array(
        'id'       => $term->term_id,
        'name'     => $term->name,
        'slug'     => $term->slug,
        'children' => array(),
        'parent'   => $term->parent,
      );
    }

    // Build the category tree
    $tree = array();

    foreach ($term_map as $id => &$term) {
      if ($term['parent'] && isset($term_map[$term['parent']])) {
        $term_map[$term['parent']]['children'][] = &$term;
      } else {
        $tree[] = &$term;
      }
    }
    unset($term); // Break the reference

    // Prepare category data
    $category_data = array();
    foreach ($tree as $term) {
      $category_data[] = $this->build_category_hierarchy($term);
    }

    return new WP_REST_Response(array(
      'success'   => true,
      'product_id'=> $product->get_id(),
      'categories'=> $category_data
    ), 200);
  }
  
  /**
   * Update product categories
   *
   * @param  mixed $request
   * @return void
   */
  public function update_product_categories(WP_REST_Request $request) {
    $this->options = get_option('content_api_options_polyplugins');
    
    $fields     = $request->get_json_params();
    $product_id = isset($fields['product_id']) ? absint($fields['product_id']) : 0;
    $sku        = isset($fields['sku']) ? sanitize_text_field($fields['sku']) : '';
    $categories = isset($fields['categories']) ? array_map('absint', $fields['categories']) : array();

    if (!$product_id && !$sku) {
      return new WP_Error('missing_identifier', 'Product ID or SKU is required', array('status' => 400));
    }

    if ($product_id) {
      if (!is_numeric($product_id)) {
        return new WP_Error('product_id_invalid', 'Product ID is invalid', array('status' => 400));
      }
    }

    if ($sku) {
      if ($sku !== sanitize_text_field($sku)) {
        return new WP_Error('sku_invalid', 'SKU is invalid', array('status' => 400));
      }
    }

    if ($product_id && $sku) {
      return new WP_Error('conflicting_identifiers', 'Both Product ID and SKU are provided. Please provide only one.', array('status' => 400));
    }

    if ($sku && !$product_id) {
      $product_id_by_sku = wc_get_product_id_by_sku($sku);

      if ($product_id_by_sku) {
        $product = wc_get_product($product_id_by_sku);
      } else {
        return new WP_Error('product_not_found', 'Product not found with provided SKU', array('status' => 404));
      }
    } 
    elseif ($product_id) {
      $product = wc_get_product($product_id);
    }
    
    if (!isset($product) || !$product) {
      return new WP_Error('product_not_found', 'Product not found', array('status' => 404));
    }

    if (!$categories) {
      return new WP_Error('invalid_input', 'Categories empty', array('status' => 400));
    }

    // Validate that all categories exist
    foreach ($categories as $cat_id) {
      $term = get_term($cat_id, 'product_cat');

      if (!$term || is_wp_error($term)) {
        return new WP_Error('category_not_found', "Category ID {$cat_id} does not exist", array('status' => 404));
      }
    }

    // Get current categories on the product
    $existing_cat_ids = wp_get_object_terms($product_id, 'product_cat', array('fields' => 'ids'));

    // Merge and deduplicate
    $final_cat_ids = array_unique(array_merge($existing_cat_ids, $categories));

    wp_set_object_terms($product_id, $final_cat_ids, 'product_cat');

    return new WP_REST_Response(array(
      'success' => true,
      'product_id' => $product_id,
      'assigned_category_ids' => $final_cat_ids
    ), 200);
  }

  /**
   * Get product brands
   *
   * @param  WP_REST_Request $request
   * @return WP_REST_Response
   */
  public function get_brand(WP_REST_Request $request) {
    $params   = $request->get_params();
    $taxonomy = isset($params['taxonomy']) ? sanitize_text_field($params['taxonomy']) : 'product_brand';
    $id       = isset($params['id']) ? absint($params['id']) : 0;
    $slug     = isset($params['slug']) ? sanitize_title($params['slug']) : '';
    $name     = isset($params['name']) ? sanitize_text_field($params['name']) : '';

    if ($id) {
      $term = get_term($id, $taxonomy);
    } elseif ($slug) {
      $term = get_term_by('slug', $slug, $taxonomy);
    } elseif ($name) {
      $term = get_term_by('name', $name, $taxonomy);
    }

    if (empty($term) || is_wp_error($term)) {
      return new WP_Error('no_brand_found', "No brand found under the " . $taxonomy . " taxonomy.", array('status' => 400));
    }

    $term_id    = $term->term_id;
    $brand_data = (array) $term;

    // Get thumbnail ID and URL
    $thumbnail_id  = get_term_meta($term_id, 'thumbnail_id', true);
    $thumbnail_url = $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : '';

    if ($thumbnail_url) {
      $brand_data['thumbnail'] = $thumbnail_url;
    }

    // Get Yoast SEO meta fields
    $yoast_option = get_option('wpseo_taxonomy_meta');
    $yoast_data   = [];

    if (isset($yoast_option[$taxonomy]) && isset($yoast_option[$taxonomy][$term_id])) {
      $yoast_raw = $yoast_option[$taxonomy][$term_id];
      $yoast_data = array(
        'title'       => $yoast_raw['wpseo_title'] ?? '',
        'description' => $yoast_raw['wpseo_desc'] ?? '',
        'premium' => array(
          'social_appearance' => array(
            'title'       => $yoast_raw['wpseo_opengraph-title'] ?? '',
            'description' => $yoast_raw['wpseo_opengraph-description'] ?? '',
            'image'       => $yoast_raw['wpseo_opengraph-image'] ?? '',
          ),
          'x' => array(
            'title'       => $yoast_raw['wpseo_twitter-title'] ?? '',
            'description' => $yoast_raw['wpseo_twitter-description'] ?? '',
            'image'       => $yoast_raw['wpseo_twitter-image'] ?? '',
          )
        )
      );
    }

    $brand_data['yoast'] = $yoast_data;

    return new WP_REST_Response(array(
      'success' => true,
      'data' => $brand_data,
    ), 201);
  }

  /**
   * Get product brands
   *
   * @param  WP_REST_Request $request
   * @return WP_REST_Response
   */
  public function update_brand(WP_REST_Request $request) {
    $fields      = $request->get_json_params();
    $id          = isset($fields['id']) ? absint($fields['id']) : 0;
    $name        = isset($fields['name']) ? sanitize_text_field($fields['name']) : '';
    $description = isset($fields['description']) ? wp_kses_post($fields['description']) : '';
    $slug        = isset($fields['slug']) ? sanitize_title($fields['slug']) : '';
    $parent_id   = isset($fields['parent']) ? absint($fields['parent']) : '';
    $taxonomy    = isset($fields['taxonomy']) ? sanitize_text_field($fields['taxonomy']) : 'product_brand';

    // Basic input validation
    if (!$id) {
      return new WP_Error('missing_identifier', 'Brand ID is required', array('status' => 400));
    }

    $term = get_term($id, $taxonomy);

    if (!$term || is_wp_error($term)) {
      return new WP_Error('brand_not_found', 'Brand not found', array('status' => 404));
    }

    $term_id = $term->term_id;

    $term_args = [];

    if ($name) {
      $term_args['name'] = $name;
    }

    if ($description) {
      $term_args['description'] = $description;
    }

    if ($slug) {
      $term_args['slug'] = $slug;
    }

    if ($parent_id > 0) {
      $parent_term = get_term($parent_id, $taxonomy);

      if ($parent_term && !is_wp_error($parent_term)) {
        $term_args['parent'] = $parent_id;
      } else {
        return new WP_Error('invalid_parent', 'The specified parent brand does not exist.', array('status' => 400));
      }
    } elseif ($parent_id === 0) {
      $term_args['parent'] = 0; // Remove parent
    }

    if (!empty($term_args)) {
      $result = wp_update_term($term_id, $taxonomy, $term_args);
      
      if (is_wp_error($result)) {
        return new WP_Error('term_update_failed', 'Failed to update brand term', array('status' => 500));
      }
    }

    // Update Yoast fields
    if (isset($fields['yoast']) && is_array($fields['yoast'])) {
      $yoast_option = get_option('wpseo_taxonomy_meta');

      if (!isset($yoast_option[$taxonomy])) {
        $yoast_option[$taxonomy] = [];
      }

      if (!isset($yoast_option[$taxonomy][$term_id])) {
        $yoast_option[$taxonomy][$term_id] = [];
      }

      $yoast_input = $fields['yoast'];

      if (isset($yoast_input['title']) && $yoast_input['title']) {
        $yoast_option[$taxonomy][$term_id]['wpseo_title'] = $yoast_input['title'];
      }

      if (isset($yoast_input['description']) && $yoast_input['description']) {
        $yoast_option[$taxonomy][$term_id]['wpseo_desc'] = $yoast_input['description'];
      }

      if (isset($yoast_input['premium']['social_appearance']['title']) && $yoast_input['premium']['social_appearance']['title']) {
        $yoast_option[$taxonomy][$term_id]['wpseo_opengraph-title'] = $yoast_input['premium']['social_appearance']['title'];
      }
      
      if (isset($yoast_input['premium']['social_appearance']['description']) && $yoast_input['premium']['social_appearance']['description']) {
        $yoast_option[$taxonomy][$term_id]['wpseo_opengraph-description'] = $yoast_input['premium']['social_appearance']['description'];
      }
      
      if (isset($yoast_input['premium']['social_appearance']['image']) && $yoast_input['premium']['social_appearance']['image']) {
        $yoast_option[$taxonomy][$term_id]['wpseo_opengraph-image'] = $yoast_input['premium']['social_appearance']['image'];
      }
      
      if (isset($yoast_input['premium']['x']['title']) && $yoast_input['premium']['x']['title']) {
        $yoast_option[$taxonomy][$term_id]['wpseo_twitter-title'] = $yoast_input['premium']['x']['title'];
      }
      
      if (isset($yoast_input['premium']['x']['description']) && $yoast_input['premium']['x']['description']) {
        $yoast_option[$taxonomy][$term_id]['wpseo_twitter-description'] = $yoast_input['premium']['x']['description'];
      }
      
      if (isset($yoast_input['premium']['x']['image']) && $yoast_input['premium']['x']['image']) {
        $yoast_option[$taxonomy][$term_id]['wpseo_twitter-image'] = $yoast_input['premium']['x']['image'];
      }

      update_option('wpseo_taxonomy_meta', $yoast_option);
    }

    return new WP_REST_Response(array(
      'success' => true,
      'brand_id' => $term_id,
      'message' => 'Brand updated successfully.'
    ), 200);
  }

  /**
   * Create a new product brand
   *
   * @param  WP_REST_Request $request
   * @return WP_REST_Response
   */
  public function create_brand(WP_REST_Request $request) {
    $fields      = $request->get_json_params();
    $name        = isset($fields['name']) ? sanitize_text_field($fields['name']) : '';
    $description = isset($fields['description']) ? wp_kses_post($fields['description']) : '';
    $slug        = isset($fields['slug']) ? sanitize_title($fields['slug']) : '';
    $parent_id   = isset($fields['parent']) ? absint($fields['parent']) : 0;
    $taxonomy    = isset($fields['taxonomy']) ? sanitize_text_field($fields['taxonomy']) : 'product_brand';

    // Validate name
    if (empty($name)) {
      return new WP_Error('missing_name', 'Brand name is required.', array('status' => 400));
    }

    $term_args = array(
      'description' => $description,
      'slug'        => $slug,
      'parent'      => 0
    );

    if ($parent_id > 0) {
      $parent_term = get_term($parent_id, $taxonomy);

      if ($parent_term && !is_wp_error($parent_term)) {
        $term_args['parent'] = $parent_id;
      } else {
        return new WP_Error('invalid_parent', 'The specified parent brand does not exist.', array('status' => 400));
      }
    }

    // Create the term
    $result = wp_insert_term($name, $taxonomy, $term_args);

    if (is_wp_error($result)) {
      return new WP_Error('term_creation_failed', $result->get_error_message(), array('status' => 500));
    }

    $term_id = $result['term_id'];

    // Update Yoast fields
    if (isset($fields['yoast']) && is_array($fields['yoast'])) {
      $yoast_option = get_option('wpseo_taxonomy_meta');

      if (!isset($yoast_option[$taxonomy])) {
        $yoast_option[$taxonomy] = array();
      }

      if (!isset($yoast_option[$taxonomy][$term_id])) {
        $yoast_option[$taxonomy][$term_id] = array();
      }

      $yoast_input = $fields['yoast'];

      if (!empty($yoast_input['title'])) {
        $yoast_option[$taxonomy][$term_id]['wpseo_title'] = $yoast_input['title'];
      }

      if (!empty($yoast_input['description'])) {
        $yoast_option[$taxonomy][$term_id]['wpseo_desc'] = $yoast_input['description'];
      }

      if (!empty($yoast_input['premium']['social_appearance']['title'])) {
        $yoast_option[$taxonomy][$term_id]['wpseo_opengraph-title'] = $yoast_input['premium']['social_appearance']['title'];
      }

      if (!empty($yoast_input['premium']['social_appearance']['description'])) {
        $yoast_option[$taxonomy][$term_id]['wpseo_opengraph-description'] = $yoast_input['premium']['social_appearance']['description'];
      }

      if (!empty($yoast_input['premium']['social_appearance']['image'])) {
        $yoast_option[$taxonomy][$term_id]['wpseo_opengraph-image'] = $yoast_input['premium']['social_appearance']['image'];
      }

      if (!empty($yoast_input['premium']['x']['title'])) {
        $yoast_option[$taxonomy][$term_id]['wpseo_twitter-title'] = $yoast_input['premium']['x']['title'];
      }

      if (!empty($yoast_input['premium']['x']['description'])) {
        $yoast_option[$taxonomy][$term_id]['wpseo_twitter-description'] = $yoast_input['premium']['x']['description'];
      }

      if (!empty($yoast_input['premium']['x']['image'])) {
        $yoast_option[$taxonomy][$term_id]['wpseo_twitter-image'] = $yoast_input['premium']['x']['image'];
      }

      update_option('wpseo_taxonomy_meta', $yoast_option);
    }

    return new WP_REST_Response(array(
      'success'  => true,
      'brand_id' => $term_id,
      'message'  => 'Brand created successfully.'
    ), 201);
  }

  /**
   * Get terms
   *
   * @param  mixed $request
   * @return void
   */
  public function get_terms(WP_REST_Request $request) {
    $this->options = get_option('content_api_options_polyplugins');
    
    // Get the data from the request
    $params = $request->get_params();

    if (empty($params)) {
      return new WP_Error('data_missing', "Missing data", array('status' => 400));
    }

    $taxonomy = isset($params['taxonomy']) ? sanitize_text_field($params['taxonomy']) : '';

    if (!$taxonomy) {
      return new WP_Error('taxonomy_missing', "Missing taxonomy", array('status' => 400));
    }
    
    if ($taxonomy !== 'product_cat' && $taxonomy !== 'brand') {
      return new WP_Error('taxonomy_invalid', "Only product_cat and brand taxonomies are supported currently.", array('status' => 400));
    }

    $get_terms = get_terms(array(
      'taxonomy' => $taxonomy,
      'hide_empty' => false,
    ));

    $terms = array();

    if (!$get_terms || is_wp_error($get_terms)) {
      return new WP_Error('terms_not_found', "No terms were found in " . $taxonomy . " taxonomy.", array('status' => 400));
    }

    foreach ($get_terms as $term) {
      $id   = $term->term_id;
      $name = $term->name;
      $slug = $term->slug;
      $link = get_term_link($id);

      $terms[] = array(
        'id'            => $id,
        'name'          => $name,
        'slug'          => $slug,
        'link'          => $link,
        'relative_link' => wp_make_link_relative($link),
      );
    }

    return new WP_REST_Response(array(
      'success' => true,
      'data' => $terms,
    ), 201);

  }

  /**
   * Create post
   *
   * @param  mixed $request
   * @return void
   */
  public function update_terms(WP_REST_Request $request) {
    $this->options = get_option('content_api_options_polyplugins');
    
    // Get the JSON data from the request
    $params = $request->get_json_params();

    if (empty($params)) {
      return new WP_Error('data_missing', "Missing data", array('status' => 400));
    }

    $taxonomy = isset($params['taxonomy']) ? sanitize_text_field($params['taxonomy']) : '';

    if (!$taxonomy) {
      return new WP_Error('taxonomy_missing', "Missing taxonomy", array('status' => 400));
    }
    
    if ($taxonomy !== 'product_cat' && $taxonomy !== 'brand') {
      return new WP_Error('taxonomy_invalid', "Only product_cat and brand taxonomies are supported currently.", array('status' => 400));
    }

    $term_id = isset($params['id']) ? sanitize_text_field($params['id']) : '';

    if (!is_numeric($term_id)) {
      return new WP_Error('invalid_term_id', "Invalid term id", array('status' => 400));
    }

    $term_name = isset($params['name']) ? sanitize_text_field($params['name']) : '';

    if (!$term_name) {
      return new WP_Error('term_name_missing', "Missing term name", array('status' => 400));
    }

    $term_description = isset($params['description']) ? wp_kses_post($params['description']) : '';

    if (!$term_description) {
      return new WP_Error('no_description', "No description was provided", array('status' => 400));
    }

    $update_terms = wp_update_term(
      $term_id,
      $taxonomy,
      array(
        'description' => $term_description,
        'name'        => $term_name,
      )
    );

    if (!$update_terms || is_wp_error($update_terms)) {
      return new WP_Error('terms_not_found', "The term id or taxonomy was incorrect", array('status' => 400));
    }

    // Return success response
    return new WP_REST_Response(array(
      'success' => true,
      'term_id' => $term_id,
    ), 201);
  }

  /**
   * Get all products with attributes
   *
   * @param  WP_REST_Request $request
   * @return WP_REST_Response
   */
  public function get_attributes(WP_REST_Request $request)
  {
    $this->options = get_option('content_api_options_polyplugins');

    $product_data = get_transient('content_api_attributes');

    if (!$product_data) {
      // Get all products
      $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1, // Get all products
      );

      $products = get_posts($args);

      if (!$products) {
        return new WP_Error('no_products', "No products found.", array('status' => 400));
      }

      $product_data = array();

      foreach ($products as $product) {
        $product_id  = $product->ID;
        $product_obj = wc_get_product($product_id);

        if (!$product_obj) {
          continue;
        }

        // Get product attributes
        $attributes = $product_obj->get_attributes();

        $formatted_attributes = array();

        foreach ($attributes as $attribute) {
          if ($attribute->is_taxonomy()) {
            $terms = wp_get_post_terms($product_id, $attribute->get_name(), array('fields' => 'names'));

            $formatted_attributes[] = array(
              'name'   => $attribute->get_name(),
              'value'  => $terms,
            );
          } else {
            $formatted_attributes[] = array(
              'name'   => $attribute->get_name(),
              'value'  => $attribute->get_options(),
            );
          }
        }

        $product_data[] = array(
          'id'         => $product_id,
          'name'       => $product_obj->get_name(),
          'attributes' => $formatted_attributes,
        );
      }

      set_transient('content_api_attributes', $product_data, 86400);
    }

    return new WP_REST_Response(array(
      'success' => true,
      'data'    => $product_data,
    ), 200);
  }

  /**
   * Get all products with attributes
   *
   * @param  WP_REST_Request $request
   * @return WP_REST_Response
   */
  public function get_product_attributes(WP_REST_Request $request)
  {
    $this->options = get_option('content_api_options_polyplugins');
    
    $fields     = $request->get_params();
    $product_id = isset($fields['product_id']) && is_numeric($fields['product_id']) ? absint($fields['product_id']) : '';
    $sku        = isset($fields['sku']) ? sanitize_text_field($fields['sku']) : '';

    if (!$product_id && !$sku) {
      return new WP_Error('missing_identifier', 'Product ID or SKU is required', array('status' => 400));
    }

    if ($product_id) {
      if (!is_numeric($product_id)) {
        return new WP_Error('product_id_invalid', 'Product ID is invalid', array('status' => 400));
      } else {
        $product_id = absint($product_id);
      }
    }

    if ($sku) {
      if ($sku !== sanitize_text_field($sku)) {
        return new WP_Error('sku_invalid', 'SKU is invalid', array('status' => 400));
      }
    }

    if ($product_id && $sku) {
      return new WP_Error('conflicting_identifiers', 'Both Product ID and SKU are provided. Please provide only one.', array('status' => 400));
    }

    if ($sku && !$product_id) {
      $product_id_by_sku = wc_get_product_id_by_sku($sku);

      if ($product_id_by_sku) {
        $product = wc_get_product($product_id_by_sku);
      } else {
        return new WP_Error('product_not_found', 'Product not found with provided SKU', array('status' => 404));
      }
    } 
    elseif ($product_id) {
      $product = wc_get_product($product_id);
    }
    
    if (!isset($product) || !$product) {
      return new WP_Error('product_not_found', 'Product not found', array('status' => 404));
    }

    $attributes = array();

    foreach ($product->get_attributes() as $attribute_key => $attribute) {
      if ($attribute->is_taxonomy()) {
        $terms = wp_get_post_terms($product->get_id(), $attribute->get_name(), array('fields' => 'names'));
        $attributes[$attribute->get_name()] = $terms;
      } else {
        $attributes[$attribute->get_name()] = $attribute->get_options();
      }
    }

    $product_data = array(
      'id'         => $product->get_id(),
      'name'       => $product->get_name(),
      'attributes' => $attributes,
    );

    return new WP_REST_Response(array(
      'success' => true,
      'data'    => $product_data,
    ), 200);
  }

  /**
   * Update product attributes
   *
   * @param  WP_REST_Request $request
   * @return WP_REST_Response
   */
  public function update_attributes(WP_REST_Request $request)
  {
    $this->options = get_option('content_api_options_polyplugins');
    
    // Get the JSON data from the request
    $params = $request->get_json_params();

    if (empty($params)) {
      return new WP_Error('data_missing', "Missing JSON data", array('status' => 400));
    }

    // Extract product ID
    $product_id = isset($params['id']) ? intval($params['id']) : 0;
    
    if (!$product_id || !get_post($product_id) || get_post_type($product_id) !== 'product') {
      return new WP_Error('invalid_product', "Invalid product ID", array('status' => 400));
    }

    // Get the WooCommerce product object
    $product = wc_get_product($product_id);

    if (!$product) {
      return new WP_Error('product_not_found', "Product not found", array('status' => 404));
    }

    // Extract attributes from JSON request
    $new_attributes = isset($params['attributes']) ? $params['attributes'] : array();

    if (empty($new_attributes) || !is_array($new_attributes)) {
      return new WP_Error('no_attributes', "Invalid attributes formatting", array('status' => 400));
    }

    // Retrieve existing attributes
    $existing_attributes = $product->get_attributes();

    // Loop through new attributes and update/add them
    foreach ($new_attributes as $attribute) {
      if (!isset($attribute['name']) || !isset($attribute['value'])) {
        return new WP_Error('invalid_attribute', "Each attribute must include 'name' and 'value'", array('status' => 400));
      }

      $attr_name  = sanitize_title($attribute['name']);
      $attr_value = is_array($attribute['value']) ? array_map('sanitize_text_field', $attribute['value']) : array(sanitize_text_field($attribute['value']));
      $taxonomy   = wc_attribute_taxonomy_name($attr_name);

      // Check if it's a global attribute (taxonomy-based)
      if (taxonomy_exists($taxonomy)) {
        // Ensure terms exist before assigning
        $term_ids = array();

        foreach ($attr_value as $term_name) {
          $term = term_exists($term_name, $taxonomy);

          if (!$term) {
            $term = wp_insert_term($term_name, $taxonomy);
          }

          if (!is_wp_error($term)) {
            $term_ids[] = (int) $term['term_id'];
          }
        }

        // Set taxonomy-based attribute
        $existing_attributes[$taxonomy] = new WC_Product_Attribute();
        $existing_attributes[$taxonomy]->set_id(wc_attribute_taxonomy_id_by_name($attr_name));
        $existing_attributes[$taxonomy]->set_name($taxonomy);
        $existing_attributes[$taxonomy]->set_options($term_ids);
        $existing_attributes[$taxonomy]->set_position(0);
        $existing_attributes[$taxonomy]->set_visible(true);
        $existing_attributes[$taxonomy]->set_variation(false);
      } else {
        return new WP_Error('attribute_does_not_exist', "The attribute ". $attr_name . " does not exist.", array('status' => 400));
      }
    }

    // Update product attributes
    $product->set_attributes($existing_attributes);

    try {
      $product->save();
    } catch (Exception $e) {
      return new WP_Error('product_exception', 'An error occurred when attempting to save the product.', array('status' => 500));
    }

    return new WP_REST_Response(array(
      'success'    => true,
      'message'    => "Product attributes updated successfully.",
      'product_id' => $product_id,
    ), 200);
  }

  /**
   * Get product brands
   *
   * @param  WP_REST_Request $request
   * @return WP_REST_Response
   */
  public function get_product_brands(WP_REST_Request $request) {
    $params   = $request->get_params();
    $taxonomy = isset($params['taxonomy']) ? sanitize_text_field($params['taxonomy']) : 'product_brand';
    $limit    = isset($params['limit']) && is_numeric($params['limit']) ? absint($params['limit']) : false;
    
    $args = array(
      'taxonomy'   => $taxonomy,
      'hide_empty' => false,
    );

    if ($limit) {
      $args['number'] = $limit;
    }

    $terms = get_terms($args);

    if (is_wp_error($terms)) {
      return new WP_Error('get_terms_failed', 'Failed to get product brands', array('status' => 500));
    }

    $brands = array();

    foreach ($terms as $term) {
      $brands[] = array(
        'id'    => $term->term_id,
        'name'  => $term->name,
        'slug'  => $term->slug,
        'count' => $term->count,
      );
    }

    return new WP_REST_Response(array(
      'success' => true,
      'data'    => $brands,
    ), 200);
  }

  /**
   * Update product brands
   *
   * @param  WP_REST_Request $request
   * @return WP_REST_Response
   */
  public function update_product_brands(WP_REST_Request $request) {
    $params       = $request->get_json_params();
    $product_id   = isset($params['product_id']) && is_numeric($params['product_id']) ? (int) $params['product_id'] : 0;
    $brands       = isset($params['brands']) ? array_map('sanitize_text_field', $params['brands']) : array();
    $taxonomy     = isset($params['taxonomy']) ? $params['taxonomy'] : 'product_brand';
    $append       = empty($params['append']);
    $create_brand = !empty($params['create_brand']);

    if (!$product_id || !is_numeric($product_id)) {
      return new WP_Error('invalid_product_id', 'Valid Product ID is required', array('status' => 400));
    }

    if (!is_array($brands) || empty($brands)) {
      return new WP_Error('invalid_brands', 'Brands must be a non-empty array', array('status' => 400));
    }

    $product = wc_get_product($product_id);

    if (!$product) {
      return new WP_Error('product_not_found', 'Product not found', array('status' => 404));
    }

    $term_ids = array();

    foreach ($brands as $brand) {
      $term = null;

      // Numeric: try by ID
      if (is_numeric($brand)) {
        $term = get_term($brand, $taxonomy);
      } else {
        // First try by slug
        $term = get_term_by('slug', sanitize_title($brand), $taxonomy);

        // Then try by name if slug didn't match
        if (!$term) {
          $term = get_term_by('name', $brand, $taxonomy);
        }

        // If still not found, create it (only if allowed)
        if (!$term && $create_brand) {
          $new_term = wp_insert_term($brand, $taxonomy);

          if (!is_wp_error($new_term)) {
            $term = get_term($new_term['term_id'], $taxonomy);
          }
        }
      }

      if ($term && !is_wp_error($term)) {
        $term_ids[] = intval($term->term_id);
      }
    }

    if (empty($term_ids)) {
      return new WP_Error('no_valid_terms', 'No valid brand terms found or created', array('status' => 400));
    }

    $result = wp_set_object_terms($product_id, $term_ids, $taxonomy, $append);

    if (is_wp_error($result)) {
      return new WP_Error('update_failed', 'Failed to update product brands', array('status' => 500));
    }

    return new WP_REST_Response(array(
      'success' => true,
      'message' => 'Product brands updated successfully',
      'data'    => $term_ids,
    ), 200);
  }
  
  /**
   * Register settings page 
   *
   * @return void
   */
  public function register_settings_page() {
		add_menu_page(
			'Content API',                  // Page title
			'Content API',                  // Menu title
			'manage_options',               // Capability
			'content-api-settings',         // Menu slug (same as settings page)
			array(),                        // Function to display the content (settings page)
			'dashicons-welcome-write-blog', // Icon URL (WordPress Dashicons)
			5                               // Position in the menu
		);

    add_submenu_page(
			'content-api-settings',              // Parent slug (same as main menu)
			'Settings',                          // Page title
			'Settings',                          // Menu title
			'manage_options',                    // Capability
			'content-api-settings',              // Menu slug
			array($this, 'create_settings_page') // Function to display the content
		);
	}
  
  /**
   * Create settings page
   *
   * @return void
   */
  public function create_settings_page() {
		$this->options = get_option('content_api_options_polyplugins');
		?>

		<div class="wrap">
			<h2>Content API Settings</h2>
			<p></p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields('content_api_option_group');
					do_settings_sections('content-api-settings');
					submit_button();
				?>
			</form>
		</div>
	<?php
	}
  
  /**
   * Init settings page
   *
   * @return void
   */
  public function settings_page_init() {
		$this->options = get_option('content_api_options_polyplugins');
		
		register_setting(
			'content_api_option_group', // option_group
			'content_api_options_polyplugins',      // option_name
			array($this, 'sanitize')        // sanitize_callback
		);

		// Token
		add_settings_section(
			'setting_section',         // id
			'',                        // title
			array(),                   // callback
			'content-api-settings' // page
		);

		add_settings_field(
			'token',                        // id
			'Token',                        // title
			array($this, 'token_callback'), // callback
			'content-api-settings',         // page
			'setting_section'               // section
		);

		add_settings_field(
			'last_accessed_enabled',
			'Last Accessed Notice',
			array($this, 'last_accessed_enabled_callback'),
			'content-api-settings',
			'setting_section'
		);

		add_settings_field(
			'last_accessed_error_time',
			'Last Accessed Error Time',
			array($this, 'last_accessed_error_time_callback'),
			'content-api-settings',
			'setting_section'
		);
	}
  
  /**
   * Token callback
   *
   * @return void
   */
  public function token_callback() {
    $option = isset($this->options['token']) ? sanitize_text_field($this->options['token']) : '';
    ?>
		<input class="regular-text" type="password" name="content_api_options_polyplugins[token]" id="token" value="<?php echo esc_html($option); ?>">
    <?php
	}
  
  /**
   * Last Accessed Enabled callback
   *
   * @return void
   */
  public function last_accessed_enabled_callback() {
    $option = isset($this->options['last_accessed_enabled']) ? sanitize_text_field($this->options['last_accessed_enabled']) : '';
		?>
    <input type="checkbox" name="content_api_options_polyplugins[last_accessed_enabled]" id="last_accessed_enabled" <?php esc_attr(checked(1, $option, true)); ?> /> <?php echo esc_html__('Yes', 'content-api'); ?>
    <p>This logs when the API was last accessed and displays a notice in the admin when it was last accessed.</p>
    <?php
	}
  
  /**
   * Last Accessed Error Time callback
   *
   * @return void
   */
  public function last_accessed_error_time_callback() {
    $option = isset($this->options['last_accessed_error_time']) ? sanitize_text_field($this->options['last_accessed_error_time']) : '';
    ?>
		<input class="regular-text" type="number" name="content_api_options_polyplugins[last_accessed_error_time]" id="last_accessed_error_time" value="<?php echo esc_html($option); ?>">
    <p>Enter the number of minutes that the notice for last accessed should be turned red.</p>
    <?php
	}

  /**
   * Sanitize Options
   *
   * @param  array $input Array of option inputs
   * @return array $sanitary_values Array of sanitized options
   */
  public function sanitize($input) {
    $sanitary_values = array();

    if (isset($input['token']) && $input['token']) {
      $sanitary_values['token'] = sanitize_text_field($input['token']);
    }

    if (isset($input['last_accessed_enabled']) && $input['last_accessed_enabled']) {
      $sanitary_values['last_accessed_enabled'] = $input['last_accessed_enabled'] === 'on' ? true : false;
    } else {
      $sanitary_values['last_accessed_enabled'] = false;
    }

    if (isset($input['last_accessed_error_time']) && $input['last_accessed_error_time']) {
      $sanitary_values['last_accessed_error_time'] = intval($input['last_accessed_error_time']);
    }

    return $sanitary_values;
  }

  /**
   * Maybe display last access notice
   *
   * @param  mixed $request
   * @return true|WP_Error True if granted, error if not
   */
  public function maybe_display_last_accessed_notice() {
    $this->options = get_option('content_api_options_polyplugins');

    if (!$this->options['last_accessed_enabled']) {
      return;
    }

    $get_last_accessed = get_option('content_api_last_accessed_polyplugins');

    if (!$get_last_accessed) {
      return;
    }

    $last_accessed             = intval($get_last_accessed);
    $now                       = time();
    $threshold                 = isset($this->options['last_accessed_error_time']) ? intval($this->options['last_accessed_error_time']) : 0;
    $seconds_since_last_access = ($now - $last_accessed) / 60;
    $is_error                  = $threshold && $seconds_since_last_access > $threshold;
    $notice_class              = $is_error ? 'notice-error' : 'notice-success';
    $formatted_time            = wp_date(get_option('date_format') . ' ' . get_option('time_format'), $last_accessed);
    ?>
    <?php if (!$is_error) : ?>
      <div class="notice <?php echo esc_attr($notice_class); ?>">
        <p>Content API Last Accessed: <strong><?php echo esc_html($formatted_time); ?></strong></p>
      </div>
    <?php else : ?>
      <div class="notice <?php echo esc_attr($notice_class); ?>" style="background-color: #D63638; color: #fff;">
        <p>Content API Last Accessed: <strong><?php echo esc_html($formatted_time); ?></strong></p>
      </div>
    <?php endif; ?>
    <?php
  }
  
  /**
   * Checks if request is granted
   *
   * @param  mixed $request
   * @return true|WP_Error True if granted, error if not
   */
  public function has_permission(WP_REST_Request $request) {
    $this->options = get_option('content_api_options_polyplugins');
    $token         = $this->options['token'];
    $request_token = $this->get_request_token($request->get_header('authorization'));
    
    if ($request_token !== $token) {
      return new WP_Error('not_authorized', "Not Authorized", array('status' => 401));
    }

    if ($this->options['last_accessed_enabled']) {
      update_option('content_api_last_accessed_polyplugins', time());
    }
    
    return true;
  }
  
  /**
   * Create a category if it doesn't already exist
   *
   * @param  mixed $category_name
   * @return bool
   */
  private function maybe_create_category($category_name) {
    // Check if the category already exists
    $category = get_term_by('name', $category_name, 'category');

    if ($category) {
      return $category->term_id;
    }

    // Create the category if it does not exist
    $new_category = wp_insert_term($category_name, 'category');
    if (!is_wp_error($new_category)) {
      return $new_category['term_id'];
    }

    // Return false if there was an error
    return false;
  }
  
  /**
   * Create or update product attributes
   *
   * @param  array $attributes
   * @param  array $existing_attributes
   * @return void
   */
  private function create_or_update_product_attributes($attributes, $existing_attributes) {
    // Loop through new attributes and update/add them
    foreach ($attributes as $attribute) {
      if (!isset($attribute['name']) || !isset($attribute['value'])) {
        return new WP_Error('invalid_attribute', "Each attribute must include 'name' and 'value'", array('status' => 400));
      }

      $attr_name  = sanitize_text_field($attribute['name']);
      $attr_slug  = sanitize_title($attribute['name']);
      $attr_value = is_array($attribute['value']) ? array_map('sanitize_text_field', $attribute['value']) : array(sanitize_text_field($attribute['value']));
      $taxonomy   = wc_attribute_taxonomy_name($attr_slug);

      if (!taxonomy_exists($taxonomy)) {
        $attribute_args = array(
          'name' => $attr_name,
          'slug' => $attr_slug,
        );

        $create_taxonomy = wc_create_attribute($attribute_args);
        
        register_taxonomy(
          $taxonomy,
          apply_filters('woocommerce_taxonomy_objects_' . $taxonomy, array('product')),
          apply_filters('woocommerce_taxonomy_args_' . $taxonomy, array(
              'labels'       => array(
                'name' => $attr_name,
              ),
              'hierarchical' => true,
              'show_ui'      => false,
              'query_var'    => true,
              'rewrite'      => false,
            )
          )
        );
      }

      // Check if it's a global attribute (taxonomy-based)
      if (taxonomy_exists($taxonomy)) {
        // Ensure terms exist before assigning
        $term_ids = array();

        foreach ($attr_value as $term_name) {
          $term = term_exists($term_name, $taxonomy);

          if (!$term) {
            $term = wp_insert_term($term_name, $taxonomy);
          }

          if (!is_wp_error($term)) {
            $term_ids[] = (int) $term['term_id'];
          }
        }

        // Set taxonomy-based attribute
        $existing_attributes[$taxonomy] = new WC_Product_Attribute();
        $existing_attributes[$taxonomy]->set_id(wc_attribute_taxonomy_id_by_name($attr_slug));
        $existing_attributes[$taxonomy]->set_name($taxonomy);
        $existing_attributes[$taxonomy]->set_options($term_ids);
        $existing_attributes[$taxonomy]->set_position(0);
        $existing_attributes[$taxonomy]->set_visible(true);
        $existing_attributes[$taxonomy]->set_variation(false);
      } else {
        return new WP_Error('attribute_does_not_exist', "The attribute ". $attr_slug . " does not exist. Attempt to create failed.", array('status' => 400));
      }
    }

    return $existing_attributes;
  }
  
  /**
   * Get product IDs with missing descriptions
   *
   * @param  mixed $missing_description_limit The limit of missing descriptions
   * @return array $product_ids               The product ids with missing descriptions
   */
  private function get_product_ids_with_missing_descriptions($missing_description_limit = 100) {
    global $wpdb;

    $product_ids = $wpdb->get_col(
      $wpdb->prepare("
        SELECT ID 
        FROM $wpdb->posts 
        WHERE post_type = 'product' 
        AND (post_content = '' OR post_content IS NULL) 
        LIMIT %d
      ", $missing_description_limit)
    );

    return $product_ids;
  }

  /**
   * Build category hierarchy
   *
   * @param  mixed $term
   * @return array $category
   */
  private function build_category_hierarchy($term) {
    $category = array(
      'id'       => $term['id'],
      'name'     => $term['name'],
      'slug'     => $term['slug'],
      'children' => array(),
    );

    // Recursively add children if they exist
    if (!empty($term['children'])) {
      foreach ($term['children'] as $child) {
        $category['children'][] = $this->build_category_hierarchy($child);
      }
    }

    return $category;
  }
    
  /**
   * Check if any data is missing from the request
   *
   * @param  mixed $parameters
   * @return bool
   */
  private function data_missing($parameters) {
    $errors = array();

    foreach (self::REQUIRED_FIELDS as $field) {
      if (empty($parameters[$field])) {
        $errors[] = $field;
      }
    }

    if ($parameters['post_type'] === 'page') {
      $key = array_search("categories", $errors);

      unset($errors[$key]);
    }

    if (!empty($errors)) {
      $this->error = 'Missing parameters: ' . implode(', ', $errors);

      return true;
    }

    return false;
  }
  
  /**
   * Get the request token from the authorization header
   *
   * @param  string $authorization The bearer token
   * @return string $token         The token
   */
  private function get_request_token($authorization) {
    if ($authorization) {
      $parts = explode(' ', $authorization);

      if (isset($parts[1])) {
        $token = $parts[1];
      }
    }

    return $token;
  }
  
  /**
   * Replaces the image variables in content with it's image url
   *
   * @param  string $content The content
   * @param  array  $images  Array of images
   * @return string $content The content swapped variables
   */
  private function replace_image_variables($content, $images) {
    $replacements = array();

    foreach ($images as $image_key => $image_url) {
      $replacements['{{' . $image_key . '}}'] = $image_url;
    }
    
    $content = str_replace(array_keys($replacements), array_values($replacements), $content);

    return $content;
  }
  
  /**
   * Upload image to media library
   *
   * @param  string $image_url
   * @param  int    $post_id
   * @return int    $file_id
   */
  private function upload_image_to_media_library($image_url, $post_id) {
    // Include the necessary WordPress file for handling media and file uploads
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    $tmp = download_url($image_url);

    if (is_wp_error($tmp)) {
      return $tmp;
    }

    $file_array = array(
      'name'     => basename($image_url),
      'tmp_name' => $tmp,
    );

    // Check the file type and handle if it's valid
    $file_id = media_handle_sideload($file_array, $post_id);

    if (is_wp_error($file_id)) {
      @unlink($tmp);
      return;
    }

    // Clean up temporary file
    @unlink($tmp);

    return $file_id;
  }
  
  /**
   * Get error
   *
   * @return void
   */
  private function get_error() {
    $errors = $this->error;

    // Clear error after retrieval
    if (!empty($error)) {
      $this->error = '';
    }

    return $errors;
  }

}

$content_api = new Content_API;
$content_api->init();
