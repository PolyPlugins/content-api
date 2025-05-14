![Content API Banner](.wordpress-org/banner-1880x609.png)

## About

Content API offers a powerful and flexible solution for managing your site’s posts, products, SEO settings, and more through custom endpoints. By creating tailored REST API endpoints, the plugin enables you to perform actions such as updating product attributes, managing content, and even optimizing SEO all from external systems or custom-built applications.

Whether you’re managing an eCommerce store, handling content on a blog, or controlling SEO configurations, this plugin provides the tools to do it efficiently. The custom API endpoints support secure, token-based authentication to ensure only authorized users can interact with your WordPress site.

With features like bulk updates for product attributes, streamlined content management, and simplified SEO configurations, this plugin enhances the flexibility and automation of your WordPress site, making it easier to integrate with third-party tools and manage everything from a centralized platform.

[Documentation](https://www.polyplugins.com/docs/content-api/)

## Features

- Create new posts
- Fetch products
- Update products
- Fetch attributes
- Update attributes
- Fetch terms
- Update terms
- Capability to add Yoast title, description, social titles, social descriptions, and social images when adding a post or updating a product
- Secure access through API tokens for authentication
- Admin page to manage the plugin settings, including the API token

## GDPR

We are not lawyers and always recommend doing your own compliance research into third-party plugins, libraries, etc., as we've seen other plugins not be in compliance with these regulations.

This plugin uses the Bootstrap, BootStrap Icons, and SweetAlert2 third-party libraries. These libraries are loaded locally to be compliant with data protection regulations.

This plugin collects and stores certain data on your server to ensure proper functionality. This includes:

- Storing plugin settings
- Remembering which notices have been dismissed

## Installation

1. Backup WordPress
2. Upload the plugin files to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
3. Activate the plugin through the 'Plugins' screen in WordPress
4. Configure the plugin

## Frequently Asked Questions

### What is this plugin used for?

This plugin allows you to manage your WordPress site's posts, products, SEO settings, and more via custom REST API endpoints. It helps automate and streamline content management and product updates, making it easier to integrate with external applications or systems.

### How do I authenticate API requests?

The plugin uses token-based authentication. You need to generate a token from the plugin's settings page and include it in the Authorization header of your API requests.

### Can I update product attributes with this plugin?

Yes, you can update WooCommerce product attributes via the custom API endpoints provided by the plugin. This includes adding or modifying attributes like size, color, and more.

### Can I integrate this plugin with third-party applications?

Yes, the plugin's custom REST API endpoints are designed to be easily integrated with third-party applications, giving you the flexibility to automate workflows and manage your site remotely.

### Can I upload images through the API?

Yes, the plugin supports uploading images to the WordPress media library via the custom API. You can include image URLs in your requests, and the plugin will handle the upload process.

## Changelog

### 1.0.6
* Added: upc attribute to results of [Get Product](https://www.polyplugins.com/docs/content-api/api/get-product/) endpoint
* Added: upc attribute to [Update Product](https://www.polyplugins.com/docs/content-api/api/update-product/) endpoint
* Added: upc attribute to [Create Product](https://www.polyplugins.com/docs/content-api/api/create-product/) endpoint

### 1.0.5
* Added: status attribute to [Update Product](https://www.polyplugins.com/docs/content-api/api/update-product/) endpoint

### 1.0.4
* Added: [Create Product](https://www.polyplugins.com/docs/content-api/api/create-product/) endpoint
* Added: cost, map_price, and categories attributes to [Get Product](https://www.polyplugins.com/docs/content-api/api/get-product/) endpoint
* Added: cost, map_price, and categories attributes to [Update Product](https://www.polyplugins.com/docs/content-api/api/update-product/) endpoint
* Bugfix: Get Product endpoint error when fetching images

### 1.0.3

* Added: [Get Product Category](https://www.polyplugins.com/docs/content-api/api/get-product-category/) endpoint
* Added: [Update Product Category](https://www.polyplugins.com/docs/content-api/api/update-product-category/) endpoint
* Added: [Create Product Category](https://www.polyplugins.com/docs/content-api/api/create-product-category/) endpoint
* Updated: [Get Brand](https://www.polyplugins.com/docs/content-api/api/get-brand/) endpoint (Previous endpoint is an alias)
* Updated: [Update Brand](https://www.polyplugins.com/docs/content-api/api/update-brand/) endpoint (Previous endpoint is an alias)
* Updated: [Create Brand](https://www.polyplugins.com/docs/content-api/api/create-brand/) endpoint (Previous endpoint is an alias)

### 1.0.2

* Added: [Get Brand](https://www.polyplugins.com/docs/content-api/api/get-brand/) endpoint
* Added: [Update Brand](https://www.polyplugins.com/docs/content-api/api/update-brand/) endpoint
* Added: [Create Brand](https://www.polyplugins.com/docs/content-api/api/create-brand/) endpoint

### 1.0.1

* Added: [Get Product Brands](https://www.polyplugins.com/docs/content-api/api/get-product-brands/) endpoint
* Added: [Update Product Brands](https://www.polyplugins.com/docs/content-api/api/update-product-brands/) endpoint

### 1.0.0

* Initial Release