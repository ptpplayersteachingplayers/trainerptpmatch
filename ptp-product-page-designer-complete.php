<?php
/**
 * Plugin Name: PTP Product Page Designer - Complete
 * Description: Custom product page with trust badges, urgency, and all required tabs (Description, Location, Schedule, Reviews, Safety)
 * Version: 3.0
 * Author: PTP Team
 * Compatible with: Astra Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class PTP_Product_Page_Designer_Complete {

    public function __construct() {
        // Enqueue CSS file
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));

        // Add facts bar after title
        add_action('woocommerce_single_product_summary', array($this, 'add_facts_bar'), 8);

        // Add price in buy box and social proof
        add_action('woocommerce_single_product_summary', array($this, 'add_buy_box_price'), 25);

        // Add coach strip beneath gallery
        add_action('woocommerce_product_thumbnails', array($this, 'add_coach_strip'), 40);

        // Add trust badges and urgency section after cart button
        add_action('woocommerce_after_add_to_cart_button', array($this, 'add_trust_urgency_section'));

        // Add invite-a-friend CTA beneath gallery
        add_action('woocommerce_product_thumbnails', array($this, 'add_invite_friend_section'), 50);

        // Add Trustindex reviews under product image
        add_action('woocommerce_product_thumbnails', array($this, 'add_trustindex_under_image'), 60);

        // Move tabs to right column under payment section
        remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
        add_action('woocommerce_single_product_summary', array($this, 'output_product_tabs'), 65);

        // Add custom tabs
        add_filter('woocommerce_product_tabs', array($this, 'add_custom_product_tabs'));

        // Add Event schema
        add_action('wp_footer', array($this, 'output_event_schema'));

        // Output lightweight UX helpers
        add_action('wp_footer', array($this, 'output_invite_friend_script'), 25);

        // Place related products directly beneath the summary content
        remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
        add_action('woocommerce_single_product_summary', array($this, 'add_related_products_block'), 80);
    }

    /**
     * Safely fetch current WooCommerce product.
     *
     * @return WC_Product|null
     */
    private function get_current_product() {
        if (!is_product()) {
            return null;
        }

        global $product;

        return ($product instanceof WC_Product) ? $product : null;
    }

    /**
     * Normalize any stored date/time value to display date.
     */
    private function format_event_date($raw) {
        if (empty($raw)) {
            return '';
        }

        if (is_numeric($raw)) {
            $timestamp = (int) $raw;
        } else {
            $timestamp = strtotime($raw);
        }

        if (!$timestamp) {
            return is_string($raw) ? $raw : '';
        }

        return wp_date('M j, Y', $timestamp);
    }

    /**
     * Normalize any stored date/time value to display time.
     */
    private function format_event_time($raw) {
        if (empty($raw)) {
            return '';
        }

        // Already time-like (HH:MM or similar)
        if (strlen($raw) <= 8 && preg_match('/\d/', $raw)) {
            return $raw;
        }

        $timestamp = is_numeric($raw) ? (int) $raw : strtotime($raw);

        if (!$timestamp) {
            return is_string($raw) ? $raw : '';
        }

        return wp_date('g:i A', $timestamp);
    }

    /**
     * Convert date/time values into ISO 8601 for schema.org.
     *
     * @param mixed $raw
     * @return string|null
     */
    private function format_iso8601($raw) {
        if (empty($raw)) {
            return null;
        }

        if (function_exists('wp_timezone')) {
            $timezone = wp_timezone();
        } elseif (function_exists('wp_timezone_string')) {
            $timezone = wp_timezone_string();
        } else {
            $timezone = get_option('timezone_string');
        }

        if (empty($timezone)) {
            $timezone = date_default_timezone_get();
        }

        try {
            if ($raw instanceof DateTimeInterface) {
                $dt = new DateTime($raw->format('c'));
            } elseif (is_numeric($raw)) {
                $dt = new DateTime('@' . (int) $raw);
            } else {
                $tz = is_object($timezone) ? $timezone : new DateTimeZone(is_string($timezone) ? (string) $timezone : date_default_timezone_get());
                $dt = new DateTime((string) $raw, $tz);
            }

            if (is_object($timezone)) {
                $dt->setTimezone($timezone);
            } elseif (is_string($timezone) && $timezone) {
                $dt->setTimezone(new DateTimeZone($timezone));
            }

            return $dt->format(DateTime::ATOM);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Enqueue CSS file
     */
    public function enqueue_styles() {
        if (is_product()) {
            wp_enqueue_style(
                'ptp-product-styles',
                plugin_dir_url(__FILE__) . 'ptp-product-styles.css',
                array(),
                '3.0'
            );
        }
    }

    /**
     * Add compact facts bar under title
     */
    public function add_facts_bar() {
        $product = $this->get_current_product();

        if (!$product) {
            return;
        }

        $product_id = $product->get_id();

        // Get facts from attributes or meta
        $city = $product->get_attribute('city') ?: get_post_meta($product_id, '_ptp_city', true);
        $date = $product->get_attribute('date') ?: get_post_meta($product_id, '_ptp_event_start', true);
        $time = $product->get_attribute('time') ?: get_post_meta($product_id, '_ptp_event_start', true);
        $ages = $product->get_attribute('age') ?: get_post_meta($product_id, '_ptp_age_band', true);

        $date_display = $this->format_event_date($date);
        $time_display = $this->format_event_time($time);

        if ($city || $date_display || $time_display || $ages) {
            ?>
            <div class="ptp-facts-bar">
                <?php if ($city) : ?>
                    <span><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> <?php echo esc_html($city); ?></span>
                <?php endif; ?>
                <?php if ($city && $date_display) : ?><span class="ptp-facts-divider">•</span><?php endif; ?>
                <?php if ($date_display) : ?>
                    <span><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> <?php echo esc_html($date_display); ?></span>
                <?php endif; ?>
                <?php if ($date_display && $time_display) : ?><span class="ptp-facts-divider">•</span><?php endif; ?>
                <?php if ($time_display) : ?>
                    <span><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> <?php echo esc_html($time_display); ?></span>
                <?php endif; ?>
                <?php if ($time_display || ($date_display && !$time_display)) : ?><span class="ptp-facts-divider">•</span><?php endif; ?>
                <span>3-hour clinic</span>
                <?php if ($ages) : ?>
                    <span class="ptp-facts-divider">•</span>
                    <span>Ages <?php echo esc_html($ages); ?></span>
                <?php endif; ?>
            </div>
            <?php
        }
    }

    /**
     * Add price in buy box (replaces hidden price)
     */
    public function add_buy_box_price() {
        $product = $this->get_current_product();

        if (!$product) {
            return;
        }

        $price_html = $product->get_price_html();
        $stock_qty = $product->get_stock_quantity();

        ?>
        <div class="ptp-buy-box-price">
            <?php echo $price_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php if ($stock_qty !== null && $stock_qty > 0) : ?>
                <span class="ptp-stock-counter"><?php echo esc_html($stock_qty); ?> spots available</span>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Add coach strip
     */
    public function add_coach_strip() {
        ?>
        <section class="ptp-coach-strip" aria-labelledby="ptp-coach-strip-heading">
            <div class="ptp-coach-strip-inner">
                <div class="ptp-coach-avatars" aria-hidden="true">
                    <span class="ptp-coach-avatar ptp-coach-avatar--a"></span>
                    <span class="ptp-coach-avatar ptp-coach-avatar--b"></span>
                    <span class="ptp-coach-avatar ptp-coach-avatar--c"></span>
                </div>
                <div class="ptp-coach-info">
                    <h3 id="ptp-coach-strip-heading" class="ptp-coach-title">Led by NCAA &amp; Pro Coaches</h3>
                    <p class="ptp-coach-copy">Small group stations, game-speed reps, and live feedback so every player levels up.</p>
                </div>
            </div>
        </section>
        <?php
    }

    /**
     * Add trust badges and urgency section after cart button
     */
    public function add_trust_urgency_section() {
        $product = $this->get_current_product();

        if (!$product) {
            return;
        }

        // Get stock quantity for dynamic urgency
        $stock_qty = $product->get_stock_quantity();

        ?>
        <section class="ptp-trust-urgency-wrapper" aria-label="Clinic trust signals">
            <?php if ($stock_qty !== null && $stock_qty > 0 && $stock_qty <= 50) : ?>
                <div class="ptp-urgency-banner" role="status" aria-live="polite">
                    <p class="ptp-urgency-text">
                        <?php if ($stock_qty <= 5) : ?>
                            <strong>Almost sold out!</strong> Only <?php echo esc_html($stock_qty); ?> spot<?php echo $stock_qty > 1 ? 's' : ''; ?> remaining
                        <?php elseif ($stock_qty <= 10) : ?>
                            <strong>Limited availability!</strong> Just <?php echo esc_html($stock_qty); ?> spots left
                        <?php elseif ($stock_qty <= 20) : ?>
                            <strong>Filling fast!</strong> <?php echo esc_html($stock_qty); ?> spots available
                        <?php else : ?>
                            <strong>Register today!</strong> Limited spots available
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="ptp-trust-badges" role="list">
                <div class="ptp-trust-badge" role="listitem">
                    <span class="ptp-badge-icon" aria-hidden="true">🔒</span>
                    <span class="ptp-badge-text">Secure payment processing</span>
                </div>
                <div class="ptp-trust-badge" role="listitem">
                    <span class="ptp-badge-icon" aria-hidden="true">🔄</span>
                    <span class="ptp-badge-text">Easy cancellation credits</span>
                </div>
                <div class="ptp-trust-badge" role="listitem">
                    <span class="ptp-badge-icon" aria-hidden="true">📞</span>
                    <span class="ptp-badge-text">Live support team</span>
                </div>
            </div>
        </section>
        <?php
    }

    /**
     * Invite-a-friend block to encourage sharing.
     */
    public function add_invite_friend_section() {
        $product = $this->get_current_product();

        if (!$product) {
            return;
        }

        $permalink = get_permalink($product->get_id());

        if (!$permalink) {
            return;
        }

        $title = wp_strip_all_tags(get_the_title($product->get_id()));
        $email_subject = rawurlencode(sprintf(__('Join me at %s', 'ptp'), $title));
        $email_body = rawurlencode(sprintf("I found this camp and thought you'd love it!\n\n%s", $permalink));
        $sms_body = rawurlencode(sprintf(__('Check out this camp: %s', 'ptp'), $permalink));

        ?>
        <section class="ptp-invite-friends" aria-labelledby="ptp-invite-heading">
            <div class="ptp-invite-icon" aria-hidden="true">🤝</div>
            <div class="ptp-invite-content">
                <h3 id="ptp-invite-heading"><?php esc_html_e('Camp is more fun with friends', 'ptp'); ?></h3>
                <p><?php esc_html_e('Share this clinic with teammates in one tap.', 'ptp'); ?></p>
                <div class="ptp-invite-actions">
                    <button type="button" class="ptp-copy-link" data-link="<?php echo esc_attr($permalink); ?>">
                        <?php esc_html_e('Copy link', 'ptp'); ?>
                    </button>
                    <a class="ptp-action-link" href="mailto:?subject=<?php echo esc_attr($email_subject); ?>&amp;body=<?php echo esc_attr($email_body); ?>">
                        <?php esc_html_e('Email invite', 'ptp'); ?>
                    </a>
                    <a class="ptp-action-link" href="sms:?&amp;body=<?php echo esc_attr($sms_body); ?>">
                        <?php esc_html_e('Text invite', 'ptp'); ?>
                    </a>
                </div>
                <p class="ptp-copy-feedback" role="status" aria-live="polite"></p>
            </div>
        </section>
        <?php
    }

    /**
     * Add Trustindex reviews under product image
     */
    public function add_trustindex_under_image() {
        if (!is_product() || !shortcode_exists('trustindex')) {
            return;
        }

        ?>
        <div class="ptp-trustindex-under-image">
            <h3 class="ptp-reviews-heading">What Parents Are Saying</h3>
            <?php echo do_shortcode('[trustindex no-registration=google]'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
        <?php
    }

    public function output_product_tabs() {
        woocommerce_output_product_data_tabs();
    }

    public function add_custom_product_tabs($tabs) {
        // Reorder tabs: Description (default) → Schedule → Location → Safety → Reviews
        if (isset($tabs['description'])) {
            $tabs['description']['priority'] = 10;
        }

        // Schedule tab
        $tabs['schedule'] = array(
            'title'    => __('Schedule', 'ptp'),
            'priority' => 20,
            'callback' => array($this, 'schedule_tab_content'),
        );

        // Location tab
        $tabs['location'] = array(
            'title'    => __('Location', 'ptp'),
            'priority' => 30,
            'callback' => array($this, 'location_tab_content'),
        );

        // Safety Reminders tab
        $tabs['safety'] = array(
            'title'    => __('Safety', 'ptp'),
            'priority' => 50,
            'callback' => array($this, 'safety_tab_content'),
        );

        // Reviews (already exists, just ensure priority)
        if (isset($tabs['reviews'])) {
            $tabs['reviews']['priority'] = 60;
        }

        unset($tabs['additional_information']);

        return $tabs;
    }

    public function location_tab_content() {
        $product = $this->get_current_product();

        if (!$product) {
            return;
        }

        // Try to get from product attributes first, then meta
        $venue_name = $product->get_attribute('venue') ?: get_post_meta($product->get_id(), '_ptp_venue_name', true);
        $address = $product->get_attribute('address') ?: get_post_meta($product->get_id(), '_ptp_address', true);
        $city = $product->get_attribute('city') ?: get_post_meta($product->get_id(), '_ptp_city', true);
        $state = $product->get_attribute('state') ?: get_post_meta($product->get_id(), '_ptp_state', true);
        $zip = $product->get_attribute('zip') ?: get_post_meta($product->get_id(), '_ptp_zip', true);

        // Fallback defaults
        if (empty($venue_name)) {
            $venue_name = 'Event Venue';
        }

        $parking_info = get_post_meta($product->get_id(), '_ptp_parking_info', true) ?: 'Free parking available on-site';
        $google_maps_url = get_post_meta($product->get_id(), '_ptp_google_maps_url', true);
        $google_maps_embed = get_post_meta($product->get_id(), '_ptp_google_maps_embed', true);

        $full_address = trim(implode(', ', array_filter(array($address, $city, $state))), ', ');
        if ($zip) {
            $full_address = trim($full_address . ' ' . $zip);
        }

        ?>
        <div class="ptp-location-grid">
            <div class="ptp-location-item">
                <strong>Venue</strong>
                <p><?php echo esc_html($venue_name); ?></p>
            </div>

            <?php if (!empty($city)) : ?>
            <div class="ptp-location-item">
                <strong>City</strong>
                <p><?php echo esc_html($city); ?><?php echo !empty($state) ? ', ' . esc_html($state) : ''; ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($address) || !empty($zip)) : ?>
            <div class="ptp-location-item">
                <strong>Address</strong>
                <p><?php echo esc_html($full_address); ?></p>
                <?php if (!empty($google_maps_url)) : ?>
                    <p><a href="<?php echo esc_url($google_maps_url); ?>" target="_blank" rel="noopener">View on Google Maps</a></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($parking_info)) : ?>
            <div class="ptp-location-item">
                <strong>Parking</strong>
                <p><?php echo esc_html($parking_info); ?></p>
            </div>
            <?php endif; ?>
        </div>

        <div class="ptp-map">
            <?php if (!empty($google_maps_embed)) : ?>
                <?php echo wp_kses_post($google_maps_embed); ?>
            <?php elseif (!empty($full_address) && strlen($full_address) > 5) : ?>
                <iframe
                    src="https://www.google.com/maps?q=<?php echo rawurlencode($full_address); ?>&amp;output=embed"
                    width="100%"
                    height="450"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy">
                </iframe>
            <?php else : ?>
                <div class="ptp-map-placeholder">
                    <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <p>Venue announced soon</p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    public function schedule_tab_content() {
        $product = $this->get_current_product();

        if (!$product) {
            return;
        }

        $schedule_items = get_post_meta($product->get_id(), '_ptp_schedule', true);

        if (empty($schedule_items) || !is_array($schedule_items)) {
            $schedule_items = array(
                array('time' => '9:00 AM', 'activity' => 'Check-in & Registration'),
                array('time' => '9:30 AM', 'activity' => 'Welcome & Opening Remarks'),
                array('time' => '10:00 AM', 'activity' => 'Main Event Begins'),
                array('time' => '12:00 PM', 'activity' => 'Lunch Break'),
                array('time' => '1:00 PM', 'activity' => 'Afternoon Session'),
                array('time' => '3:00 PM', 'activity' => 'Q&A and Networking'),
                array('time' => '4:00 PM', 'activity' => 'Event Concludes'),
            );
        }

        ?>
        <p class="ptp-schedule-intro">Here's what to expect during the event:</p>

        <div class="ptp-schedule-timeline">
            <?php foreach ($schedule_items as $item) :
                $time = isset($item['time']) ? $item['time'] : '';
                $activity = isset($item['activity']) ? $item['activity'] : '';

                if ($time === '' && $activity === '') {
                    continue;
                }
                ?>
                <div class="ptp-schedule-item">
                    <div class="ptp-schedule-time"><?php echo esc_html($time); ?></div>
                    <div class="ptp-schedule-activity"><?php echo esc_html($activity); ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <p class="ptp-schedule-note">
            <strong>Note:</strong> Schedule is subject to change. We'll notify you of any updates.
        </p>
        <?php
    }

    public function safety_tab_content() {
        $product = $this->get_current_product();

        if (!$product) {
            return;
        }

        $custom_reminders = get_post_meta($product->get_id(), '_ptp_safety_reminders', true);

        ?>
        <div id="tab-safety" class="ptp-tab-content">
            <h2>Safety &amp; What to Bring</h2>
            <p class="ptp-safety-intro">Please review these important reminders before attending your PTP soccer clinic.</p>

            <div class="ptp-safety-grid">
                <div class="ptp-safety-item">
                    <div class="ptp-safety-icon">⚽</div>
                    <h3>What to Bring</h3>
                    <p><strong>Required:</strong> Cleats (or flats for indoor), shin guards (must be covered by socks), water bottle<br><strong>Optional:</strong> Goalkeeper gloves<br><strong>We provide:</strong> Balls, cones, goals, pinnies</p>
                </div>

                <div class="ptp-safety-item">
                    <div class="ptp-safety-icon">👕</div>
                    <h3>Attire Requirements</h3>
                    <p><strong>Required:</strong> Shin guards, athletic wear, soccer socks, cleats<br><strong>Do not wear:</strong> Jewelry, metal hair clips, jeans, street shoes<br>Long hair must be tied back.</p>
                </div>

                <div class="ptp-safety-item">
                    <div class="ptp-safety-icon">🏥</div>
                    <h3>Medical &amp; Safety</h3>
                    <p>All Site Leads are CPR/First Aid certified. If your child has an EpiPen or inhaler, hand it to the Site Lead at check-in with a clear label. We operate nut-aware clinics - please avoid bringing nut products.</p>
                </div>

                <div class="ptp-safety-item">
                    <div class="ptp-safety-icon">☔</div>
                    <h3>Weather Policy</h3>
                    <p>For lightning, extreme heat, or poor air quality, we pause or reschedule. Light rain: we play. Check your email for updates. If PTP cancels, you receive automatic credit to a future clinic.</p>
                </div>

                <div class="ptp-safety-item">
                    <div class="ptp-safety-icon">👨‍👩‍👧</div>
                    <h3>Check-In &amp; Pick-Up</h3>
                    <p>Arrive 5 minutes early. Children must be signed in/out by an authorized adult. Please watch from designated spectator areas and refrain from sideline coaching.</p>
                </div>

                <div class="ptp-safety-item">
                    <div class="ptp-safety-icon">🎯</div>
                    <h3>Age-Specific Rules</h3>
                    <p><strong>Heading:</strong> None for 10U and under; limited for 11-13<br><strong>Contact:</strong> No slide tackling for younger ages<br>Coach-to-player ratio: ~1:8-10</p>
                </div>

                <div class="ptp-safety-item">
                    <div class="ptp-safety-icon">🌡️</div>
                    <h3>Illness Policy</h3>
                    <p><strong>Do not attend if your child has:</strong> Fever, flu symptoms, vomiting/diarrhea within 24 hours, or recent exposure to contagious illness. Recent injuries require medical clearance.</p>
                </div>

                <div class="ptp-safety-item">
                    <div class="ptp-safety-icon">📞</div>
                    <h3>Contact &amp; Questions</h3>
                    <p><strong>Phone:</strong> <a href="tel:9145237878">(914) 523-7878</a><br><strong>Email:</strong> <a href="mailto:info@ptpsports.com">info@ptpsports.com</a><br>All staff carry emergency contact information.</p>
                </div>
            </div>

            <?php if (!empty($custom_reminders)) : ?>
                <div class="ptp-custom-safety">
                    <h3>Clinic-Specific Safety Information</h3>
                    <?php echo wpautop(wp_kses_post($custom_reminders)); ?>
                </div>
            <?php endif; ?>

            <div class="ptp-safety-footer">
                <h3>Parent/Guardian Acknowledgment &amp; Agreement</h3>
                <p><strong>By registering for and attending this PTP Sports soccer clinic, you acknowledge that you have read, understood, and agree to follow all safety guidelines, policies, and procedures listed above.</strong> You confirm that your child is physically able to participate in high-intensity soccer training activities and that all medical information, emergency contacts, and allergy information provided during registration is accurate and complete.</p>

                <p><strong>You understand that:</strong></p>
                <ul class="ptp-acknowledgment-list">
                    <li>Soccer is a physical sport with inherent risks including but not limited to: collisions, falls, sprains, strains, and impact injuries</li>
                    <li>PTP Sports coaches are trained to minimize risks but cannot eliminate all possibility of injury</li>
                    <li>You are responsible for ensuring your child has appropriate medical insurance coverage</li>
                    <li>You will immediately notify staff of any injuries, illnesses, or concerns during the clinic</li>
                    <li>Failure to follow safety guidelines may result in removal from the clinic without refund</li>
                </ul>

                <p class="ptp-questions"><strong>Questions about safety policies or procedures?</strong><br>Contact us at <a href="mailto:info@ptpsports.com">info@ptpsports.com</a> or call <a href="tel:9145237878">(914) 523-7878</a>. We're here to ensure your child has a safe, fun, and developmental soccer experience.</p>
            </div>
        </div>
        <?php
    }

    /**
     * Output schema.org Event markup.
     */
    public function output_event_schema() {
        $product = $this->get_current_product();

        if (!$product) {
            return;
        }

        $product_id = $product->get_id();

        $start_meta = get_post_meta($product_id, '_ptp_event_start', true);
        $end_meta   = get_post_meta($product_id, '_ptp_event_end', true);

        $start_iso = $this->format_iso8601($start_meta ?: $product->get_attribute('date'));
        $end_iso   = $this->format_iso8601($end_meta ?: $product->get_attribute('end-date'));

        if (!$start_iso) {
            return;
        }

        $venue   = $product->get_attribute('venue') ?: get_post_meta($product_id, '_ptp_venue_name', true);
        $address = $product->get_attribute('address') ?: get_post_meta($product_id, '_ptp_address', true);
        $city    = $product->get_attribute('city') ?: get_post_meta($product_id, '_ptp_city', true);
        $state   = $product->get_attribute('state') ?: get_post_meta($product_id, '_ptp_state', true);
        $zip     = $product->get_attribute('zip') ?: get_post_meta($product_id, '_ptp_zip', true);

        $image_id  = $product->get_image_id();
        $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'full') : '';

        $description = $product->get_short_description();
        if (empty($description)) {
            $description = $product->get_description();
        }

        $price = wc_get_price_to_display($product);
        $currency = get_woocommerce_currency();

        $offers = array(
            '@type'         => 'Offer',
            'url'           => get_permalink($product_id),
            'priceCurrency' => $currency,
            'availability'  => 'https://schema.org/' . ($product->is_in_stock() ? 'InStock' : 'OutOfStock'),
            'validFrom'     => $start_iso,
        );

        if ($price !== '') {
            $offers['price'] = (float) $price;
        }

        $event = array(
            '@context' => 'https://schema.org',
            '@type'    => 'Event',
            'name'     => wp_strip_all_tags(get_the_title($product_id)),
            'description' => wp_strip_all_tags($description),
            'startDate' => $start_iso,
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'eventStatus' => 'https://schema.org/EventScheduled',
            'location' => array(
                '@type' => 'Place',
                'name'  => wp_strip_all_tags($venue ?: 'Event Location'),
                'address' => array(
                    '@type'           => 'PostalAddress',
                    'streetAddress'   => wp_strip_all_tags($address),
                    'addressLocality' => wp_strip_all_tags($city),
                    'addressRegion'   => wp_strip_all_tags($state),
                    'postalCode'      => wp_strip_all_tags($zip),
                    'addressCountry'  => 'US',
                ),
            ),
            'offers' => $offers,
        );

        if ($end_iso) {
            $event['endDate'] = $end_iso;
        }

        if ($image_url) {
            $event['image'] = array($image_url);
        }

        $json = wp_json_encode($event, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if (!$json) {
            return;
        }

        echo '<script type="application/ld+json">' . $json . '</script>';
    }

    /**
     * Lightweight client-side helpers for invite-a-friend UX.
     */
    public function output_invite_friend_script() {
        if (!is_product()) {
            return;
        }

        $copy_success = esc_js(__('Link copied! Share it with your friends.', 'ptp'));
        $copy_error = esc_js(__('Copy failed — use the email or text buttons.', 'ptp'));
        $copy_missing = esc_js(__('Unable to copy link.', 'ptp'));

        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
          var sections = document.querySelectorAll('.ptp-invite-friends');
          if (!sections.length) return;

          sections.forEach(function(section) {
            var button = section.querySelector('.ptp-copy-link');
            var feedback = section.querySelector('.ptp-copy-feedback');
            if (!button) return;

            var showMessage = function(message, isError) {
              if (!feedback) return;
              feedback.textContent = message;
              feedback.classList.toggle('is-error', !!isError);
              feedback.classList.add('is-visible');
              window.setTimeout(function() {
                feedback.textContent = '';
                feedback.classList.remove('is-visible');
                feedback.classList.remove('is-error');
              }, 4000);
            };

            button.addEventListener('click', function() {
              var link = button.getAttribute('data-link');
              if (!link) {
                showMessage('<?php echo $copy_missing; ?>', true);
                return;
              }

              var onSuccess = function() {
                showMessage('<?php echo $copy_success; ?>');
              };

              var onError = function() {
                showMessage('<?php echo $copy_error; ?>', true);
              };

              if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(link).then(onSuccess, onError);
                return;
              }

              var temp = document.createElement('input');
              temp.value = link;
              temp.setAttribute('readonly', '');
              temp.style.position = 'absolute';
              temp.style.opacity = '0';
              document.body.appendChild(temp);
              temp.select();

              try {
                var ok = document.execCommand('copy');
                document.body.removeChild(temp);
                if (ok) {
                  onSuccess();
                } else {
                  onError();
                }
              } catch (err) {
                document.body.removeChild(temp);
                onError();
              }
            });
          });
        });
        </script>
        <?php
    }

    /**
     * Render related products within the summary column for consistent UX.
     */
    public function add_related_products_block() {
        if (!is_product()) {
            return;
        }

        ob_start();
        woocommerce_output_related_products();
        $html = trim(ob_get_clean());

        if (empty($html)) {
            return;
        }

        echo '<section class="ptp-related-products" aria-labelledby="ptp-related-heading">';
        echo '<div class="ptp-related-header">';
        echo '<h3 id="ptp-related-heading">' . esc_html__('More camps you might like', 'ptp') . '</h3>';
        echo '<p>' . esc_html__('Invite a teammate or explore another session nearby.', 'ptp') . '</p>';
        echo '</div>';
        echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '</section>';
    }
}

new PTP_Product_Page_Designer_Complete();

// Meta boxes for admin
add_action('add_meta_boxes', 'ptp_add_meta_boxes');
function ptp_add_meta_boxes() {
    add_meta_box('ptp_location_meta', 'Location Details', 'ptp_location_meta_box', 'product', 'normal');
    add_meta_box('ptp_schedule_meta', 'Event Schedule', 'ptp_schedule_meta_box', 'product', 'normal');
    add_meta_box('ptp_safety_meta', 'Safety Reminders', 'ptp_safety_meta_box', 'product', 'normal');
}

function ptp_location_meta_box($post) {
    wp_nonce_field('ptp_meta', 'ptp_meta_nonce');
    $fields = array('venue_name', 'address', 'city', 'state', 'zip', 'parking_info', 'google_maps_url');
    foreach ($fields as $field) {
        $value = get_post_meta($post->ID, '_ptp_' . $field, true);
        echo '<p><label><strong>' . esc_html(ucwords(str_replace('_', ' ', $field))) . ':</strong></label><br>';
        $type = $field === 'parking_info' ? 'textarea' : 'input';
        if ($type === 'textarea') {
            echo '<textarea name="ptp_' . esc_attr($field) . '" style="width:100%;height:80px;">' . esc_textarea($value) . '</textarea>';
        } else {
            echo '<input type="text" name="ptp_' . esc_attr($field) . '" value="' . esc_attr($value) . '" style="width:100%;">';
        }
        echo '</p>';
    }
    $embed = get_post_meta($post->ID, '_ptp_google_maps_embed', true);
    echo '<p><label><strong>Google Maps Embed:</strong></label><br>';
    echo '<textarea name="ptp_google_maps_embed" style="width:100%;height:100px;">' . esc_textarea($embed) . '</textarea></p>';
}

function ptp_schedule_meta_box($post) {
    wp_nonce_field('ptp_meta', 'ptp_meta_nonce');
    $schedule = get_post_meta($post->ID, '_ptp_schedule', true);
    if (empty($schedule) || !is_array($schedule)) {
        $schedule = array(array('time' => '', 'activity' => ''));
    }
    echo '<div id="ptp-schedule-items">';
    foreach ($schedule as $i => $item) {
        $time = isset($item['time']) ? $item['time'] : '';
        $activity = isset($item['activity']) ? $item['activity'] : '';
        echo '<div style="margin-bottom:10px;">';
        echo '<input type="text" name="ptp_schedule[' . intval($i) . '][time]" value="' . esc_attr($time) . '" placeholder="9:00 AM" style="width:20%;" />';
        echo '<input type="text" name="ptp_schedule[' . intval($i) . '][activity]" value="' . esc_attr($activity) . '" placeholder="Activity" style="width:75%;margin-left:4px;" />';
        echo '</div>';
    }
    echo '</div><button type="button" id="ptp-add-schedule" class="button">Add Item</button>';
    $count = count($schedule);
    $template = '<div style="margin-bottom:10px;"><input type="text" name="ptp_schedule[__index__][time]" placeholder="9:00 AM" style="width:20%;" /> <input type="text" name="ptp_schedule[__index__][activity]" placeholder="Activity" style="width:75%;margin-left:4px;" /></div>';
    $encoded_template = wp_json_encode($template);
    if ($encoded_template) {
        echo '<script>jQuery(function($){var i=' . (int) $count . ';var tpl=' . $encoded_template . ';$("#ptp-add-schedule").on("click",function(){var html=tpl.replace(/__index__/g,i);$("#ptp-schedule-items").append(html);i++;});});</script>';
    }
}

function ptp_safety_meta_box($post) {
    wp_nonce_field('ptp_meta', 'ptp_meta_nonce');
    $reminders = get_post_meta($post->ID, '_ptp_safety_reminders', true);
    echo '<p><label><strong>Additional Safety Reminders:</strong></label><br>';
    echo '<textarea name="ptp_safety_reminders" style="width:100%;height:150px;">' . esc_textarea($reminders) . '</textarea></p>';
}

add_action('save_post', 'ptp_save_meta_boxes');
function ptp_save_meta_boxes($post_id) {
    if (!isset($_POST['ptp_meta_nonce']) || !wp_verify_nonce($_POST['ptp_meta_nonce'], 'ptp_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (isset($_POST['post_type']) && 'product' !== $_POST['post_type']) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $text_fields = array('venue_name', 'address', 'city', 'state', 'zip', 'parking_info');
    foreach ($text_fields as $field) {
        if (isset($_POST['ptp_' . $field])) {
            $value = $field === 'parking_info' ? sanitize_textarea_field(wp_unslash($_POST['ptp_' . $field])) : sanitize_text_field(wp_unslash($_POST['ptp_' . $field]));
            update_post_meta($post_id, '_ptp_' . $field, $value);
        }
    }

    if (isset($_POST['ptp_google_maps_url'])) {
        update_post_meta($post_id, '_ptp_google_maps_url', esc_url_raw(wp_unslash($_POST['ptp_google_maps_url'])));
    }

    if (isset($_POST['ptp_google_maps_embed'])) {
        update_post_meta($post_id, '_ptp_google_maps_embed', wp_kses_post(wp_unslash($_POST['ptp_google_maps_embed'])));
    }

    if (isset($_POST['ptp_safety_reminders'])) {
        update_post_meta($post_id, '_ptp_safety_reminders', wp_kses_post(wp_unslash($_POST['ptp_safety_reminders'])));
    }

    if (isset($_POST['ptp_schedule']) && is_array($_POST['ptp_schedule'])) {
        $schedule = array();
        foreach ($_POST['ptp_schedule'] as $item) {
            $time = isset($item['time']) ? sanitize_text_field(wp_unslash($item['time'])) : '';
            $activity = isset($item['activity']) ? sanitize_text_field(wp_unslash($item['activity'])) : '';
            if ($time === '' && $activity === '') {
                continue;
            }
            $schedule[] = array(
                'time'     => $time,
                'activity' => $activity,
            );
        }
        update_post_meta($post_id, '_ptp_schedule', $schedule);
    }
}

