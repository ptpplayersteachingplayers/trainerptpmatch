# PTP Revolve-Style PDP v4 - Installation Guide

## What was fixed?

The original code was missing:
1. ✅ Opening `<?php` tag
2. ✅ Proper plugin headers for WordPress
3. ✅ Security check (`ABSPATH`)
4. ✅ Proper priority values for `wp_head` and `wp_footer` hooks

## Installation Options

### Option 1: Install as a Plugin (RECOMMENDED)

1. **Upload the plugin:**
   - Go to WordPress Admin → Plugins → Add New → Upload Plugin
   - Upload the `ptp-revolve-pdp-v4.php` file
   - Click "Install Now" then "Activate"

2. **Verify WooCommerce is active:**
   - Make sure WooCommerce is installed and activated
   - This code requires WooCommerce to function

3. **Test on a product page:**
   - Go to any WooCommerce product page
   - You should see the new Revolve-style layout

### Option 2: Add to functions.php

If you prefer to add this to your theme:

1. **Go to:** Appearance → Theme File Editor
2. **Select:** functions.php (on the right sidebar)
3. **Copy everything** from line 14 onwards in `ptp-revolve-pdp-v4.php` (skip the plugin header)
4. **Paste at the bottom** of your functions.php file
5. **Click "Update File"**

⚠️ **Warning:** Use a child theme if possible, or your changes will be lost on theme updates.

### Option 3: Use Code Snippets Plugin (SAFEST)

1. **Install:** [Code Snippets](https://wordpress.org/plugins/code-snippets/) plugin
2. **Go to:** Snippets → Add New
3. **Copy** everything from line 14 onwards in `ptp-revolve-pdp-v4.php`
4. **Paste** into the snippet
5. **Set:** "Run snippet everywhere"
6. **Save and Activate**

## Required Product Attributes

For full functionality, create these WooCommerce product attributes:

| Attribute Slug | Name | Used For |
|---|---|---|
| `pa_date_range` | Date Range | Event date chip |
| `pa_time_range` | Time Range | Event time chip |
| `pa_ages` | Ages | Age group chip |
| `pa_venue_name` | Venue Name | Location chip & map |
| `pa_venue_address` | Venue Address | Map & directions |

### Creating Attributes:

1. Go to **Products → Attributes**
2. Add each attribute with the slug exactly as shown above
3. Check "Enable archives" if you want filterable listings
4. When editing products, go to **Attributes tab** and add values

## Custom Fields

Add this custom field for "Who's Attending":

- **Field name:** `ptp_attending`
- **Value:** Comma-separated list (e.g., "John Doe, Jane Smith, Coach Mike")

## Trustindex Integration

The Reviews tab loads Trustindex reviews. Make sure:

1. **Trustindex plugin is installed**
2. The shortcode `[trustindex no-registration=google]` works
3. Or modify line 171 in the PHP file to use your review shortcode

## Disabling Old Versions

If you have older v2/v3/v3.1 versions active:

1. **Deactivate or remove** old PTP PDP snippets
2. **Search your theme** for similar customizations
3. **Clear cache** after activation

## Troubleshooting

### Layout not showing?

- ✅ Confirm you're on a WooCommerce product page
- ✅ Check WooCommerce is active
- ✅ Clear all caches (theme, plugin, browser)
- ✅ Check for JavaScript errors in browser console (F12)

### Gallery not working?

- ✅ Add multiple images to the Product Gallery section
- ✅ Make sure your theme supports WooCommerce gallery

### Tabs not switching?

- ✅ Check browser console for JavaScript errors
- ✅ Make sure no other plugin is conflicting
- ✅ Try disabling other custom scripts temporarily

### Map not loading?

- ✅ Add the `pa_venue_address` or `pa_venue_name` attribute
- ✅ Click "Tap to load map" button on the Location tab

## Support

Need help? Check:
- WooCommerce is version 3.0+
- WordPress is version 5.0+
- PHP version is 7.2+
- No JavaScript errors in console

---

**Created for:** PTP Events  
**Style:** Revolve-inspired Product Detail Page  
**Version:** 4.0.0
