# PTP Revolve-Style Product Detail Page v4

A WordPress/WooCommerce customization that transforms specific product detail pages into a modern, Revolve-style layout with enhanced user experience features.

## 🎯 Features

- **Targeted Implementation**: Only applies to specific product slugs (currently: `winter-soccer-clinic-rye-ny-january-11-2026-100-pm`)
- **Spacing-Safe**: Preserves existing vertical spacing, only adds minimal top margin
- **Modern Gallery**: Portrait layout with vertical thumbnails (desktop) and horizontal thumbnails (mobile)
- **Interactive Tabs**: Description, Location, Who's Attending, and Reviews sections
- **Event Chips**: Visual display of key event information (venue, date, time, ages)
- **Lazy Loading**: Maps and reviews load only when needed for better performance
- **Mobile Responsive**: Optimized for all screen sizes
- **Accessibility**: ARIA labels and proper semantic structure

## 📋 Requirements

- WordPress 5.0+
- WooCommerce 5.0+
- PHP 7.4+

## 🚀 Installation Options

### Option 1: Plugin Installation (Recommended)

1. Upload `ptp-revolve-style-pdp.php` to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The styling will automatically apply to targeted products

### Option 2: Theme Functions.php Integration

1. Copy the contents of `ptp-functions-php-version.php`
2. Paste it into your active theme's `functions.php` file
3. Save the file

## ⚙️ Configuration

### Product Attributes Required

Make sure your target products have these WooCommerce attributes set up:

- `pa_date_range` - Event date
- `pa_time_range` - Event time  
- `pa_ages` - Age range
- `pa_venue_name` - Venue name
- `pa_venue_address` - Venue address

### Custom Meta Fields

- `ptp_attending` - Comma-separated list of attendees (stored as post meta)

### Adding More Products

To target additional products, modify the `$allowed` array in the `ptp_is_target_pdp()` function:

```php
$allowed = array(
    'winter-soccer-clinic-rye-ny-january-11-2026-100-pm',
    'your-new-product-slug-here',
    'another-product-slug',
);
```

## 🎨 Customization

### Colors

The main brand color is defined as `#FCB900` (yellow). To change it, update these CSS variables:

```css
#ptp-revolve-v4 .tab.is-active{background:#FCB900;color:#0e0f11;border-color:#FCB900}
#ptp-revolve-v4 .chip{background:rgba(252,185,0,.14);border:1px solid #f3d37a;}
```

### Layout Breakpoints

- Desktop: 1100px+
- Mobile: Below 1100px

### Gallery Aspect Ratio

Images use a 4:5 aspect ratio. To change this, modify:

```css
#ptp-revolve-v4 .woocommerce-product-gallery__wrapper{aspect-ratio:4/5;}
```

## 🔧 Advanced Configuration

### Make Site-Wide

To apply to ALL product pages instead of just targeted ones, replace the `ptp_is_target_pdp()` function to simply return `is_product()`:

```php
function ptp_is_target_pdp() {
    return is_product();
}
```

### Trustindex Integration

The code includes lazy-loading support for Trustindex reviews. Make sure you have the Trustindex plugin installed and the shortcode `[trustindex no-registration=google]` configured.

### Google Maps Integration

Maps are embedded using Google Maps embed API. No API key required for basic embedding, but you may want to add one for enhanced features.

## 📱 Mobile Optimizations

- Horizontal thumbnail navigation
- Responsive typography scaling
- Touch-friendly button sizes
- Optimized spacing for mobile viewports

## 🐛 Troubleshooting

### Styling Not Appearing

1. Check that WooCommerce is active
2. Verify the product slug matches exactly
3. Clear any caching plugins
4. Check for theme conflicts

### JavaScript Not Working

1. Ensure no JavaScript errors in browser console
2. Check for jQuery conflicts
3. Verify the HTML structure is rendering correctly

### Map Not Loading

1. Check that venue address is properly set
2. Verify Google Maps embed URL is accessible
3. Check for content security policy restrictions

## 🔄 Updates

When updating:

1. Backup your current implementation
2. Test on a staging site first
3. Check for any custom modifications you've made
4. Update the version number in comments

## 📞 Support

For issues specific to this implementation:

1. Check the browser console for JavaScript errors
2. Verify WooCommerce product attributes are set correctly
3. Ensure the product slug matches the targeted slug exactly
4. Test with a default theme to rule out theme conflicts

## 🎯 Performance Notes

- CSS and JS only load on targeted product pages
- Maps and reviews use lazy loading
- Minimal impact on page load speed
- Optimized for Core Web Vitals

---

*Last updated: October 2024*
*Version: 4.0.0*