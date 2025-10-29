# PTP Revolve-Style Product Detail Page (PDP) v4

A WordPress plugin that transforms WooCommerce product detail pages into a modern, Revolve-style layout with targeted styling and enhanced functionality.

## Features

- **Targeted Implementation**: Only applies to specific product slugs (currently configured for `winter-soccer-clinic-rye-ny-january-11-2026-100-pm`)
- **Spacing-Safe Design**: Maintains existing vertical spacing without adding unwanted margins/padding
- **Modern Gallery Layout**: Portrait gallery with vertical thumbnails on desktop, horizontal on mobile
- **Interactive Tabs**: Description, Location, Who's Attending, and Reviews tabs
- **Event Information Chips**: Displays venue, date, time, and age range in styled chips
- **Lazy Loading**: Maps and reviews load only when needed
- **Accessibility**: Full ARIA support and keyboard navigation
- **Responsive Design**: Optimized for all screen sizes

## Installation

1. Upload the `ptp-revolve-style-pdp.php` file to your WordPress site's `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The plugin will automatically apply to the configured target product

## Configuration

### Adding More Target Products

To apply this styling to additional products, edit the `$allowed` array in the `ptp_is_target_pdp()` function:

```php
$allowed = array(
    'winter-soccer-clinic-rye-ny-january-11-2026-100-pm',
    'your-new-product-slug',
    'another-product-slug',
);
```

### Product Attributes

The plugin expects these WooCommerce product attributes:
- `pa_date_range` - Event date range
- `pa_time_range` - Event time range  
- `pa_ages` - Age range for participants
- `pa_venue_name` - Venue name
- `pa_venue_address` - Venue address

### Custom Meta Fields

- `ptp_attending` - Comma-separated list of attendees (stored as post meta)

## Styling Customization

The CSS is embedded in the plugin and can be customized by modifying the styles in the `wp_head` action. Key CSS classes:

- `#ptp-revolve-v4` - Main container
- `.pr-wrap` - Grid layout wrapper
- `.pr-media` - Gallery section
- `.pr-info` - Product information section
- `.pr-header` - Chips container
- `.pr-tabs` - Tab navigation
- `.pr-panels` - Tab content panels

## Making It Site-Wide

To apply this styling to all WooCommerce products instead of just targeted ones, replace:

```php
if ( ! ptp_is_target_pdp() ) return;
```

with:

```php
if ( ! is_product() ) return;
```

## Requirements

- WordPress 5.0+
- WooCommerce 3.0+
- PHP 7.4+

## Browser Support

- Chrome 60+
- Firefox 60+
- Safari 12+
- Edge 79+

## Notes

- The plugin removes default WooCommerce tabs and meta information
- Price and rating are repositioned for better visual hierarchy
- Maps use Google Maps embed with lazy loading
- Reviews integration supports Trustindex plugin
- All styling is spacing-safe and won't interfere with theme layouts