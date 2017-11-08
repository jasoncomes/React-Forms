<?php

/**
 * Create Shortcode webform
 * Use the shortcode: [webform form="join-now|discovery-session|meet-with-coach"]
 */
function webform_shortcode($atts, $content = null) 
{
    // Attributes
    $atts = shortcode_atts(array('form' => 'join-now'), $atts, 'webform');

    // HTML
    if (is_user_logged_in()) {

        $html = '<div class="shortcodeBox">Please logout to view the form.</div>';

    } elseif(!empty($atts['form']) && in_array($atts['form'], array('join-now', 'discovery-session', 'meet-with-coach'))) {

        $html = '<div id="webform" data-form="' . $atts['form'] . '" data-nonce="' . wp_create_nonce('webform-script-nonce') . '" data-ajaxurl="' . admin_url('admin-ajax.php') . '"></div>';    

    } else {

        $html = '<div class="shortcodeBox">Please verify webform form attibute is one of the following and spelled correctly: join-now, discovery-session or meet-with-coach. e.g. &#91;webform form="join-now"&#93;</div>';

    }

    // Return
    return $html;
}
add_shortcode('webform', 'webform_shortcode');



/**
 * Profile Form - JS Assets & AJAX Object
 *
 */
function webformAssets() 
{
    global $post;

    // CTA Styles
    if (in_array(get_the_ID(), array(88, 61, 94))) {
        wp_enqueue_style('cta-styles', get_template_directory_uri() . '/webforms/assets/styles-cta.css');
        return;
    }

    if (!is_a($post, 'WP_Post') || !has_shortcode($post->post_content, 'webform')) {
        return;
    }

    // Remove Unneccessary Scripts on Webform Page
    if (is_page_template('webforms/page-webform.php')) {

        // Remove Unneeded Scripts
        wp_deregister_script('contact-form-7');
        wp_deregister_script('discovery_session_js');
        wp_deregister_script('fancybox');
        //wp_deregister_script('jquery');
        wp_deregister_script('jquery-fancybox');
        wp_deregister_script('jquery-easing');
        wp_deregister_script('jquery-mousewheel');
        wp_deregister_script('jquery-metadata');
        wp_deregister_script('common');
        wp_deregister_script('wp-list');
        wp_deregister_script('postbox');

        // Remove Unneeded Styles
        wp_deregister_style('fancybox');
        wp_deregister_style('wpsl-styles');
        wp_deregister_style('iconic-nav-style');
        wp_deregister_style('contact-form-7');
        
    }

    // Scripts
    wp_enqueue_script('webform', get_template_directory_uri() . '/webforms/assets/script.js', null, null, true);

    // Styles
    wp_enqueue_style('webform', get_template_directory_uri() . '/webforms/assets/styles.css'); 
    
}
add_action('wp_enqueue_scripts', 'webformAssets', 1000);



/**
 * Add Preload Script to site
 */
function preloadWebformScript() 
{
    global $post;

    // Preload Script
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'webform')) {
        echo '<link rel="preload" href="' . get_template_directory_uri() . '/webforms/assets/script.js" as="script">';
        return;    
    }

    // Prefetch Script
    echo '<link rel="prefetch" href="' . get_template_directory_uri() . '/webforms/assets/script.js">';
    
}
add_action('wp_head', 'preloadWebformScript');



/**
 * Store Locator - Custom Data
 */
function webforms_meta_box_fields($meta_fields) {
    
    $meta_fields['Custom Data'] = array_merge($meta_fields['Custom Data'], array(
        'tax_rate' => array('label' => 'Tax Rate - Default'),
        'tax_rate_300' => array('label' => 'Tax Rate - $300'),
        'tax_rate_150' => array('label' => 'Tax Rate - $150'),
        'tax_rate_99' => array('label' => 'Tax Rate - $99'),
        'tax_rate_69' => array('label' => 'Tax Rate - $69'),
        'tax_rate_50' => array('label' => 'Tax Rate - $50')
    ));

    return $meta_fields;
}
add_filter('wpsl_meta_box_fields', 'webforms_meta_box_fields');



/**
 * Options Page - Built for Discounts
 */
$discountPrices = array(
    '0' => '$0',
    '50' => '$50',
    '69' => '$69',
    '99' => '$99',
    '150' => '$150',
);

$offerPrices = $discountPrices;
unset($offerPrices[0]);

// Discount Options Page
if (function_exists('acf_add_options_page')) {
    acf_add_options_page(array(
        'page_title'    => 'Discounts',
        'menu_title'    => 'Discounts',
        'menu_slug'     => 'discounts',
        'capability'    => 'edit_posts',
        'redirect'      => false
    ));
}

// Discounts Option Fields
if (function_exists("register_field_group")) {
    register_field_group(array(
        'id' => 'acf_discount-settings',
        'title' => 'Discount Settings',
        'fields' => array(
            array(
                'key' => 'field_59eec6facf202',
                'label' => 'Limited Time Offer',
                'name' => '',
                'type' => 'tab',
            ),
            array(
                'key' => 'field_59eec6dccf201',
                'label' => 'Price',
                'name' => 'limited_time_offer',
                'type' => 'select',
                'choices' => $offerPrices,
                'default_value' => '',
                'allow_null' => 1,
                'multiple' => 0,
            ),
            array(
                'key' => 'field_59eec710cf203',
                'label' => 'Promo Codes',
                'name' => '',
                'type' => 'tab',
            ),
            array(
                'key' => 'field_59eec722cf204',
                'label' => 'Promos',
                'name' => 'promos',
                'type' => 'repeater',
                'sub_fields' => array(
                    array(
                        'key' => 'field_59eec733cf205',
                        'label' => 'Title',
                        'name' => 'title',
                        'type' => 'text',
                        'required' => 1,
                        'column_width' => '',
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'formatting' => 'html',
                        'maxlength' => '',
                    ),
                    array(
                        'key' => 'field_59eec738cf206',
                        'label' => 'Code',
                        'name' => 'code',
                        'type' => 'text',
                        'required' => 1,
                        'column_width' => '',
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'formatting' => 'html',
                        'maxlength' => '',
                    ),
                    array(
                        'key' => 'field_59eec752cf207',
                        'label' => 'Price',
                        'name' => 'price',
                        'type' => 'select',
                        'required' => 1,
                        'column_width' => '',
                        'choices' => $discountPrices,
                        'default_value' => '',
                        'allow_null' => 0,
                        'multiple' => 0,
                    ),
                    array(
                        'key' => 'field_59fff2d839dd6',
                        'label' => 'Start Date',
                        'name' => 'start_date',
                        'type' => 'date_picker',
                        'column_width' => '',
                        'date_format' => '@',
                        'display_format' => 'mm/dd/yy',
                        'first_day' => 1,
                    ),
                    array(
                        'key' => 'field_59fff31d39dd7',
                        'label' => 'End Date',
                        'name' => 'end_date',
                        'type' => 'date_picker',
                        'column_width' => '',
                        'date_format' => '@',
                        'display_format' => 'mm/dd/yy',
                        'first_day' => 1,
                    ),
                ),
                'row_min' => '',
                'row_limit' => '',
                'layout' => 'row',
                'button_label' => 'Add Promo',
            ),
            array(
                'key' => 'field_59eec77acf208',
                'label' => 'Location Offers',
                'name' => '',
                'type' => 'tab',
            ),
            array(
                'key' => 'field_59eec7a6cf209',
                'label' => 'Locations',
                'name' => 'locations',
                'type' => 'repeater',
                'sub_fields' => array(
                    array(
                        'key' => 'field_59eec7bbcf20a',
                        'label' => 'Location',
                        'name' => 'location',
                        'type' => 'post_object',
                        'required' => 1,
                        'column_width' => '',
                        'post_type' => array(
                            0 => 'wpsl_stores',
                        ),
                        'taxonomy' => array(
                            0 => 'all',
                        ),
                        'allow_null' => 0,
                        'multiple' => 0,
                    ),
                    array(
                        'key' => 'field_59eec7e8cf20b',
                        'label' => 'Price',
                        'name' => 'price',
                        'type' => 'select',
                        'required' => 1,
                        'column_width' => '',
                        'choices' => $discountPrices,
                        'default_value' => '',
                        'allow_null' => 0,
                        'multiple' => 0,
                    ),
                ),
                'row_min' => '',
                'row_limit' => '',
                'layout' => 'row',
                'button_label' => 'Add Location',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'discounts',
                    'order_no' => 0,
                    'group_no' => 0,
                ),
            ),
        ),
        'options' => array(
            'position' => 'normal',
            'layout' => 'default',
            'hide_on_screen' => array(
            ),
        ),
        'menu_order' => 0,
    ));
}

// CTA Sections
if (function_exists("register_field_group")) {
    register_field_group(array(
        'id' => 'acf_section-call-to-action',
        'title' => 'Section - Call To Action',
        'fields' => array(
            array(
                'key' => 'field_59f6c0634899a',
                'label' => 'Include',
                'name' => 'cta_include',
                'type' => 'true_false',
                'message' => '',
                'default_value' => 0,
            ),
            array(
                'key' => 'field_59f6bb1fa29c9',
                'label' => 'Title',
                'name' => 'cta_title',
                'type' => 'text',
                'conditional_logic' => array(
                    'status' => 1,
                    'rules' => array(
                        array(
                            'field' => 'field_59f6c0634899a',
                            'operator' => '==',
                            'value' => '1',
                        ),
                    ),
                    'allorany' => 'all',
                ),
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'formatting' => 'html',
                'maxlength' => '',
            ),
            array(
                'key' => 'field_59f6bb2da29cb',
                'label' => 'Description',
                'name' => 'cta_description',
                'type' => 'wysiwyg',
                'conditional_logic' => array(
                    'status' => 1,
                    'rules' => array(
                        array(
                            'field' => 'field_59f6c0634899a',
                            'operator' => '==',
                            'value' => '1',
                        ),
                    ),
                    'allorany' => 'all',
                ),
                'default_value' => '',
                'toolbar' => 'basic',
                'media_upload' => 'no',
            ),
            array(
                'key' => 'field_59f6bb40a29cc',
                'label' => 'Icon',
                'name' => 'cta_icon',
                'type' => 'image',
                'conditional_logic' => array(
                    'status' => 1,
                    'rules' => array(
                        array(
                            'field' => 'field_59f6c0634899a',
                            'operator' => '==',
                            'value' => '1',
                        ),
                    ),
                    'allorany' => 'all',
                ),
                'save_format' => 'url',
                'preview_size' => 'thumbnail',
                'library' => 'uploadedTo',
            ),
            array(
                'key' => 'field_59f6bbb9a29ce',
                'label' => 'Include Memberships',
                'name' => 'cta_include_memberships',
                'type' => 'true_false',
                'conditional_logic' => array(
                    'status' => 1,
                    'rules' => array(
                        array(
                            'field' => 'field_59f6c0634899a',
                            'operator' => '==',
                            'value' => '1',
                        ),
                    ),
                    'allorany' => 'all',
                ),
                'message' => '',
                'default_value' => 0,
            ),
            array(
                'key' => 'field_59f6bb5ba29cd',
                'label' => 'Include Buttons',
                'name' => 'cta_include_buttons',
                'type' => 'true_false',
                'conditional_logic' => array(
                    'status' => 1,
                    'rules' => array(
                        array(
                            'field' => 'field_59f6c0634899a',
                            'operator' => '==',
                            'value' => '1',
                        ),
                    ),
                    'allorany' => 'all',
                ),
                'message' => '',
                'default_value' => 0,
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'page',
                    'operator' => '==',
                    'value' => '88',
                    'order_no' => 0,
                    'group_no' => 0,
                ),
            ),
            array(
                array(
                    'param' => 'page',
                    'operator' => '==',
                    'value' => '61',
                    'order_no' => 0,
                    'group_no' => 1,
                ),
            ),
            array(
                array(
                    'param' => 'page',
                    'operator' => '==',
                    'value' => '94',
                    'order_no' => 0,
                    'group_no' => 2,
                ),
            ),
        ),
        'options' => array(
            'position' => 'normal',
            'layout' => 'default',
            'hide_on_screen' => array(
            ),
        ),
        'menu_order' => 0,
    ));
}

/*
 * ACF Options Discount - Transient
 *
 */
function discountsTransients() {

    // Detect ACF Options Screen
    $screen = get_current_screen();

    // Create Discounts Transients.
    if ($screen->id == 'toplevel_page_discounts') {

        /*
         * Limited Time Offer
         *
         */
        $limited = (int) get_field('limited_time_offer', 'option');

        // Limited Time Offer Transient
        set_transient('discounts_limited_time_offer', (!empty($limited) ? $limited : ''));


        /*
         * Promo Offers
         *
         */
        $promos = array();
        $promoResults = get_field('promos', 'option');

        // If Promos.
        if ($promoResults) {

            // Loop Promos.
            foreach ($promoResults as $promoResult) {
                $promos[] = (object) array(
                    'title'      => $promoResult['title'],
                    'code'       => $promoResult['code'],
                    'price'      => (int) $promoResult['price'],
                    'start_date' => !empty($promoResult['start_date']) ? (int) $promoResult['start_date'] : 0,
                    'end_date'   => !empty($promoResult['end_date']) ? (int) $promoResult['end_date'] : 0
                );
            }
        }

        // Promos Transient.
        set_transient('discounts_promos', (!empty($promos) ? serialize($promos) : ''));


        /*
         * Location Offers
         *
         */
        $locationOffers = new stdClass();
        $locationResults = get_field('locations', 'option');
        
        // If Locations.
        if ($locationResults) {

            // Loop Locatins.
            foreach ($locationResults as $locationResult) {
                 $p = $locationResult['location'];

                if (!array_key_exists($p->post_name, $locationOffers)) {
                    $locationOffers->{$p->post_name} = (int) $locationResult['price'];
                }
            }
        }

        // Location Offer Transient
        set_transient('discounts_location_offers', (!empty($locationOffers) ? serialize($locationOffers) : ''));

    }
}
add_action('acf/save_post', 'discountsTransients', 20);


/**
 * Ajax - Datapoints locations/events/employers
 *
 */
include_once 'ajax-data.php';


/**
 * Ajax - DB Entries
 *
 */
include_once 'ajax-wordpressdb.php';



/**
 * Ajax - Authorize.net
 *
 */
include_once 'ajax-authorize.php';



/**
 * Ajax - Mailer
 *
 */
include_once 'ajax-mailer.php';
