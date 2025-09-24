# Product Filter for Avada

A comprehensive WordPress/WooCommerce plugin that provides an advanced product filter, designed to be fully compatible with the Avada theme and Fusion Builder. The filter can be integrated either via shortcode or as a block/element usable directly in Avada/Fusion Builder.

## Features

- **Avada Theme Integration**: Seamlessly integrates with Avada theme and Fusion Builder
- **Multiple Filter Types**: Filter by categories, attributes, and price range
- **AJAX Filtering**: Real-time filtering without page refresh
- **Flexible Layouts**: Sidebar, horizontal, and modal layout options
- **Shortcode Support**: Easy integration with `[product_filter_avada]` shortcode
- **Fusion Builder Element**: Native Fusion Builder element for drag-and-drop integration
- **Responsive Design**: Mobile-friendly and fully responsive
- **Customizable Styling**: Color options and custom CSS classes

## Requirements

- WordPress 5.0 or higher
- WooCommerce 5.0 or higher
- PHP 7.4 or higher
- Avada Theme (recommended for full integration)

## Installation

1. Upload the plugin files to the `/wp-content/plugins/product-filter-for-avada` directory
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to Settings > Product Filter Avada to configure the plugin

## Usage

### Shortcode Usage

Use the shortcode anywhere in your content:

```
[product_filter_avada]
```

#### Shortcode Parameters

- `categories` - Comma-separated category slugs to limit filtering
- `attributes` - Comma-separated attribute names to include
- `layout` - Layout type: `sidebar`, `horizontal`, or `modal`
- `show_count` - Show product count: `yes` or `no`
- `ajax` - Enable AJAX filtering: `yes` or `no`
- `products_per_page` - Number of products per page

#### Examples

```
[product_filter_avada layout="horizontal" show_count="yes" ajax="yes"]
[product_filter_avada categories="electronics,clothing" attributes="color,size"]
[product_filter_avada layout="modal" products_per_page="16"]
```

### Fusion Builder Integration

If you're using Avada theme, you can find the **Product Filter for Avada** element in Fusion Builder with additional styling options including:

- Filter background color
- Filter text color  
- Filter accent color
- Custom CSS classes

## Configuration

### Plugin Settings

Navigate to **Settings > Product Filter Avada** to configure:

#### General Settings
- Enable/disable AJAX filtering
- Set default filter layout

#### Filter Options
- Enable/disable category filtering
- Enable/disable attribute filtering
- Enable/disable price filtering

#### Display Settings
- Show/hide product counts
- Set default products per page

## File Structure

```
product-filter-for-avada/
├── assets/
│   ├── css/
│   │   ├── product-filter.css
│   │   └── admin.css
│   └── js/
│       ├── product-filter.js
│       └── admin.js
├── includes/
│   ├── class-product-filter-avada.php
│   ├── class-product-filter-shortcode.php
│   ├── class-product-filter-ajax.php
│   ├── class-product-filter-admin.php
│   └── class-fusion-builder-element.php
├── templates/
│   └── product-filter.php
├── languages/
├── product-filter-for-avada.php
└── README.md
```

## Hooks and Filters

The plugin provides several hooks for customization:

### Actions
- `product_filter_avada_before_filter` - Before filter form
- `product_filter_avada_after_filter` - After filter form
- `product_filter_avada_before_products` - Before products grid
- `product_filter_avada_after_products` - After products grid

### Filters
- `product_filter_avada_query_args` - Modify WP_Query arguments
- `product_filter_avada_categories` - Filter available categories
- `product_filter_avada_attributes` - Filter available attributes

## Styling

The plugin includes comprehensive CSS that works well with Avada theme. You can customize the appearance through:

1. **Fusion Builder Options** - When using the Fusion Builder element
2. **Custom CSS** - Add custom styles to your theme
3. **Theme Customizer** - Use WordPress Customizer additional CSS

### CSS Classes

- `.product-filter-wrapper` - Main container
- `.product-filter-section` - Filter area
- `.product-results-section` - Products area
- `.products-grid` - Products grid container
- `.product-item` - Individual product item

## Troubleshooting

### Common Issues

1. **No products showing**: Ensure WooCommerce is installed and you have published products
2. **AJAX not working**: Check that jQuery is loaded and no JavaScript errors in console
3. **Styling issues**: Clear any caching plugins and check for theme conflicts

### Support

For support and bug reports, please visit the [GitHub repository](https://github.com/ahautanen/product-filter-for-avada).

## License

This plugin is licensed under the GPL v2 or later.

## Changelog

### 1.0.0
- Initial release
- Basic filtering functionality
- Avada/Fusion Builder integration
- AJAX support
- Multiple layout options
