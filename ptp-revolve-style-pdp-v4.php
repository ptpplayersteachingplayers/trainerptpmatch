<?php
/**
 * Plugin Name: PTP Revolve-Style PDP v4
 * Description: Revolve-style product display page with portrait gallery, centered buy box, and related products
 * Version: 4.0.0
 * Author: PTP
 * Requires at least: 5.0
 * Tested up to: 6.4
 * WC requires at least: 3.0
 * WC tested up to: 8.0
 * 
 * PTP — Revolve‑Style PDP v4 (Portrait Gallery + Centered Buy Box + Related)
 * Goal: Mimic Revolve PDP — tall portrait media left w/ vertical thumbs, 
 *       wide centered title + pay box right, minimal tabs (Description | Location | Who's Attending | Reviews),
 *       and a clean related-products row.
 *
 * Notes:
 * - Disable older v2/v3/v3.1 snippets to avoid duplication.
 * - Uses Woo core gallery/images; add multiple gallery images in the product as usual.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    return;
}

/**
 * Helper function to get current product
 */
if (!function_exists('ptp_current_product')) {
    function ptp_current_product() {
        if (!is_product()) {
            return null;
        }
        global $product;
        return ($product instanceof WC_Product) ? $product : null;
    }
}

/**
 * Initialize the plugin
 */
class PTP_Revolve_Style_PDP_v4 {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        // Only run on product pages
        if (!is_admin()) {
            add_action('wp', array($this, 'setup_product_page'));
        }
    }
    
    public function setup_product_page() {
        if (!is_product()) {
            return;
        }
        
        $this->woo_cleanups_and_ordering();
        $this->setup_product_layout();
        $this->setup_related_products();
        $this->add_styles();
        $this->add_scripts();
    }
    
    /**
     * WooCommerce cleanups & ordering
     */
    private function woo_cleanups_and_ordering() {
        // Remove Woo default tabs block (we render custom tabs)
        remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
        
        // Remove short description from summary; we'll show it in Description tab
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
        
        // Remove SKU/Categories/Tags meta row
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
        
        // Ensure price shows ONCE, under title; remove default then reinsert later
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
        add_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 12);
        
        // Keep rating close to price
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
        add_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 13);
        
        // Remove only Additional Info tab if any plugin adds it back
        add_filter('woocommerce_product_tabs', function($tabs) {
            unset($tabs['additional_information']);
            return $tabs;
        }, 98);
    }
    
    /**
     * Setup product page layout
     */
    private function setup_product_layout() {
        // Shell: open (gallery col + info col)
        add_action('woocommerce_before_single_product_summary', array($this, 'open_product_wrapper'), 1);
        
        // Close media col, open info col wrapper
        add_action('woocommerce_before_single_product_summary', array($this, 'close_media_open_info'), 99);
        
        // After summary: header chips + tabs & panels
        add_action('woocommerce_after_single_product_summary', array($this, 'render_product_tabs_and_panels'), 2);
    }
    
    /**
     * Open product wrapper
     */
    public function open_product_wrapper() {
        echo '<section id="ptp-revolve-v4" class="alignfull" aria-label="PTP Product"><div class="pr-wrap"><div class="pr-media">';
    }
    
    /**
     * Close media column and open info column
     */
    public function close_media_open_info() {
        echo '</div><div class="pr-info">';
    }
    
    /**
     * Render product tabs and panels
     */
    public function render_product_tabs_and_panels() {
        $product = ptp_current_product();
        if (!$product) {
            echo '</div></div></section>';
            return;
        }

        // Get product attributes
        $date = trim($product->get_attribute('pa_date_range'));
        $time = trim($product->get_attribute('pa_time_range'));
        $ages = trim($product->get_attribute('pa_ages'));
        $venue = trim($product->get_attribute('pa_venue_name'));
        $address = trim($product->get_attribute('pa_venue_address'));

        // Description content
        $desc_html = $product->get_short_description();
        if (!$desc_html) {
            $desc_html = '<p>High‑rep, mentor‑led training with current NCAA players. First touch, 1v1 courage, finishing, and smart decisions — in a safe, organized environment.</p>';
        }

        // Who's attending (CSV meta)
        $att_raw = get_post_meta($product->get_id(), 'ptp_attending', true);
        $att = array_filter(array_map('trim', explode(',', (string)$att_raw)));
        if (empty($att)) {
            $att = array('NCAA mentors announced soon');
        }

        // Map setup
        $map_q = $address ?: $venue;
        $map_src = $map_q ? 'https://www.google.com/maps?q=' . rawurlencode($map_q) . '&output=embed' : '';

        // Header chips bar
        echo '<div class="pr-header" role="region" aria-label="Event facts">';
        if ($venue) echo '<span class="chip">📍 ' . esc_html($venue) . '</span>';
        if ($date) echo '<span class="chip">📅 ' . esc_html($date) . '</span>';
        if ($time) echo '<span class="chip">⏰ ' . esc_html($time) . '</span>';
        if ($ages) echo '<span class="chip">👥 Ages ' . esc_html($ages) . '</span>';
        echo '</div>';

        // Tabs
        echo '<div class="pr-tabs" role="tablist" aria-label="Event details tabs">';
        echo '<button class="tab is-active" role="tab" aria-selected="true" aria-controls="tab-desc">Description</button>';
        echo '<button class="tab" role="tab" aria-selected="false" aria-controls="tab-loc">Location</button>';
        echo '<button class="tab" role="tab" aria-selected="false" aria-controls="tab-att">Who\'s Attending</button>';
        echo '<button class="tab" role="tab" aria-selected="false" aria-controls="tab-rev">Reviews</button>';
        echo '</div>';

        // Panels
        echo '<div class="pr-panels">';
        
        // Description panel
        echo '<section id="tab-desc" class="panel is-active" role="tabpanel">' . wp_kses_post(wpautop($desc_html)) . '</section>';
        
        // Location panel
        echo '<section id="tab-loc" class="panel" role="tabpanel">';
        if ($venue || $address) {
            echo '<p class="loc"><b>' . esc_html($venue) . '</b><br>' . esc_html($address ?: '') . '</p>';
        }
        if ($map_src) {
            $map_link = 'https://www.google.com/maps?q=' . rawurlencode($map_q);
            echo '<div class="ptp-map-embed">';
            echo '<button type="button" class="map-loader" data-src="' . esc_url($map_src) . '">Tap to load map</button>';
            echo '<iframe title="Map to ' . esc_attr($venue ?: 'PTP Event') . '" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>';
            echo '</div>';
            echo '<p class="center sm"><a class="btn-outline" target="_blank" rel="noopener" href="' . esc_url($map_link) . '">Open in Google Maps</a></p>';
        }
        echo '</section>';
        
        // Who's attending panel
        echo '<section id="tab-att" class="panel" role="tabpanel"><ul class="att">';
        foreach ($att as $a) {
            echo '<li>' . esc_html($a) . '</li>';
        }
        echo '</ul></section>';
        
        // Reviews panel
        echo '<section id="tab-rev" class="panel" role="tabpanel"><div id="ptp-ti" data-ti></div></section>';
        echo '</div>';

        echo '</div></div></section>';
    }
    
    /**
     * Setup related products
     */
    private function setup_related_products() {
        add_filter('woocommerce_output_related_products_args', function($args) {
            $args['posts_per_page'] = 4; // show 4
            $args['columns'] = 4;       // 4 columns
            return $args;
        }, 20);
    }
    
    /**
     * Add custom styles
     */
    private function add_styles() {
        add_action('wp_head', array($this, 'output_styles'));
    }
    
    /**
     * Output custom styles
     */
    public function output_styles() {
        ?>
        <style id="ptp-revolve-v4-css">
        #ptp-revolve-v4{--y:#FCB900;--ink:#0e0f11;--muted:#6b7280;--b:#e5e7eb;--r:14px;--shadow:0 8px 28px rgba(0,0,0,.08);background:#fff;color:var(--ink)}
        #ptp-revolve-v4.alignfull{width:100vw;margin-left:calc(50% - 50vw);margin-right:calc(50% - 50vw)}
        #ptp-revolve-v4 *{box-sizing:border-box}
        #ptp-revolve-v4 img{max-width:100%;display:block}

        /* =====================
           Canvas + Grid (PTP)
           ===================== */
        #ptp-revolve-v4 .pr-wrap{max-width:1440px;margin:0 auto;padding:clamp(18px,4vw,40px);display:grid;gap:clamp(28px,3vw,44px)}
        /* Make RIGHT column dominant (Revolve feel) */
        @media (min-width:1100px){#ptp-revolve-v4 .pr-wrap{grid-template-columns:minmax(420px,580px) minmax(780px,980px);align-items:start}}

        /* =====================
           Left: Portrait gallery
           ===================== */
        #ptp-revolve-v4 .pr-media{display:grid;grid-template-columns:84px 1fr;gap:14px;align-items:start}
        #ptp-revolve-v4 .pr-media .woocommerce-product-gallery{grid-column:2;border:1px solid var(--b);border-radius:var(--r);overflow:hidden;box-shadow:var(--shadow);background:#fff}
        /* Tall main image */
        #ptp-revolve-v4 .woocommerce-product-gallery__wrapper{aspect-ratio:4/5;min-height:620px}
        #ptp-revolve-v4 .flex-viewport{height:100%}
        #ptp-revolve-v4 .woocommerce-product-gallery__image,
        #ptp-revolve-v4 .woocommerce-product-gallery__image a,
        #ptp-revolve-v4 .woocommerce-product-gallery__image img{width:100%;height:100%;object-fit:contain;background:#fff}
        /* Vertical thumbs */
        #ptp-revolve-v4 .flex-control-nav{grid-column:1;display:flex;flex-direction:column;gap:10px;position:sticky;top:84px}
        #ptp-revolve-v4 .flex-control-nav li{margin:0}
        #ptp-revolve-v4 .flex-control-nav img{width:100%;aspect-ratio:4/5;object-fit:cover;border-radius:12px;border:1px solid var(--b);box-shadow:var(--shadow)}

        /* Mobile: stack; horizontal thumbs */
        @media (max-width:1099px){
          #ptp-revolve-v4 .pr-wrap{grid-template-columns:1fr}
          #ptp-revolve-v4 .pr-media{grid-template-columns:1fr}
          #ptp-revolve-v4 .flex-control-nav{position:static;flex-direction:row;justify-content:center}
        }

        /* =====================
           Right: Wide, centered buy box
           ===================== */
        #ptp-revolve-v4 .pr-info{max-width:980px;margin:0 auto;text-align:center;display:flex;flex-direction:column;gap:12px}
        #ptp-revolve-v4 .product_title{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;font-weight:900;line-height:1.08;margin:0 0 8px;font-size:clamp(1.6rem,2.8vw,2.35rem)}
        /* Hide stray price above title; real price sits below title */
        #ptp-revolve-v4 .entry-summary > .price:first-child{display:none!important}
        #ptp-revolve-v4 .price{font-size:clamp(1.2rem,2.1vw,1.55rem);font-weight:900;margin:.1rem 0 .4rem}
        #ptp-revolve-v4 .woocommerce-product-rating{display:flex;gap:8px;justify-content:center;margin:0 0 .35rem}
        #ptp-revolve-v4 form.cart{display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:center;margin:12px 0 18px}
        #ptp-revolve-v4 .quantity input.qty{min-width:104px}
        /* PTP button styling */
        #ptp-revolve-v4 .single_add_to_cart_button{background:var(--y)!important;color:#0e0f11!important;border:none!important;border-radius:12px!important;font-weight:900!important;padding:14px 28px!important;letter-spacing:.01em;box-shadow:0 10px 22px rgba(252,185,0,.28)}
        #ptp-revolve-v4 .single_add_to_cart_button:hover{filter:brightness(.95)}
        /* Payment request buttons full-width */
        #ptp-revolve-v4 .pr-info .wc-stripe-payment-request-button,
        #ptp-revolve-v4 .pr-info .payment-request-button{width:100%}

        /* =====================
           Header chips (PTP)
           ===================== */
        #ptp-revolve-v4 .pr-header{margin:6px 0 0;padding:10px;display:flex;flex-wrap:wrap;gap:10px;align-items:center;justify-content:center;border:1px solid var(--b);border-radius:14px;background:#fff}
        #ptp-revolve-v4 .chip{background:rgba(252,185,0,.14);border:1px solid #f3d37a;padding:8px 12px;border-radius:999px;font-weight:800;color:#0e0f11;white-space:nowrap}

        /* =====================
           Tabs (PTP minimal)
           ===================== */
        #ptp-revolve-v4 .pr-tabs{display:flex;gap:10px;justify-content:center;margin:16px 0 0}
        #ptp-revolve-v4 .tab{appearance:none;background:#fff;border:1px solid var(--b);border-radius:9999px;padding:10px 16px;font-weight:900;cursor:pointer}
        #ptp-revolve-v4 .tab.is-active{background:var(--y);color:#0e0f11;border-color:var(--y)}
        #ptp-revolve-v4 .pr-panels{max-width:980px;margin:14px auto 6px;padding:0 8px}
        #ptp-revolve-v4 .panel{display:none}
        #ptp-revolve-v4 .panel.is-active{display:block}
        #ptp-revolve-v4 .panel p{margin:0 0 .9em}

        /* Location panel */
        #ptp-revolve-v4 .loc{text-align:center;color:var(--muted)}
        #ptp-revolve-v4 .ptp-map-embed{position:relative;width:100%;padding-top:56.25%;border-radius:14px;overflow:hidden;background:#f6f7f8;margin-top:10px}
        #ptp-revolve-v4 .ptp-map-embed iframe{position:absolute;inset:0;width:100%;height:100%;border:0;display:none}
        #ptp-revolve-v4 .ptp-map-embed .map-loader{position:absolute;inset:0;width:100%;height:100%;background:#fff;border:0;cursor:pointer;font-weight:800}
        #ptp-revolve-v4 .btn-outline{display:inline-block;border:1px solid var(--b);border-radius:12px;padding:10px 14px;text-decoration:none;font-weight:800}
        #ptp-revolve-v4 .center{text-align:center}
        #ptp-revolve-v4 .sm{font-size:.95rem}

        /* Who's Attending */
        #ptp-revolve-v4 .att{display:flex;flex-wrap:wrap;gap:10px;justify-content:center;padding:0;list-style:none}
        #ptp-revolve-v4 .att li{border:1px dashed var(--b);border-radius:999px;padding:8px 12px;font-weight:800}

        /* =====================
           Related products — Revolve-like
           ===================== */
        .related.products{max-width:1440px;margin:16px auto 44px;padding:0 24px}
        .related.products h2{font-size:1.25rem;margin:0 0 12px}
        .related.products ul.products{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
        .related.products ul.products li.product{border:1px solid var(--b);border-radius:14px;overflow:hidden;background:#fff;box-shadow:var(--shadow);padding:0}
        .related.products ul.products li.product a.woocommerce-LoopProduct-link{display:block;padding:0 0 12px;text-align:center}
        .related.products ul.products li.product img{width:100%;aspect-ratio:4/5;object-fit:cover;display:block}
        .related.products ul.products li.product .price{font-weight:800}
        .related.products ul.products li.product .button{margin:0 12px 14px}
        @media (max-width:1099px){.related.products ul.products{grid-template-columns:repeat(2,1fr)}}

        /* =====================
           Mobile polish
           ===================== */
        @media (max-width:1099px){
          #ptp-revolve-v4 .product_title,#ptp-revolve-v4 .price,#ptp-revolve-v4 .woocommerce-product-rating,#ptp-revolve-v4 form.cart{justify-content:center;text-align:center}
          #ptp-revolve-v4 .single_add_to_cart_button{width:100%}
        }
        </style>
        <?php
    }
    
    /**
     * Add custom scripts
     */
    private function add_scripts() {
        add_action('wp_footer', array($this, 'output_scripts'));
    }
    
    /**
     * Output custom scripts
     */
    public function output_scripts() {
        ?>
        <script>
        (function(){
          var root=document.getElementById('ptp-revolve-v4'); 
          if(!root) return;
          
          // Tabs functionality
          var tabs=root.querySelectorAll('.pr-tabs .tab');
          var panels=root.querySelectorAll('.pr-panels .panel');
          
          function openTab(id){
            tabs.forEach(function(x){
              x.classList.remove('is-active');
              x.setAttribute('aria-selected','false');
            });
            panels.forEach(function(p){
              p.classList.remove('is-active');
            });
            
            var t = Array.prototype.find.call(tabs, function(el){
              return el.getAttribute('aria-controls')===id;
            });
            var pane=root.querySelector('#'+id);
            
            if(t){ 
              t.classList.add('is-active'); 
              t.setAttribute('aria-selected','true'); 
            }
            if(pane){ 
              pane.classList.add('is-active'); 
            }
            
            if (window.matchMedia('(max-width:1099px)').matches){ 
              root.scrollIntoView({behavior:'smooth', block:'start'}); 
            }
          }
          
          tabs.forEach(function(t){ 
            t.addEventListener('click', function(){ 
              openTab(t.getAttribute('aria-controls')); 
            }); 
          });

          // Map lazy-load functionality
          var mapLoaded=false; 
          
          function loadMap(){ 
            if(mapLoaded) return; 
            var wrap=root.querySelector('#tab-loc .ptp-map-embed'); 
            if(!wrap) return; 
            var btn=wrap.querySelector('.map-loader'); 
            var iframe=wrap.querySelector('iframe'); 
            
            function doLoad(){ 
              if(iframe.src) return; 
              iframe.src=btn.getAttribute('data-src'); 
              iframe.style.display='block'; 
              if(btn) btn.remove(); 
              mapLoaded=true; 
            }
            
            if(btn){ 
              btn.addEventListener('click', doLoad); 
            }
            if (root.querySelector('#tab-loc.panel.is-active')) {
              doLoad();
            }
          }
          
          var tLoc = Array.prototype.find.call(tabs, function(el){
            return el.getAttribute('aria-controls')==='tab-loc';
          }); 
          if(tLoc){ 
            tLoc.addEventListener('click', loadMap); 
          }

          // Trustindex lazy-load in Reviews
          var revPane=root.querySelector('#tab-rev'); 
          var tiLoaded=false;
          
          function loadTI(){ 
            if(tiLoaded) return; 
            var ph=revPane && revPane.querySelector('#ptp-ti[data-ti]'); 
            if(!ph) return; 
            
            // Load Trustindex shortcode content
            <?php 
            $trustindex_content = '';
            if (shortcode_exists('trustindex')) {
                $trustindex_content = do_shortcode('[trustindex no-registration=google]');
            }
            ?>
            ph.outerHTML = <?php echo json_encode($trustindex_content); ?>; 
            tiLoaded=true; 
          }
          
          var tRev = Array.prototype.find.call(tabs, function(el){
            return el.getAttribute('aria-controls')==='tab-rev';
          }); 
          if(tRev){ 
            tRev.addEventListener('click', loadTI); 
          }
        })();
        </script>
        <?php
    }
}

// Initialize the plugin
new PTP_Revolve_Style_PDP_v4();