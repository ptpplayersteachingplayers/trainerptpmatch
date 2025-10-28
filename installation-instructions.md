# PTP Revolve-Style PDP v4 Installation Instructions

## Installation Options

### Option 1: As a WordPress Plugin (Recommended)

1. **Upload the Plugin:**
   - Upload `ptp-revolve-style-pdp-v4.php` to your `/wp-content/plugins/` directory
   - Or create a new folder `/wp-content/plugins/ptp-revolve-style-pdp/` and place the file inside
   - Activate the plugin from the WordPress admin dashboard

### Option 2: Add to Theme Functions

1. **Add to functions.php:**
   - Copy the code from `ptp-revolve-style-pdp-v4.php` (excluding the plugin header)
   - Paste it into your active theme's `functions.php` file
   - Remove the plugin header comments and the `new PTP_Revolve_Style_PDP_v4();` line
   - Add `new PTP_Revolve_Style_PDP_v4();` at the end

### Option 3: As a Must-Use Plugin

1. **Upload to mu-plugins:**
   - Upload `ptp-revolve-style-pdp-v4.php` to `/wp-content/mu-plugins/` directory
   - The plugin will be automatically activated

## Requirements

- WordPress 5.0 or higher
- WooCommerce 3.0 or higher
- PHP 7.4 or higher

## Product Setup

For the plugin to work properly, ensure your WooCommerce products have:

1. **Product Gallery Images:** Add multiple images to the product gallery
2. **Product Attributes:** Set up these custom attributes:
   - `pa_date_range` (Date Range)
   - `pa_time_range` (Time Range) 
   - `pa_ages` (Ages)
   - `pa_venue_name` (Venue Name)
   - `pa_venue_address` (Venue Address)
3. **Custom Meta:** Add `ptp_attending` meta field with comma-separated attendee names

## Features

- **Revolve-style Layout:** Portrait gallery with vertical thumbnails + centered buy box
- **Custom Tabs:** Description, Location, Who's Attending, Reviews
- **Responsive Design:** Mobile-optimized with horizontal thumbnails on smaller screens
- **Interactive Map:** Lazy-loaded Google Maps integration
- **Related Products:** 4-column grid layout matching the design aesthetic

## Troubleshooting

1. **Styles not loading:** Ensure no caching plugins are interfering
2. **Layout issues:** Check for theme CSS conflicts
3. **Tabs not working:** Verify JavaScript is not being blocked
4. **Map not loading:** Check Google Maps embedding permissions

## Customization

The plugin uses CSS custom properties (variables) for easy theming:
- `--y`: Primary yellow color (#FCB900)
- `--ink`: Text color (#0e0f11)
- `--muted`: Muted text color (#6b7280)
- `--b`: Border color (#e5e7eb)
- `--r`: Border radius (14px)

Modify these in the CSS section to match your brand colors.