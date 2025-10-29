# PTP Revolve-Style PDP v4 - WordPress Installation Guide

## Overview
This plugin customizes WooCommerce product detail pages (PDPs) for event listings with a Revolve-inspired design featuring:
- Portrait-oriented product gallery with vertical thumbnails (desktop) / horizontal (mobile)
- Event chips (venue, date, time, ages)
- Custom tabs (Description, Location, Who's Attending, Reviews)
- Lazy-loaded Google Maps integration
- Lazy-loaded Trustindex reviews

## Installation Methods

### Method 1: As a Plugin (Recommended)

1. **Upload the Plugin:**
   - Upload `ptp-revolve-pdp-v4.php` to `/wp-content/plugins/` directory
   - Or create a folder: `/wp-content/plugins/ptp-revolve-pdp-v4/` and place the file inside
   
2. **Activate:**
   - Go to WordPress Admin → Plugins
   - Find "PTP — Revolve-Style PDP v4"
   - Click "Activate"

### Method 2: Add to Theme Functions

1. **Copy the code:**
   - Open `ptp-revolve-pdp-v4.php`
   - Copy all code EXCEPT the plugin header comments
   
2. **Paste into theme:**
   - Go to `wp-content/themes/your-theme/functions.php`
   - Paste at the bottom of the file
   
3. **Or create a custom functions file:**
   - Create: `wp-content/themes/your-theme/inc/ptp-pdp.php`
   - Paste the code there
   - Add to `functions.php`: `require_once get_template_directory() . '/inc/ptp-pdp.php';`

## Configuration

### 1. Target Specific Products

By default, the plugin only affects products with this slug:
- `winter-soccer-clinic-rye-ny-january-11-2026-100-pm`

**To add more products:**

```php
function ptp_is_target_pdp() {
  if ( ! is_product() ) return false;
  $obj = get_queried_object();
  if ( empty($obj) || empty($obj->post_name) ) return false;
  $allowed = array(
    'winter-soccer-clinic-rye-ny-january-11-2026-100-pm',
    'summer-soccer-camp-2026',           // Add your product slugs here
    'fall-basketball-clinic-2026',       // Add more as needed
  );
  return in_array( $obj->post_name, $allowed, true );
}
```

**To apply to ALL products:**

Replace `ptp_is_target_pdp()` with `is_product()` throughout the file (see bottom comment).

### 2. Set Up Product Attributes

Your WooCommerce products need these attributes:

| Attribute Slug | Label | Example |
|---------------|-------|---------|
| `pa_date_range` | Date Range | January 11, 2026 |
| `pa_time_range` | Time Range | 1:00 PM - 3:00 PM |
| `pa_ages` | Ages | 8-14 |
| `pa_venue_name` | Venue Name | Rye Recreation Park |
| `pa_venue_address` | Venue Address | 281 Midland Ave, Rye, NY 10580 |

**Create attributes in WordPress:**
1. Go to: Products → Attributes
2. Create each attribute with the exact slug above
3. Add values to each attribute
4. Assign to your products

### 3. Set Who's Attending (Custom Meta)

Add attendee names via custom meta field:

```php
// Add to your product edit screen
update_post_meta( $product_id, 'ptp_attending', 'John Smith, Sarah Johnson, Mike Davis' );
```

Or use a plugin like ACF (Advanced Custom Fields):
- Field Name: `ptp_attending`
- Field Type: Text
- Format: CSV (comma-separated names)

### 4. Trustindex Reviews Integration

The plugin looks for `[trustindex]` shortcode.

**If using Trustindex plugin:**
- Install Trustindex plugin
- Configure your Google reviews
- The Reviews tab will auto-populate

**Without Trustindex:**
- The Reviews tab will show an empty placeholder
- You can customize it by editing line 119 in the plugin

## Customization

### Change Brand Colors

Current brand color: `#FCB900` (yellow)

Find and replace `#FCB900` with your color:
```css
#ptp-revolve-v4 .chip{background:rgba(252,185,0,.14);border:1px solid #f3d37a}
#ptp-revolve-v4 .tab.is-active{background:#FCB900;color:#0e0f11;border-color:#FCB900}
#ptp-revolve-v4 .att li{border-left:3px solid #FCB900}
```

### Adjust Gallery Aspect Ratio

Default: 4:5 portrait

```css
#ptp-revolve-v4 .woocommerce-product-gallery__wrapper{aspect-ratio:4/5;min-height:600px}
```

Change to 3:4: `aspect-ratio:3/4`  
Change to square: `aspect-ratio:1/1`

### Add More Tabs

Edit the function at line 75:

```php
echo '<div class="pr-tabs" role="tablist">'
   . '<button class="tab is-active" role="tab" aria-controls="tab-desc">Description</button>'
   . '<button class="tab" role="tab" aria-controls="tab-loc">Location</button>'
   . '<button class="tab" role="tab" aria-controls="tab-att">Who\'s Attending</button>'
   . '<button class="tab" role="tab" aria-controls="tab-faq">FAQ</button>' // NEW TAB
   . '<button class="tab" role="tab" aria-controls="tab-rev">Reviews</button>'
   . '</div>';

// Add corresponding panel
echo '<section id="tab-faq" class="panel" role="tabpanel">'
   . '<p>Your FAQ content here</p>'
   . '</section>';
```

## Requirements

- **WordPress:** 5.8+
- **PHP:** 7.4+
- **WooCommerce:** 5.0+
- **Theme:** Must support WooCommerce product galleries

## Troubleshooting

### Styling Doesn't Apply

1. **Check product slug:**
   - Go to product edit screen
   - Check the permalink slug matches your `$allowed` array
   
2. **Clear cache:**
   - Clear WP cache plugins (WP Rocket, W3 Total Cache, etc.)
   - Clear browser cache
   - Use incognito mode to test

3. **Check WooCommerce is active:**
   - Go to Plugins
   - Ensure WooCommerce is activated

### Gallery Not Showing

- Ensure your theme supports WooCommerce product galleries
- Check if product has a featured image + gallery images
- Try a default theme (Storefront) to test

### Map Not Loading

- Verify `pa_venue_address` or `pa_venue_name` attribute has a value
- Check browser console for errors
- Google Maps embed may be blocked by privacy plugins

### Tabs Not Working

- Check browser console for JavaScript errors
- Ensure no conflicts with other plugins
- Disable other custom JS temporarily to test

## Support

For custom development or issues:
- Check WooCommerce version compatibility
- Test with default theme
- Disable other plugins to isolate conflicts

## License

This plugin is provided as-is for PTP event products.
