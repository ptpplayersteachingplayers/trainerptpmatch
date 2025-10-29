<?php
/**
 * Plugin Name: PTP — Revolve-Style PDP v4
 * Description: Targeted, spacing-safe product detail page styling for WooCommerce events
 * Version: 4.0.0
 * Author: PTP
 * Requires at least: 5.0
 * Tested up to: 6.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 * 
 * Target product slug: winter-soccer-clinic-rye-ny-january-11-2026-100-pm
 * - Keeps your current vertical spacing (no new top/bottom margins).
 * - Hides duplicate price above title + SKU/Category meta row.
 * - Adds chips + tabs (Desc / Location / Who's Attending / Reviews).
 * - Portrait gallery w/ vertical thumbs on desktop; horizontal on mobile.
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check if WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    return;
}

/**
 * Main PTP Revolve Style PDP Class
 */
class PTP_Revolve_Style_PDP {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'init', array( $this, 'init' ) );
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Hook into WordPress
        add_action( 'wp', array( $this, 'setup_product_hooks' ), 20 );
        add_filter( 'woocommerce_product_tabs', array( $this, 'remove_additional_info_tab' ), 98 );
        add_filter( 'woocommerce_output_related_products_args', array( $this, 'modify_related_products' ), 20 );
        add_action( 'wp_head', array( $this, 'add_custom_css' ) );
        add_action( 'wp_footer', array( $this, 'add_custom_js' ) );
    }
    
    /**
     * Target: enable only on specific PDP(s)
     */
    private function is_target_pdp() {
        if ( ! is_product() ) {
            return false;
        }
        
        $obj = get_queried_object();
        if ( empty( $obj ) || empty( $obj->post_name ) ) {
            return false;
        }
        
        $allowed = array(
            'winter-soccer-clinic-rye-ny-january-11-2026-100-pm',
            // Add more slugs here if needed
        );
        
        return in_array( $obj->post_name, $allowed, true );
    }
    
    /**
     * Safe current product getter
     */
    private function get_current_product() {
        if ( ! is_product() ) {
            return null;
        }
        
        global $product;
        return ( $product instanceof WC_Product ) ? $product : null;
    }
    
    /**
     * Setup product page hooks
     */
    public function setup_product_hooks() {
        if ( ! $this->is_target_pdp() ) {
            return;
        }
        
        // Remove default Woo tabs—we'll render our own
        remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
        
        // Remove short description from summary (we show it in Description tab)
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
        
        // Remove meta (SKU/Cats/Tags) row entirely
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
        
        // Price should appear once under title
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
        add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 12 );
        
        // Keep rating close to price
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
        add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 13 );
        
        // Add wrapper elements
        add_action( 'woocommerce_before_single_product_summary', array( $this, 'add_wrapper_start' ), 1 );
        add_action( 'woocommerce_before_single_product_summary', array( $this, 'add_info_column_start' ), 99 );
        add_action( 'woocommerce_after_single_product_summary', array( $this, 'add_chips_and_tabs' ), 2 );
    }
    
    /**
     * Remove additional information tab if added by plugins
     */
    public function remove_additional_info_tab( $tabs ) {
        if ( ! $this->is_target_pdp() ) {
            return $tabs;
        }
        
        unset( $tabs['additional_information'] );
        return $tabs;
    }
    
    /**
     * Add wrapper start
     */
    public function add_wrapper_start() {
        echo '<section id="ptp-revolve-v4" class="alignfull" aria-label="PTP Product"><div class="pr-wrap"><div class="pr-media">';
    }
    
    /**
     * Close media col, open info col
     */
    public function add_info_column_start() {
        echo '</div><div class="pr-info">';
    }
    
    /**
     * Add chips and tabs after summary
     */
    public function add_chips_and_tabs() {
        $product = $this->get_current_product();
        if ( ! $product ) {
            echo '</div></div></section>';
            return;
        }
        
        // Pull attributes (adjust slugs if yours differ)
        $date    = trim( wp_strip_all_tags( $product->get_attribute( 'pa_date_range' ) ) );
        $time    = trim( wp_strip_all_tags( $product->get_attribute( 'pa_time_range' ) ) );
        $ages    = trim( wp_strip_all_tags( $product->get_attribute( 'pa_ages' ) ) );
        $venue   = trim( wp_strip_all_tags( $product->get_attribute( 'pa_venue_name' ) ) );
        $address = trim( wp_strip_all_tags( $product->get_attribute( 'pa_venue_address' ) ) );
        
        // Description tab uses short description; fallback provided
        $desc_html = $product->get_short_description();
        if ( ! $desc_html ) {
            $desc_html = '<p>High-rep, mentor-led training with current NCAA players. First touch, 1v1 courage, finishing, and smarter decisions — in a safe, organized environment.</p>';
        }
        
        // Who's Attending from CSV meta
        $att_raw = get_post_meta( $product->get_id(), 'ptp_attending', true );
        $att = array_filter( array_map( 'trim', explode( ',', (string) $att_raw ) ) );
        if ( empty( $att ) ) {
            $att = array( 'NCAA mentors announced soon' );
        }
        
        // Map
        $map_q   = $address ?: $venue;
        $map_src = $map_q ? 'https://www.google.com/maps?q=' . rawurlencode( $map_q ) . '&output=embed' : '';
        
        // Chips (no extra vertical spacing)
        echo '<div class="pr-header" role="region" aria-label="Event facts">';
        if ( $venue ) {
            echo '<span class="chip">📍 ' . esc_html( $venue ) . '</span>';
        }
        if ( $date ) {
            echo '<span class="chip">📅 ' . esc_html( $date ) . '</span>';
        }
        if ( $time ) {
            echo '<span class="chip">⏰ ' . esc_html( $time ) . '</span>';
        }
        if ( $ages ) {
            echo '<span class="chip">👥 Ages ' . esc_html( $ages ) . '</span>';
        }
        echo '</div>';
        
        // Tabs
        echo '<div class="pr-tabs" role="tablist" aria-label="Event details tabs">'
           . '<button class="tab is-active" role="tab" aria-selected="true" aria-controls="tab-desc">Description</button>'
           . '<button class="tab" role="tab" aria-selected="false" aria-controls="tab-loc">Location</button>'
           . '<button class="tab" role="tab" aria-selected="false" aria-controls="tab-att">Who\'s Attending</button>'
           . '<button class="tab" role="tab" aria-selected="false" aria-controls="tab-rev">Reviews</button>'
           . '</div>';
        
        // Description bar (updates with active tab)
        echo '<div class="pr-desc-bar" role="heading" aria-level="2"><span>Description</span></div>';
        
        // Panels
        echo '<div class="pr-panels">';
        echo '<section id="tab-desc" class="panel is-active" role="tabpanel">' . wp_kses_post( wpautop( $desc_html ) ) . '</section>';
        
        echo '<section id="tab-loc" class="panel" role="tabpanel">';
        if ( $venue || $address ) {
            echo '<p class="loc"><b>' . esc_html( $venue ) . '</b><br>' . esc_html( $address ?: '' ) . '</p>';
        }
        if ( $map_src ) {
            $map_link = 'https://www.google.com/maps?q=' . rawurlencode( $map_q );
            echo '<div class="ptp-map-embed">'
               . '<button type="button" class="map-loader" data-src="' . esc_url( $map_src ) . '">Click to load map</button>'
               . '<iframe title="Map to ' . esc_attr( $venue ?: 'PTP Event' ) . '" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>'
               . '</div>'
               . '<p class="center sm"><a class="btn-outline" target="_blank" rel="noopener" href="' . esc_url( $map_link ) . '">Open in Google Maps</a></p>';
        }
        echo '</section>';
        
        echo '<section id="tab-att" class="panel" role="tabpanel"><ul class="att">';
        foreach ( $att as $a ) {
            echo '<li>' . esc_html( $a ) . '</li>';
        }
        echo '</ul></section>';
        
        // Reviews placeholder (Trustindex lazy below)
        echo '<section id="tab-rev" class="panel" role="tabpanel"><div id="ptp-ti" data-ti></div></section>';
        
        echo '</div>'; // .pr-panels
        echo '</div></div></section>'; // close wrappers
    }
    
    /**
     * Related products: 4-up grid on this PDP only
     */
    public function modify_related_products( $args ) {
        if ( ! $this->is_target_pdp() ) {
            return $args;
        }
        
        $args['posts_per_page'] = 4;
        $args['columns']        = 4;
        return $args;
    }
    
    /**
     * Add custom CSS
     */
    public function add_custom_css() {
        if ( ! $this->is_target_pdp() ) {
            return;
        }
        ?>
        <style id="ptp-revolve-v4-css">
        /* Keep global spacing intact */
        #ptp-revolve-v4{margin-top:14px} /* requested top margin only */
        #ptp-revolve-v4 *{box-sizing:border-box}
        #ptp-revolve-v4 img{max-width:100%;display:block}

        /* --- Minimal gallery layout (no extra outer spacing) --- */
        #ptp-revolve-v4 .pr-wrap{display:grid;gap:clamp(16px,2vw,24px)}
        @media (min-width:1100px){
          #ptp-revolve-v4 .pr-wrap{grid-template-columns:minmax(380px,560px) minmax(640px,1fr);align-items:start}
        }
        #ptp-revolve-v4 .pr-media{display:grid;grid-template-columns:84px 1fr;gap:12px;align-items:start}
        #ptp-revolve-v4 .woocommerce-product-gallery__wrapper{aspect-ratio:4/5;min-height:600px}
        #ptp-revolve-v4 .flex-viewport{height:100%}
        #ptp-revolve-v4 .woocommerce-product-gallery__image,
        #ptp-revolve-v4 .woocommerce-product-gallery__image a,
        #ptp-revolve-v4 .woocommerce-product-gallery__image img{width:100%;height:100%;object-fit:contain;background:#fff;border-radius:12px} /* rounded corners requested */
        #ptp-revolve-v4 .flex-control-nav{grid-column:1;display:flex;flex-direction:column;gap:10px;position:sticky;top:84px}
        #ptp-revolve-v4 .flex-control-nav li{margin:0}
        #ptp-revolve-v4 .flex-control-nav img{width:100%;aspect-ratio:4/5;object-fit:cover;border-radius:12px;border:1px solid #e5e7eb}

        /* Mobile: stack; horizontal thumbs */
        @media (max-width:1099px){
          #ptp-revolve-v4 .pr-wrap{grid-template-columns:1fr}
          #ptp-revolve-v4 .pr-media{grid-template-columns:1fr}
          #ptp-revolve-v4 .flex-control-nav{position:static;flex-direction:row;justify-content:center}
        }

        /* --- Info column (centered) without changing section spacing --- */
        #ptp-revolve-v4 .pr-info{text-align:center;display:flex;flex-direction:column;gap:12px}
        #ptp-revolve-v4 .entry-summary > .price:first-child{display:none!important} /* hide duplicate top price */
        .single-product .product_meta{display:none!important} /* hide SKU / Category row */
        #ptp-revolve-v4 .single_add_to_cart_button{border-radius:12px;font-weight:900}

        /* --- Chips + Tabs: zero extra margins/padding --- */
        #ptp-revolve-v4 .pr-header{margin:0!important;padding:.5rem 0!important;border:0!important;background:transparent!important;display:flex;flex-wrap:wrap;gap:10px;justify-content:center}
        #ptp-revolve-v4 .chip{background:rgba(252,185,0,.14);border:1px solid #f3d37a;padding:8px 12px;border-radius:999px;font-weight:800;color:#0e0f11;white-space:nowrap}
        #ptp-revolve-v4 .pr-tabs{margin:0!important;display:flex;gap:10px;justify-content:center}
        #ptp-revolve-v4 .tab{appearance:none;background:#fff;border:1px solid #e5e7eb;border-radius:9999px;padding:10px 16px;font-weight:900;cursor:pointer}
        #ptp-revolve-v4 .tab.is-active{background:#FCB900;color:#0e0f11;border-color:#FCB900}

        /* Description bar (no extra margins) */
        #ptp-revolve-v4 .pr-desc-bar{display:flex;justify-content:center;align-items:center;height:38px;border:1px solid #e5e7eb;border-radius:999px;background:#fff}
        #ptp-revolve-v4 .pr-desc-bar span{font-weight:900;color:#0e0f11;}

        /* Panels */
        #ptp-revolve-v4 .pr-panels{margin:0!important;padding:0!important}
        #ptp-revolve-v4 .panel{display:none}
        #ptp-revolve-v4 .panel.is-active{display:block}
        #ptp-revolve-v4 .panel p:first-child{margin-top:0!important}

        /* --- Location map embed --- */
        #ptp-revolve-v4 .loc{text-align:center;color:#6b7280}
        #ptp-revolve-v4 .ptp-map-embed{position:relative;width:100%;padding-top:56.25%;border-radius:14px;overflow:hidden;background:#f6f7f8;margin-top:10px}
        #ptp-revolve-v4 .ptp-map-embed iframe{position:absolute;inset:0;width:100%;height:100%;border:0;display:none}
        #ptp-revolve-v4 .ptp-map-embed .map-loader{position:absolute;inset:0;width:100%;height:100%;background:#fff;border:0;cursor:pointer;font-weight:800}
        #ptp-revolve-v4 .btn-outline{display:inline-block;border:1px solid #e5e7eb;border-radius:12px;padding:10px 14px;text-decoration:none;font-weight:800}
        #ptp-revolve-v4 .center{text-align:center}
        #ptp-revolve-v4 .sm{font-size:.95rem}

        /* --- Who's Attending list styles --- */
        #ptp-revolve-v4 .att{list-style:none;padding:0;margin:0}
        #ptp-revolve-v4 .att li{padding:8px 0;border-bottom:1px solid #e5e7eb}
        #ptp-revolve-v4 .att li:last-child{border-bottom:none}
        </style>
        <?php
    }
    
    /**
     * Add custom JavaScript
     */
    public function add_custom_js() {
        if ( ! $this->is_target_pdp() ) {
            return;
        }
        
        $trustindex_html = shortcode_exists( 'trustindex' ) ? do_shortcode( '[trustindex no-registration=google]' ) : '';
        ?>
        <script>
        (function(){
          var root=document.getElementById('ptp-revolve-v4'); 
          if(!root) return;

          // Tabs functionality
          var tabs=root.querySelectorAll('.pr-tabs .tab');
          var panels=root.querySelectorAll('.pr-panels .panel');
          var bar=root.querySelector('.pr-desc-bar span');
          
          function openTab(id){
            tabs.forEach(function(x){
              x.classList.remove('is-active');
              x.setAttribute('aria-selected','false');
            });
            panels.forEach(function(p){
              p.classList.remove('is-active');
            });
            
            var activeTab = [].find.call(tabs, function(el){
              return el.getAttribute('aria-controls') === id;
            });
            var activePane = root.querySelector('#' + id);
            
            if(activeTab){ 
              activeTab.classList.add('is-active'); 
              activeTab.setAttribute('aria-selected','true'); 
              if(bar){ 
                bar.textContent = activeTab.textContent; 
              } 
            }
            if(activePane){ 
              activePane.classList.add('is-active'); 
            }
          }
          
          tabs.forEach(function(t){ 
            t.addEventListener('click', function(){ 
              openTab(t.getAttribute('aria-controls')); 
            }); 
          });

          // Map lazy-load functionality
          function initMapLazy(){
            var wrap = root.querySelector('#tab-loc .ptp-map-embed'); 
            if(!wrap) return;
            
            var btn = wrap.querySelector('.map-loader'); 
            var iframe = wrap.querySelector('iframe'); 
            var loaded = false;
            
            function doLoad(){ 
              if(loaded || !btn || !iframe) return; 
              var src = btn.getAttribute('data-src'); 
              if(!src) return;
              
              iframe.src = src; 
              iframe.style.display = 'block'; 
              btn.remove(); 
              loaded = true;
            }
            
            if(btn) btn.addEventListener('click', doLoad);
            if (root.querySelector('#tab-loc.panel.is-active')) doLoad();
          }
          
          var locationTab = [].find.call(tabs, function(el){
            return el.getAttribute('aria-controls') === 'tab-loc';
          }); 
          if(locationTab){ 
            locationTab.addEventListener('click', initMapLazy); 
          }

          // Trustindex lazy-load functionality
          var reviewsTab = [].find.call(tabs, function(el){
            return el.getAttribute('aria-controls') === 'tab-rev';
          });
          if(reviewsTab){
            var loadedTI = false;
            reviewsTab.addEventListener('click', function(){
              if(loadedTI) return;
              var placeholder = root.querySelector('#ptp-ti[data-ti]');
              if(placeholder){
                placeholder.outerHTML = <?php echo wp_json_encode( $trustindex_html ); ?>;
                loadedTI = true;
              }
            });
          }
        })();
        </script>
        <?php
    }
}

// Initialize the plugin
new PTP_Revolve_Style_PDP();

/* --------------------------
   Make it site-wide later:
   Replace is_target_pdp() method to return is_product()
   -------------------------- */