# Product Filter for Avada

A comprehensive WordPress/WooCommerce plugin that provides advanced product filtering functionality, specifically designed for seamless integration with the Avada theme and Fusion Builder.

## Features

- **Advanced Filtering**: Filter products by categories, attributes, and price ranges
- **Avada Theme Integration**: Fully compatible with Avada theme styling and components
- **Fusion Builder Support**: Drag-and-drop element for easy page building
- **AJAX Functionality**: Seamless filtering without page reloads
- **Responsive Design**: Mobile-friendly interface that works on all devices  
- **Shortcode Support**: Easy integration via shortcode `[avada_product_filter]`
- **Customizable**: Multiple configuration options and styling compatibility
- **Performance Optimized**: Efficient database queries and caching support

## Requirements

- WordPress 5.0 or higher
- WooCommerce 5.0 or higher
- PHP 7.4 or higher
- Avada Theme (recommended for full compatibility)

## Installation

1. Download the plugin files
2. Upload to your WordPress `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure the settings under Settings → Product Filter for Avada

## Usage

### Shortcode Usage

Use the shortcode anywhere in your content:

```
[avada_product_filter]
```

#### Shortcode Parameters

- `categories` - Comma-separated category IDs to filter specific categories
- `attributes` - Comma-separated attribute names to show specific attribute filters  
- `show_categories` - Show category filter (yes/no, default: yes)
- `show_attributes` - Show attribute filters (yes/no, default: yes)
- `show_price_filter` - Show price range filter (yes/no, default: yes)
- `columns` - Number of product columns (1-6, default: 3)
- `products_per_page` - Number of products per page (default: 12)
- `orderby` - Product ordering (menu_order, title, date, price, popularity, rating)
- `order` - Sort direction (ASC/DESC, default: ASC)

#### Example Usage

```
[avada_product_filter categories="12,15" columns="4" products_per_page="16" show_price_filter="yes"]
```

### Fusion Builder Integration

1. Edit your page with Fusion Builder
2. Add a new element
3. Search for "Product Filter" 
4. Drag and drop the element to your desired location
5. Configure the element options in the settings panel
6. Save and preview your page

## Configuration

### Plugin Settings

Navigate to **Settings → Product Filter for Avada** to configure:

- Enable/disable category filtering
- Enable/disable attribute filtering  
- Enable/disable AJAX functionality
- Show/hide product counts
- Additional display options

### Styling Customization

The plugin includes CSS that's compatible with Avada theme styling. You can further customize the appearance by:

1. Using Avada Theme Options
2. Adding custom CSS in Avada → Theme Options → Custom CSS
3. Modifying the plugin's CSS files (not recommended for updates)

## Filter Types

### Category Filter
- Dropdown selection of product categories
- Support for specific category restrictions
- Product count display option

### Attribute Filters  
- Checkbox-style attribute filtering
- Support for color, size, brand, and custom attributes
- Multiple attribute selection
- Product count per attribute option

### Price Range Filter
- Minimum and maximum price inputs
- Dynamic price range based on available products
- Compatible with WooCommerce currency settings

### Dimension Filters (Width / Depth / Area)

- Minimum and maximum numeric inputs for product dimensions (width, depth, area).
- Dimension filters are enabled by shortcode attribute `show_dimension_filter` (yes/no). Default: yes.
- The plugin uses hardcoded WooCommerce attribute taxonomies:
  - **Width**: `pa_leveys-cm` (values in centimeters)
  - **Depth**: `pa_syvyys-cm` (values in centimeters) 
  - **Area**: `pa_pinta-ala-m2` (values in square meters)

Technical notes:
- Attribute values must be numeric (e.g., `34`, `34.5`, `45.6`). Both integers and decimals are supported.
- The plugin converts attribute term names to numeric values for filtering.
- If a user accidentally sets min > max, the plugin will swap the values server-side to avoid empty results.
- Products must have the corresponding WooCommerce attributes assigned with numeric values.

Example shortcode enabling dimension filters:

```
[avada_product_filter show_dimension_filter="yes" columns="4" products_per_page="16"]
```## Developer Information

### Hooks and Filters

The plugin provides several hooks for developers:

```php
// Modify query arguments before filtering
add_filter('avada_product_filter_query_args', 'my_custom_query_args');

// Customize the filter output
add_action('avada_products_filtered', 'my_custom_filter_callback');
```

### File Structure

```
product-filter-for-avada/
├── product-filter-for-avada.php (Main plugin file)
├── includes/
│   ├── class-shortcode.php (Shortcode functionality)
│   ├── class-fusion-element.php (Fusion Builder integration)
│   ├── class-ajax-handler.php (AJAX request handling)
│   ├── class-admin.php (Admin interface)
│   └── fusion-preview.php (Fusion Builder preview)
├── assets/
│   ├── css/
│   │   ├── frontend.css (Frontend styles)
│   │   └── admin.css (Admin styles)
│   └── js/
│       └── frontend.js (Frontend JavaScript)
└── README.md
```

## Troubleshooting

### Common Issues

**Filter not working:**
- Ensure WooCommerce is installed and active
- Check that products have the required categories/attributes assigned
- Verify AJAX is enabled in plugin settings

**Styling issues with Avada:**
- Clear all caches (Avada, WordPress, hosting)
- Check for CSS conflicts in browser developer tools
- Ensure Avada theme is up to date

**JavaScript errors:**
- Check browser console for specific error messages
- Ensure jQuery is loaded on the page
- Verify no JavaScript conflicts with other plugins

### Performance Tips

- Use specific category/attribute restrictions to limit query scope
- Enable object caching if available on your hosting
- Consider pagination for large product catalogs
- Optimize product images for faster loading

## Support

For support, bug reports, and feature requests:

- **GitHub**: [https://github.com/ahautanen/product-filter-for-avada](https://github.com/ahautanen/product-filter-for-avada)
- **Issues**: Use GitHub Issues for bug reports
- **Documentation**: Check this README and plugin admin pages

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This plugin is licensed under the GPL v2 or later.

## Changelog

### Version 1.0.0
- Initial release
- Basic category and attribute filtering
- Avada theme integration
- Fusion Builder element support
- AJAX functionality
- Price range filtering
- Responsive design
- Admin settings panel
