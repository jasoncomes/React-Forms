<?php

/**
 * MAILER
 * Send different mail messages based on form.
 */
function webformData() 
{   
    // Variables
    global $wpdb;
    $results = array();

    /**
     * Locations + Events
     */
    $locations = new stdClass();

    $queryLocations = "
        SELECT DISTINCT 
        post.post_title AS title,
        post.post_name AS slug,
        GROUP_CONCAT(meta.meta_key SEPARATOR '|') AS meta_keys,
        GROUP_CONCAT(meta.meta_value SEPARATOR '|') AS meta_values
        FROM wp_posts post 
        LEFT JOIN wp_postmeta meta on meta.post_id = post.ID
        WHERE (post.post_status = 'publish' AND post.post_type = 'wpsl_stores')
        AND (
            meta.meta_key = 'wpsl_store_id' 
            OR meta.meta_key = 'wpsl_tax_rate' 
            OR meta.meta_key = 'wpsl_tax_rate_300' 
            OR meta.meta_key = 'wpsl_tax_rate_150' 
            OR meta.meta_key = 'wpsl_tax_rate_99' 
            OR meta.meta_key = 'wpsl_tax_rate_69' 
            OR meta.meta_key = 'wpsl_tax_rate_50'
            )
        GROUP BY post.post_title, post.post_name
        ORDER BY post.post_title ASC
    ";

    $locationResults = $wpdb->get_results($queryLocations, OBJECT);

    if (!empty($locationResults)) {

        foreach ($locationResults as $locationResult) {

            // Venu Data.
            $dates = array();
            $events = eo_get_events(array(
                'venue'          => $locationResult->slug,
                'numberposts'    => 5,
                'showpastevents' => false
            )); 

            if (!empty($events)) {
                foreach ($events as $event) {
                    $format = eo_is_all_day($event->ID) ? get_option('date_format') : get_option('date_format') . ' ' . get_option('time_format');
                    $dates[] = eo_get_the_start($format, $event->ID, $event->occurrence_id);
                }
                $dates = array_unique($dates);
            }

            // Meta
            $meta = array_combine(explode('|', $locationResult->meta_keys), explode('|', $locationResult->meta_values));            
            $meta['id'] = $meta['wpsl_store_id'];
            $meta['taxRate']['default'] = !empty($meta['wpsl_tax_rate']) ? (float) $meta['wpsl_tax_rate'] : 6.8;
            $meta['taxRate']['300'] = !empty($meta['wpsl_tax_rate_300']) ? (float) $meta['wpsl_tax_rate_300'] : $meta['taxRate']['default'];
            $meta['taxRate']['150'] = !empty($meta['wpsl_tax_rate_150']) ? (float) $meta['wpsl_tax_rate_150'] : $meta['taxRate']['default'];
            $meta['taxRate']['99'] = !empty($meta['wpsl_tax_rate_99']) ? (float) $meta['wpsl_tax_rate_99'] : $meta['taxRate']['default'];
            $meta['taxRate']['69'] = !empty($meta['wpsl_tax_rate_69']) ? (float) $meta['wpsl_tax_rate_69'] : $meta['taxRate']['default'];
            $meta['taxRate']['50'] = !empty($meta['wpsl_tax_rate_50']) ? (float) $meta['wpsl_tax_rate_50'] : $meta['taxRate']['default'];
            unset($meta['wpsl_store_id']);
            unset($meta['wpsl_tax_rate']);
            unset($meta['wpsl_tax_rate_300']);
            unset($meta['wpsl_tax_rate_150']);
            unset($meta['wpsl_tax_rate_99']);
            unset($meta['wpsl_tax_rate_69']);
            unset($meta['wpsl_tax_rate_50']);

            // Location
            $location = new stdClass();
            $location = (object) $meta;
            $location->title = $locationResult->title;
            $location->events = $dates;
            
            // Location to Locations
            if (!array_key_exists($locationResult->slug, $locations)) {
                $locations->{$locationResult->slug} = $location;
            }
        }
    }
    $results['locations'] = !empty($locations) ? $locations : false;


    /**
     * Partners
     */
    $partners = array();

    $queryPartners = "
        SELECT DISTINCT post.post_title AS title
        FROM wp_posts post
        WHERE post.post_status = 'publish' 
        AND post.post_type = 'partner'
        ORDER BY post.post_title ASC
    ";

    $parnterResults = $wpdb->get_results($queryPartners, ARRAY_A);

    if (!empty($parnterResults)) {

        foreach ($parnterResults as $parnterResult) {
            $partners[] = $parnterResult['title'];
        }

        $partners = array_unique($partners);
    }

    $results['partners'] = !empty($partners) ? $partners : false;


    /**
     * Discounts
     */

    // Limited Time Offer
    if (false === ($limited = get_transient('discounts_limited_time_offer' ))) {
        // Limited Time Offer - ACF
        $limited = get_field('limited_time_offer', 'option');
    } 

    // Limited Time Offer Return.
    $results['discounts']['limited'] = !empty($limited) ? (int) $limited : '';
    

    // Promo Codes
    $promos = array();
    $currentDate = current_time('timestamp', 0);

    if (false === ($transientPromos = get_transient('discounts_promos' ))) {

        // Promos - ACF
        $promoResults = get_field('promos', 'option');

        if ($promoResults) {

            foreach ($promoResults as $promoResult) {
                $startDate   = (int) $promoResult['start_date'];
                $endDate     = (int) $promoResult['end_date'];
                $startDate   = !empty($startDate) ? $startDate / 1000 : false;
                $endDate     = !empty($endDate) ? $endDate / 1000 : false;

                if (
                    (empty($startDate) && empty($endDate)) ||
                    (empty($endDate) && $currentDate >= $startDate) ||
                    (empty($startDate) && $currentDate <= $endDate) ||
                    ($currentDate >= $startDate && $currentDate <= $endDate)
                ) {
                    $promos[] = (object) array(
                        'title'      => $promoResult['title'],
                        'code'       => $promoResult['code'],
                        'price'      => (int) $promoResult['price']
                    );
                }
            }
        }

    } else {

        // Transient Promo
        $transientPromos = unserialize($transientPromos);

        // Loop Promos - Start/End Date.
        foreach ($transientPromos as $transientPromo) {
            $startDate = !empty($transientPromo->start_date) ? (int) $transientPromo->start_date / 1000 : false;
            $endDate   = !empty($transientPromo->end_date) ? (int) $transientPromo->end_date / 1000 : false;

            if (
                (empty($startDate) && empty($endDate)) ||
                (empty($endDate) && $currentDate >= $startDate) ||
                (empty($startDate) && $currentDate <= $endDate) ||
                ($currentDate >= $startDate && $currentDate <= $endDate)
            ) {
                $promos[] = (object) array(
                    'title' => $transientPromo->title,
                    'code'  => $transientPromo->code,
                    'price' => (int) $transientPromo->price,
                );
            }
        }
    }

    // Promos Return.
    $results['discounts']['promos'] = !empty($promos) ? $promos : '';


    // Location Offers
    $locationOffers = new stdClass();

    if (false === ($transientLocations = get_transient('discounts_location_offers' ))) {

        // Locations - ACF
        $locationResults = get_field('locations', 'option');

        if ($locationResults) {
            $currentDate = current_time('timestamp', 0);

            foreach ($locationResults as $locationResult) {
                 $p = $locationResult['location'];

                if (!array_key_exists($p->post_name, $locationOffers)) {
                    $locationOffers->{$p->post_name} = (int) $locationResult['price'];
                }
            }
        }

    } else {

        $locationOffers = unserialize($transientLocations);

    }

    // Location Offers Return.
    $results['discounts']['locations'] = !empty($locationOffers) ? $locationOffers : '';


    // Response output in JSON Representation
    header("Content-Type: application/json");
    echo json_encode($results);


    // End AJAX Request.
    wp_die();
}
add_action('wp_ajax_webform_data_transaction', 'webformData');
add_action('wp_ajax_nopriv_webform_data_transaction', 'webformData');
