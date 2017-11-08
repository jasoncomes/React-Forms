<?php
/*
Template Name: AJAX Template
*/

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
        post.post_name AS venue,
        GROUP_CONCAT(meta.meta_key SEPARATOR '|') AS meta_keys,
        GROUP_CONCAT(meta.meta_value SEPARATOR '|') AS meta_values
        FROM wp_posts post 
        LEFT JOIN wp_postmeta meta on meta.post_id = post.ID
        WHERE (post.post_status = 'publish' AND post.post_type = 'wpsl_stores')
        AND (meta.meta_key = 'wpsl_store_id' OR meta.meta_key = 'wpsl_tax_rate')
        GROUP BY post.post_title, post.post_name
        ORDER BY post.post_title ASC
    ";

    $locationResults = $wpdb->get_results($queryLocations, OBJECT);

    if (!empty($locationResults)) {

        foreach ($locationResults as $locationResult) {

            // Venu Data.
            $dates = array();
            $events = eo_get_events(array(
                'venue'          => $locationResult->venue,
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
            $meta['taxRate'] = !empty($meta['wpsl_tax_rate']) ? (float) $meta['wpsl_tax_rate'] : 6.8; // Default Tax Rate
            unset($meta['wpsl_store_id']);
            unset($meta['wpsl_tax_rate']);

            // Location
            $location = new stdClass();
            $location = (object) $meta;
            $location->title = $locationResult->title;
            $location->events = $dates;
            
            // Location to Locations
            if (!array_key_exists($location->title, $locations)) {
                $title = $locationResult->venue;
                $locations->$title = $location;
            }
        }
    }
    $results['locations'] = !empty($locations) ? $locations : false;;


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
    $discounts = array();

    // Limited Time Offer
    $limited = get_field('limited_time_offer', 'option');
    $discounts['limited'] = !empty($limited) ? $limited : '';

    // Promo Codes
    $promos = array();
    if (have_rows('promos', 'option')) {

        while (have_rows('promos', 'option')) {
            the_row();
            $promos[] = (object) array(
                'title' => get_sub_field('title'),
                'code'  => get_sub_field('code'),
                'price' => get_sub_field('price'),
            );
        }
    }
    $discounts['promos'] = !empty($promos) ? $promos : '';

    // Location Offers
    $locationOffers = new stdClass();
    if (have_rows('locations', 'option')) {

        while (have_rows('locations', 'option')) {
            the_row();
            $p = get_sub_field('location');

            if (!array_key_exists($p->post_name, $locationOffers)) {
                $locationOffers->{$p->post_name} = get_sub_field('price');
            }
        }
    }
    $discounts['locations'] = !empty($locationOffers) ? $locationOffers : '';


    $results['discounts'] = !empty($discounts) ? $discounts : false;


    // Response output in JSON Representation
    header("Content-Type: application/json");
    echo json_encode($results);