<?php

/**
 * Profile Form - AJAX Action & DB Entry w/results
 *
 */
function wordpressDbTransaction() 
{   
    // Check Post
    if (empty($_POST)) {
        return false;
    }

    // Security
    check_ajax_referer('webform-script-nonce', '_security_nonce');

    // Variables
    global $wpdb;
    $dbName              = 'webform_members';
    $memberID            = !empty($_POST['member_id']) ? $_POST['member_id'] : 0;
    $fields              = (array) $_POST;

    // Remove unused keys
    foreach (array('action', '_security_nonce', 'member_id') as $key) {
       unset($fields[$key]);
    }

    // Sanitized Fields.
    $sanitizedFields = array();
    $allowedFields = array('form', 'first_name', 'last_name', 'email', 'phone', 'location', 'event', 'address', 'city', 'state', 'zip', 'employer', 'membership', 'total', 'payment_status', 'authorize_id', 'attempt_failure');

    // Sanitize Fields
    foreach ($fields as $field => $value) {

        // Don't allow any unknown fields.
        if (empty($value) || !in_array($field, $allowedFields)) {
            continue;
        }

        // Form Sanitize
        if ($field === 'form') {
            $sanitizedFields[$field] = sanitize_title($value);
            continue;
        }

        // Email Sanitize
        if ($field === 'email') {
            $sanitizedFields[$field] = sanitize_email($value);
            continue;
        }

        // Sanitize the Rest.
        $sanitizedFields[$field] = sanitize_text_field($value);
    }

    // Add Browser & Timestamp
    $sanitizedFields['browser']   = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $sanitizedFields['timestamp'] = gmdate("Y-m-d\TH:i:s\Z", current_time('timestamp'));
    
    // Database Update/Insertion
    if (!empty($memberID)) {

        // Update.
        $result = $wpdb->update(
            $dbName, 
            $sanitizedFields,
            array('ID' => $memberID)
        );

    } else {

        // Insert.
        $result = $wpdb->insert(
            $dbName, 
            $sanitizedFields
        );

        // Update $member_id with last row inserted.
        $memberID = $result !== false ? $wpdb->insert_id : 0;

    }

    // Returns the JSON Representation of a value
    $results = array(
        'member_id' => (int) $memberID,
        'success'   => (int) $result
    );
 
    // Response output in JSON Representation
    header("Content-Type: application/json");
    echo json_encode($results);

    // End AJAX Request.
    wp_die();
}
add_action('wp_ajax_wordpress_db_transaction', 'wordpressDbTransaction');
add_action('wp_ajax_nopriv_wordpress_db_transaction', 'wordpressDbTransaction');
