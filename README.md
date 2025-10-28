# PTP Revolve-Style Product Display Page (PDP) v4

This WordPress plugin creates a Revolve-style product display page for WooCommerce products, featuring a portrait gallery with vertical thumbnails, centered buy box, and minimal tabs.

## Features

- **Portrait Gallery**: Tall portrait images on the left with vertical thumbnail navigation
- **Centered Buy Box**: Wide, centered product information and purchase area
- **Minimal Tabs**: Clean tab interface for Description, Location, Who's Attending, and Reviews
- **Event Information**: Displays venue, date, time, and age information as chips
- **Responsive Design**: Mobile-optimized with horizontal thumbnails on smaller screens
- **Related Products**: 4-column grid of related products with portrait images

## Installation

### Method 1: Functions.php (Recommended for testing)
1. Copy the entire code from `ptp-revolve-style-pdp.php`
2. Add it to your theme's `functions.php` file
3. Save and test on a product page

### Method 2: Plugin File
1. Upload `ptp-revolve-style-pdp.php` to your `/wp-content/plugins/` directory
2. Activate the plugin in your WordPress admin
3. The styles will automatically apply to all product pages

## Requirements

- WordPress 5.0+
- WooCommerce 3.0+
- PHP 7.4+

## Customization

### Product Attributes
The plugin uses these WooCommerce product attributes:
- `pa_date_range` - Event date
- `pa_time_range` - Event time
- `pa_ages` - Age range
- `pa_venue_name` - Venue name
- `pa_venue_address` - Venue address

### Custom Meta
- `ptp_attending` - Comma-separated list of attendees (CSV format)

### Styling
The plugin includes comprehensive CSS that can be customized by targeting the `#ptp-revolve-v4` container and its child elements.

## Key Features Explained

### Gallery Layout
- Main image displays in 4:5 aspect ratio (portrait)
- Vertical thumbnails on the left (desktop) or horizontal (mobile)
- Sticky positioning for thumbnails on desktop

### Buy Box
- Centered layout with large, bold typography
- Prominent call-to-action button with custom styling
- Event information chips below the title

### Tab System
- **Description**: Product short description
- **Location**: Venue details with optional Google Maps embed
- **Who's Attending**: List of attendees from custom meta
- **Reviews**: Trustindex integration for reviews

### Mobile Optimization
- Responsive grid that stacks on mobile
- Horizontal thumbnail navigation
- Full-width buttons and optimized spacing

## Troubleshooting

### Styles Not Loading
- Ensure WooCommerce is active
- Check that you're on a product page
- Verify the code is properly added to functions.php

### JavaScript Errors
- Check browser console for errors
- Ensure jQuery is loaded (WooCommerce dependency)
- Verify Trustindex shortcode is working

### Gallery Issues
- Ensure product has multiple images
- Check that WooCommerce gallery is enabled
- Verify FlexSlider is working properly

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## License

This code is provided as-is for educational and commercial use. Please test thoroughly before using on production sites.