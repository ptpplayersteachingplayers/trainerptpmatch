<?php
/**
 * Single Product (PTP all-in-one template) -- FIXED for valid HTML output, schema, and Woo hooks.
 * Place at: your-child-theme/woocommerce/single-product.php
 * Includes layout, CSS, custom sections, snapshot, related, sticky CTA, and JSON-LD schema.
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

if ( ! function_exists( 'wc_get_product' ) || ! have_posts() ) {
	echo '<p>' . esc_html__( 'No product found or WooCommerce is missing.', 'ptp' ) . '</p>';
	get_footer( 'shop' );
	return;
}

while ( have_posts() ) : the_post();
	$product = wc_get_product( get_the_ID() );
	if ( ! $product ) { continue; }

	// Helpers
	$f = array(
		'venue'       => get_post_meta( $product->get_id(), '_ptp_venue', true ),
		'address'     => get_post_meta( $product->get_id(), '_ptp_address', true ),
		'when'        => get_post_meta( $product->get_id(), '_ptp_when', true ),
		'age_band'    => get_post_meta( $product->get_id(), '_ptp_age_band', true ),
		'capacity'    => get_post_meta( $product->get_id(), '_ptp_capacity', true ),
		'seats_left'  => get_post_meta( $product->get_id(), '_ptp_seats_left', true ),
		'coaches'     => get_post_meta( $product->get_id(), '_ptp_coaches', true ),
		'policies_ref'=> get_post_meta( $product->get_id(), '_ptp_policies_ref', true ),
	);
	$when_lines = array_filter( array_map( 'trim', preg_split( "/\r\n|\r|\n/", (string) $f['when'] ) ) );

	// Inline CSS (tokens + layout)
	?>
	<style>
	:root{--ptp-yellow:#FCB900;--ptp-ink:#0e0f11;--ptp-muted:#6b7280;--ptp-border:#e5e7eb;--ptp-soft:#f8fafc;--ptp-radius:14px}
	.ptp-pdp{display:grid;grid-template-columns:1fr;gap:24px;max-width:1400px;margin:0 auto;padding:20px}
	@media(min-width:992px){.ptp-pdp{grid-template-columns:minmax(0,1fr) 480px;align-items:start}.ptp-buybox{position:sticky;top:24px}}
	.ptp-gallery{display:flex;gap:12px;flex-direction:column}
	@media(min-width:768px){.ptp-gallery{flex-direction:row}}
	.ptp-gallery-main{flex:1;aspect-ratio:3/4;background:#fff;border:1px solid var(--ptp-border);border-radius:var(--ptp-radius);overflow:hidden;position:relative}
	.ptp-gallery-main img{width:100%;height:100%;object-fit:cover;display:block}
	.ptp-thumbs{width:100%;display:flex;gap:8px;overflow-x:auto}
	@media(min-width:768px){.ptp-thumbs{width:96px;flex-direction:column;overflow-x:visible}}
	.ptp-thumb{border:1px solid var(--ptp-border);border-radius:10px;overflow:hidden;aspect-ratio:1/1;cursor:pointer;flex-shrink:0;width:80px;transition:border-color .2s}
	@media(min-width:768px){.ptp-thumb{width:auto}}
	.ptp-thumb:hover,.ptp-thumb.active{border-color:var(--ptp-yellow);border-width:2px}
	.ptp-thumb:focus{outline:2px solid var(--ptp-yellow);outline-offset:2px}
	.ptp-thumb img{width:100%;height:100%;object-fit:cover;display:block}
	.ptp-buybox{background:#fff;border:1px solid var(--ptp-border);border-radius:var(--ptp-radius);box-shadow:0 1px 2px rgba(0,0,0,.04);padding:20px;text-align:left}
	.ptp-buybox h1{margin:0 0 8px;font-size:28px;line-height:1.2;color:var(--ptp-ink)}
	.ptp-trust{display:flex;gap:10px;color:var(--ptp-muted);font-size:13px;margin:8px 0 12px;flex-wrap:wrap}
	.ptp-short{color:var(--ptp-muted);margin:0 0 12px;line-height:1.5}
	.ptp-price{font-weight:700;font-size:24px;margin:0 0 12px}
	.ptp-included{margin:12px 0;padding:12px;background:var(--ptp-soft);border-radius:12px;list-style:none}
	.ptp-included li{margin:6px 0}
	.ptp-meta{margin-top:12px;color:var(--ptp-muted);font-size:13px}
	.ptp-pay{display:flex;gap:8px;align-items:center;color:var(--ptp-muted);margin-top:10px;flex-wrap:wrap}
	.ptp-pay .pill{border:1px solid var(--ptp-border);padding:6px 10px;border-radius:999px;font-size:12px}
	.ptp-tabs{border-top:1px solid var(--ptp-border);margin-top:24px;padding-top:16px;max-width:1400px;margin-left:auto;margin-right:auto}
	.ptp-tabs .ptp-tab-headers{display:flex;gap:8px;flex-wrap:wrap;margin:12px 0}
	.ptp-tabs .ptp-tab-headers button{border:1px solid var(--ptp-border);background:#fff;border-radius:10px;padding:8px 12px;cursor:pointer;transition:all .2s;font-weight:500}
	.ptp-tabs .ptp-tab-headers button:hover{background:var(--ptp-soft)}
	.ptp-tabs .ptp-tab-headers button[aria-selected="true"]{background:var(--ptp-ink);color:#fff;border-color:var(--ptp-ink)}
	.ptp-tabs .panel{border:1px solid var(--ptp-border);border-radius:var(--ptp-radius);padding:16px;background:#fff;margin-top:12px}
	.ptp-tabs .panel[hidden]{display:none}
	.ptp-snapshot{border:1px solid var(--ptp-border);border-radius:12px;padding:12px;margin:12px 0;background:#fff}
	.ptp-snapshot>div{margin:8px 0}
	.ptp-snapshot strong{color:var(--ptp-ink);font-weight:600}
	.ptp-sticky-cta{position:fixed;left:0;right:0;bottom:0;display:flex;justify-content:space-between;align-items:center;gap:12px;background:#fff;border-top:1px solid var(--ptp-border);padding:10px 14px;box-shadow:0 -4px 12px rgba(0,0,0,.06);z-index:40;transform:translateY(100%);transition:transform .25s ease}
	.ptp-sticky-cta.visible{transform:translateY(0)}
	.ptp-sticky-cta .price{font-weight:700;font-size:18px}
	.ptp-sticky-cta .btn{background:var(--ptp-ink);color:#fff;border-radius:12px;padding:12px 16px;font-weight:700;border:none;cursor:pointer}
	.ptp-buybox button:focus,.ptp-tabs button:focus,.ptp-sticky-cta button:focus{outline:2px solid var(--ptp-yellow);outline-offset:2px}
	.ptp-map{height:240px;background:var(--ptp-soft);border:1px dashed var(--ptp-border);border-radius:12px}
	.ptp-related{max-width:1400px;margin:40px auto;padding:0 20px}
	.ptp-related h2{margin-bottom:20px;color:var(--ptp-ink)}
	</style>
	<?php

	// WooCommerce Notices, sale flashes, etc
	do_action( 'woocommerce_before_single_product' );
	?>

	<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'ptp-pdp', get_the_ID() ); ?>>

		<div class="ptp-gallery" aria-label="<?php esc_attr_e( 'Product gallery', 'ptp' ); ?>">
			<?php
			/**
			 * Custom gallery implementation
			 * This replaces woocommerce_show_product_images()
			 */
			$product_id = $product->get_id();
			$attachment_ids = $product->get_gallery_image_ids();
			$main_image_id = $product->get_image_id();
			
			// Combine main image with gallery
			$all_images = array();
			if ( $main_image_id ) {
				$all_images[] = $main_image_id;
			}
			$all_images = array_merge( $all_images, $attachment_ids );
			
			if ( ! empty( $all_images ) ) :
				$first_image = $all_images[0];
				?>
				<div class="ptp-gallery-wrapper">
					<?php if ( count( $all_images ) > 1 ) : ?>
					<div class="ptp-thumbs" role="tablist" aria-label="<?php esc_attr_e( 'Product image thumbnails', 'ptp' ); ?>">
						<?php foreach ( $all_images as $index => $image_id ) : ?>
						<button type="button" 
							class="ptp-thumb<?php echo $index === 0 ? ' active' : ''; ?>" 
							role="tab"
							aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>"
							aria-controls="main-image"
							data-image-id="<?php echo esc_attr( $image_id ); ?>"
							tabindex="<?php echo $index === 0 ? '0' : '-1'; ?>">
							<?php echo wp_get_attachment_image( $image_id, 'woocommerce_gallery_thumbnail', false, array( 'alt' => esc_attr( get_the_title( $product_id ) ) ) ); ?>
						</button>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>
					
					<div class="ptp-gallery-main" id="main-image" role="img" aria-label="<?php esc_attr_e( 'Main product image', 'ptp' ); ?>">
						<?php echo wp_get_attachment_image( $first_image, 'woocommerce_single', false, array( 'alt' => esc_attr( get_the_title( $product_id ) ), 'data-main-image' => '' ) ); ?>
						<?php if ( $product->is_on_sale() ) : ?>
							<?php echo apply_filters( 'woocommerce_sale_flash', '<span class="onsale">' . esc_html__( 'Sale!', 'woocommerce' ) . '</span>', $product ); ?>
						<?php endif; ?>
					</div>
				</div>
			<?php else : ?>
				<div class="ptp-gallery-main">
					<?php echo wc_placeholder_img(); ?>
				</div>
			<?php endif; ?>
		</div>

		<aside class="ptp-buybox" aria-label="<?php esc_attr_e( 'Purchase information', 'ptp' ); ?>">
			<?php
			// Title
			echo '<h1 class="product_title entry-title">' . esc_html( get_the_title() ) . '</h1>';

			// Trust row
			echo '<div class="ptp-trust" aria-label="' . esc_attr__( 'Trust badges', 'ptp' ) . '"><span>' . esc_html__( 'Secure Checkout', 'ptp' ) . '</span><span>' . esc_html__( 'Refund Policy', 'ptp' ) . '</span><span>' . esc_html__( 'Verified Coaches', 'ptp' ) . '</span></div>';

			// Short description (trimmed)
			$short = $product->get_short_description();
			if ( $short ) {
				$short_trimmed = wp_trim_words( wp_strip_all_tags( $short ), 28, '…' );
				echo '<div class="ptp-short">' . wp_kses_post( $short_trimmed ) . '</div>';
			}

			// Price
			woocommerce_template_single_price();

			// Event Snapshot (above ATC)
			if ( array_filter( $f ) ) {
				echo '<div class="ptp-snapshot">';
				if ( $f['venue'] || $f['address'] ) {
					$venue_text = trim( $f['venue'] . ( $f['address'] ? ', ' . $f['address'] : '' ) );
					echo '<div><strong>' . esc_html__( 'Venue:', 'ptp' ) . '</strong> ' . esc_html( $venue_text ) . '</div>';
				}
				if ( ! empty( $when_lines ) ) {
					echo '<div><strong>' . esc_html__( 'When:', 'ptp' ) . '</strong><br>' . implode( '<br>', array_map( 'esc_html', $when_lines ) ) . '</div>';
				}
				if ( $f['age_band'] ) {
					echo '<div><strong>' . esc_html__( 'Ages:', 'ptp' ) . '</strong> ' . esc_html( $f['age_band'] ) . '</div>';
				}
				if ( $f['seats_left'] !== '' && is_numeric( $f['seats_left'] ) && (int) $f['seats_left'] <= 15 ) {
					echo '<div style="color:#b91c1c;font-weight:700">' . sprintf( esc_html__( 'Only %d spots left', 'ptp' ), intval( $f['seats_left'] ) ) . '</div>';
				}
				echo '</div>';
			}

			// Add to Cart
			woocommerce_template_single_add_to_cart();

			// What's included
			$included_items = array(
				__( 'Small group training', 'ptp' ),
				__( 'Skills & drills', 'ptp' ),
				__( 'PTP training plan', 'ptp' )
			);
			echo '<ul class="ptp-included" aria-label="' . esc_attr__( 'What\'s included', 'ptp' ) . '">';
			foreach ( $included_items as $item ) {
				echo '<li>✔ ' . esc_html( $item ) . '</li>';
			}
			echo '</ul>';

			// Meta (SKU/Cats)
			echo '<div class="ptp-meta">';
			if ( $product->get_sku() ) {
				echo '<div>' . esc_html__( 'SKU:', 'ptp' ) . ' ' . esc_html( $product->get_sku() ) . '</div>';
			}
			$cats = wc_get_product_category_list( $product->get_id(), ', ' );
			if ( $cats ) {
				echo '<div>' . esc_html__( 'Categories:', 'ptp' ) . ' ' . wp_kses_post( $cats ) . '</div>';
			}
			echo '</div>';

			// Payment icons (compact)
			$payment_methods = array( 'Apple Pay', 'Google Pay', 'Visa', 'Mastercard' );
			echo '<div class="ptp-pay" aria-label="' . esc_attr__( 'Payment methods', 'ptp' ) . '">';
			foreach ( $payment_methods as $method ) {
				echo '<span class="pill">' . esc_html( $method ) . '</span>';
			}
			echo '</div>';
			?>
		</aside>
	</div>

	<?php
	// Sections: Tabs
	?>
	<section class="woocommerce-tabs ptp-tabs" aria-label="<?php esc_attr_e( 'Product information', 'ptp' ); ?>">
		<div class="ptp-tab-headers" role="tablist">
			<button type="button" role="tab" aria-selected="true" aria-controls="tab-overview" id="tab-btn-overview" data-tab="overview"><?php esc_html_e( 'Overview', 'ptp' ); ?></button>
			<button type="button" role="tab" aria-selected="false" aria-controls="tab-loc" id="tab-btn-loc" data-tab="loc"><?php esc_html_e( 'Location & Schedule', 'ptp' ); ?></button>
			<button type="button" role="tab" aria-selected="false" aria-controls="tab-staff" id="tab-btn-staff" data-tab="staff"><?php esc_html_e( 'Who\'s Attending', 'ptp' ); ?></button>
			<button type="button" role="tab" aria-selected="false" aria-controls="tab-reviews" id="tab-btn-reviews" data-tab="reviews"><?php esc_html_e( 'Reviews', 'ptp' ); ?></button>
		</div>
		<div class="panel" id="tab-overview" role="tabpanel" aria-labelledby="tab-btn-overview">
			<?php the_content(); ?>
		</div>
		<div class="panel" id="tab-loc" role="tabpanel" aria-labelledby="tab-btn-loc" hidden>
			<?php if ( $f['venue'] ) : ?><p><strong><?php esc_html_e( 'Venue:', 'ptp' ); ?></strong> <?php echo esc_html( $f['venue'] ); ?></p><?php endif; ?>
			<?php if ( $f['address'] ) : ?><p><strong><?php esc_html_e( 'Address:', 'ptp' ); ?></strong> <?php echo esc_html( $f['address'] ); ?></p><?php endif; ?>
			<?php if ( ! empty( $when_lines ) ) : ?><p><strong><?php esc_html_e( 'Schedule:', 'ptp' ); ?></strong><br><?php echo implode( '<br>', array_map( 'esc_html', $when_lines ) ); ?></p><?php endif; ?>
			<?php if ( $f['age_band'] ) : ?><p><strong><?php esc_html_e( 'Ages:', 'ptp' ); ?></strong> <?php echo esc_html( $f['age_band'] ); ?></p><?php endif; ?>
			<div class="ptp-map" role="img" aria-label="<?php esc_attr_e( 'Map placeholder', 'ptp' ); ?>"></div>
			<?php if ( $f['policies_ref'] ) : ?><p><a href="<?php echo esc_url( $f['policies_ref'] ); ?>"><?php esc_html_e( 'Refund & Safety Policies', 'ptp' ); ?></a></p><?php endif; ?>
		</div>
		<div class="panel" id="tab-staff" role="tabpanel" aria-labelledby="tab-btn-staff" hidden>
			<?php
			$coaches = array_filter( array_map( 'trim', preg_split( "/\r\n|\r|\n/", (string) $f['coaches'] ) ) );
			if ( ! empty( $coaches ) ) { 
				echo '<ul>';
				foreach ( $coaches as $c ) {
					echo '<li>' . esc_html( $c ) . '</li>';
				}
				echo '</ul>';
			} else { 
				echo '<p>' . esc_html__( 'Staff list coming soon.', 'ptp' ) . '</p>';
			}
			?>
		</div>
		<div class="panel" id="tab-reviews" role="tabpanel" aria-labelledby="tab-btn-reviews" hidden>
			<?php comments_template(); ?>
		</div>
	</section>
	<?php

	// Related Products
	?>
	<section class="ptp-related">
		<h2><?php esc_html_e( 'More Clinics Near You', 'ptp' ); ?></h2>
		<?php
		$related_args = array(
			'posts_per_page' => 4,
			'columns'        => 4,
			'orderby'        => 'rand',
		);
		
		// Get product categories
		$terms = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'ids' ) );
		
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			$related_args['category'] = $terms;
			$related_args['visibility'] = 'visible';
			
			// Query related products
			$related_query = new WP_Query( array(
				'post_type'      => 'product',
				'posts_per_page' => 4,
				'post__not_in'   => array( $product->get_id() ),
				'tax_query'      => array(
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'term_id',
						'terms'    => $terms,
					),
				),
				'orderby' => 'rand',
			) );
			
			if ( $related_query->have_posts() ) {
				echo '<div class="products columns-4">';
				while ( $related_query->have_posts() ) {
					$related_query->the_post();
					wc_get_template_part( 'content', 'product' );
				}
				echo '</div>';
				wp_reset_postdata();
			}
		}
		?>
	</section>

	<?php
	// Sticky CTA (for mobile)
	$display_price = wc_get_price_to_display( $product );
	?>
	<div class="ptp-sticky-cta" id="ptpStickyCta" aria-hidden="true" role="region" aria-label="<?php esc_attr_e( 'Quick purchase', 'ptp' ); ?>">
		<div class="price"><?php echo wp_kses_post( wc_price( $display_price ) ); ?></div>
		<button class="btn" type="button" id="ptpStickyBtn" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>" data-price="<?php echo esc_attr( $display_price ); ?>"><?php esc_html_e( 'Register', 'ptp' ); ?></button>
	</div>

	<script>
	(function(){
		'use strict';
		
		// Gallery switching
		var thumbs = document.querySelectorAll('.ptp-thumb');
		var mainImg = document.querySelector('.ptp-gallery-main img[data-main-image]');
		
		if (thumbs.length > 0 && mainImg) {
			thumbs.forEach(function(thumb, index) {
				thumb.addEventListener('click', function() {
					// Update aria-selected
					thumbs.forEach(function(t) {
						t.setAttribute('aria-selected', 'false');
						t.classList.remove('active');
						t.setAttribute('tabindex', '-1');
					});
					thumb.setAttribute('aria-selected', 'true');
					thumb.classList.add('active');
					thumb.setAttribute('tabindex', '0');
					
					// Get image from thumb
					var img = thumb.querySelector('img');
					if (img && img.src) {
						var fullSrc = img.src.replace('-150x150', '').replace('-300x300', '');
						mainImg.src = fullSrc;
						mainImg.srcset = '';
					}
				});
				
				// Keyboard navigation
				thumb.addEventListener('keydown', function(e) {
					var currentIndex = Array.from(thumbs).indexOf(document.activeElement);
					var nextThumb = null;
					
					if (e.key === 'ArrowDown' || e.key === 'ArrowRight') {
						e.preventDefault();
						nextThumb = thumbs[currentIndex + 1] || thumbs[0];
					} else if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') {
						e.preventDefault();
						nextThumb = thumbs[currentIndex - 1] || thumbs[thumbs.length - 1];
					} else if (e.key === 'Home') {
						e.preventDefault();
						nextThumb = thumbs[0];
					} else if (e.key === 'End') {
						e.preventDefault();
						nextThumb = thumbs[thumbs.length - 1];
					}
					
					if (nextThumb) {
						nextThumb.focus();
						nextThumb.click();
					}
				});
			});
		}
		
		// Tabs
		var headers = document.querySelectorAll('.ptp-tab-headers button');
		var panels = {
			'overview': document.getElementById('tab-overview'),
			'loc': document.getElementById('tab-loc'),
			'staff': document.getElementById('tab-staff'),
			'reviews': document.getElementById('tab-reviews')
		};
		
		headers.forEach(function(btn) {
			btn.addEventListener('click', function() {
				headers.forEach(function(b) {
					b.setAttribute('aria-selected', 'false');
				});
				Object.keys(panels).forEach(function(k) {
					if (panels[k]) {
						panels[k].hidden = true;
					}
				});
				
				btn.setAttribute('aria-selected', 'true');
				var key = btn.getAttribute('data-tab');
				if (panels[key]) {
					panels[key].hidden = false;
				}
			});
			
			// Keyboard navigation for tabs
			btn.addEventListener('keydown', function(e) {
				var currentIndex = Array.from(headers).indexOf(document.activeElement);
				var nextBtn = null;
				
				if (e.key === 'ArrowRight') {
					e.preventDefault();
					nextBtn = headers[currentIndex + 1] || headers[0];
				} else if (e.key === 'ArrowLeft') {
					e.preventDefault();
					nextBtn = headers[currentIndex - 1] || headers[headers.length - 1];
				} else if (e.key === 'Home') {
					e.preventDefault();
					nextBtn = headers[0];
				} else if (e.key === 'End') {
					e.preventDefault();
					nextBtn = headers[headers.length - 1];
				}
				
				if (nextBtn) {
					nextBtn.focus();
					nextBtn.click();
				}
			});
		});

		// Sticky CTA
		var bar = document.getElementById('ptpStickyCta');
		var submit = document.querySelector('form.cart button[type="submit"]');
		
		if (submit && bar) {
			var io = new IntersectionObserver(function(entries) {
				entries.forEach(function(entry) {
					bar.classList.toggle('visible', !entry.isIntersecting);
					bar.setAttribute('aria-hidden', entry.isIntersecting ? 'true' : 'false');
				});
			}, {
				threshold: 0,
				rootMargin: '0px 0px -80% 0px'
			});
			io.observe(submit);
			
			var btn = document.getElementById('ptpStickyBtn');
			if (btn) {
				btn.addEventListener('click', function() {
					submit.click();
					
					// Custom event for tracking
					var evt = new CustomEvent('ptp:sticky-cta-click', {
						detail: {
							productId: btn.dataset.productId,
							price: btn.dataset.price
						}
					});
					window.dispatchEvent(evt);
				});
			}
		}
	})();
	</script>
	<?php

	// JSON-LD: Product + conditional Event
	$short_desc = $product->get_short_description();
	$description = $short_desc ? wp_strip_all_tags( $short_desc ) : wp_strip_all_tags( get_the_excerpt() );
	
	$schema = array(
		'@context' => 'https://schema.org',
		'@type'    => 'Product',
		'name'     => get_the_title( $product->get_id() ),
		'description' => $description,
		'brand'    => array(
			'@type' => 'Brand',
			'name'  => 'PTP'
		),
		'offers'   => array(
			'@type'         => 'Offer',
			'priceCurrency' => get_woocommerce_currency(),
			'price'         => number_format( (float) wc_get_price_to_display( $product ), 2, '.', '' ),
			'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
			'url'           => get_permalink( $product->get_id() ),
		),
	);
	
	// Add image if available
	if ( $product->get_image_id() ) {
		$image_url = wp_get_attachment_image_url( $product->get_image_id(), 'full' );
		if ( $image_url ) {
			$schema['image'] = $image_url;
		}
	}
	
	// Add Event schema if dates are available
	if ( ! empty( $when_lines ) ) {
		$valid_dates = array();
		foreach ( $when_lines as $line ) {
			// Try to parse date
			$timestamp = strtotime( $line );
			if ( $timestamp !== false ) {
				$valid_dates[] = $timestamp;
			}
		}
		
		if ( ! empty( $valid_dates ) ) {
			$first = min( $valid_dates );
			$last = max( $valid_dates );
			
			$event_schema = array(
				'@type'     => 'Event',
				'name'      => get_the_title( $product->get_id() ),
				'startDate' => gmdate( 'c', $first ),
				'endDate'   => gmdate( 'c', $last ),
			);
			
			// Add location if available
			if ( $f['venue'] || $f['address'] ) {
				$event_schema['location'] = array(
					'@type'   => 'Place',
					'name'    => $f['venue'] ? $f['venue'] : '',
					'address' => $f['address'] ? $f['address'] : '',
				);
			}
			
			// Add offers to event
			$event_schema['offers'] = $schema['offers'];
			
			$schema['event'] = $event_schema;
		}
	}
	
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>';

	// Woo after hook
	do_action( 'woocommerce_after_single_product' );

endwhile;

get_footer( 'shop' );
