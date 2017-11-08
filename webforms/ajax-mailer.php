<?php

/**
 * MAILER
 * Send different mail messages based on form.
 */
function webformsMailer() 
{
    // Check Post
    if (empty($_POST) || empty($_POST['email'])) {
        return false;
    }

    // Security
    check_ajax_referer('webform-script-nonce', '_security_nonce');

    // Variables
    extract($_POST);

    // To
    $to = $email;

    // Set content-type header
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= "Content-type:text/html; charset=UTF-8" . "\r\n";

    // Additional headers
    $headers .= 'From: Profile by Sanford <no-reply@profileplan.net>' . "\r\n";
    $headers .= 'Cc: Daniel.Okeefe@sanfordhealth.org' . "\r\n";
    $headers .= 'Bcc: jasonwcomes@gmail.com' . "\r\n";

    // Form types
    switch ($form) {

        case 'discovery-session':

            $subject = "See You Soon";
            $mailContent = '<h1>Welcome to Profile!</h1> <p>Congratulations on taking your first step toward healthy lifestyle change. We have a seat reserved for you at a Discovery Session' . (!empty($event) ? ' on ' . $event : '') . ', giving you an opportunity to learn how Profile will help you meet your individual nutrition, activity and lifestyle goals. Let’s do this together!</p><p>If you have any questions, contact our customer service department by calling 1-877-373-6069 or sending an email to <a href="mailto:info@profileplan.net">info@profileplan.net</a>. Our customer service hours are:</p><p>Monday through Friday: 7 am to 7 pm (CST) <br />Saturday: 8am to 5 pm (CST)</p><p>Thank you for choosing Profile! We’ll see you soon!</p>';

        break;

        case 'meet-with-coach':

            $subject = "We’ll Talk Soon!";
            $mailContent = '<h1>Welcome to Profile!</h1> <p>Congratulations on taking your first step toward healthy lifestyle change. Certified Profile Coach will contact you soon to schedule a free, one-on-one coaching session to put you on a path to meeting your personal nutrition, activity and lifestyle goals. Let’s do this together!</p></p>If you have any questions, contact our customer service department by calling 1-877- 373-6069 or sending an email to <a href="mailto:info@profileplan.net">info@profileplan.net</a>. Our customer service hours are:</p><p>Monday through Friday: 7 am to 7 pm (CST) <br />Saturday: 8am to 5 pm (CST)</p><p>Thank you for choosing Profile. We’ll talk soon!</p>';

        break;

        case 'join-now':
            if (empty($membership_name)) {

                // Create Account Completion
                $subject = "Welcome to Profile by Sanford!";
                $mailContent = '<h1>Welcome to Profile!</h1> <p>Congratulations on taking your first step toward healthy lifestyle change. A member of Team Profile will be in touch with you shortly to schedule a time for you to visit with a Certified Profile Coach. We’ll set you up with everything you need to meet your personal nutrition, activity and lifestyle goals. After you officially become a Profile member, we’ll send you another email to give you access to your online account.</p><p>If you have any questions, contact our customer service department by calling 1-877-373-6069 or sending an email to <a href="mailto:info@profileplan.net">info@profileplan.net</a>. Our customer service hours are:</p><p>Monday through Friday: 7 am to 7 pm (CST) <br />Saturday: 8am to 5 pm (CST)</p><p>Thank you for choosing Profile! Let’s do this together!</p>';

            } elseif(empty($authorize_id)) {

                // Payment Failure
                $subject = "Your Profile by Sanford!";
                $mailContent = '<h1>We Apologize!</h1> <p>The transation was unsuccesful, please contact our customer service department either by calling 1-877-373-6069 or sending an email to <a href="mailto:info@profileplan.net">info@profileplan.net</a>. Our customer service department hours are:</p><p>Monday through Friday: 7am to 7 pm (CST)<br />Saturday: 8 am to 5 pm (CST)</p>
                <h5>Information Reference</h5>
                Invoice/Member ID: ' . stripslashes($member_id) . ' <br />
                Description: ' . $membership_name . ' Membership - ' . $membership . ' <br />
                Price: $' . $payment_price . ' <br />
                Tax: (' . $payment_taxRate . '%) $' . $payment_taxTotal . ' <br />
                Total: $' . $payment_total . ' <br /><br />
                ' . stripslashes($first_name) . ' ' . stripslashes($last_name) . ' <br />
                ' . stripslashes($address) . ' <br />
                ' . stripslashes($city) . ', ' . $state . ' ' . $zip . ' <br />
                ';

            } else {

                // Payment Completion
                $subject = "Your Profile by Sanford Receipt";
                $mailContent = '<h1>Welcome to Profile!</h1> <p>Congratulations on taking your first step toward healthy lifestyle change. A member of Team Profile will be in touch soon to schedule your first coaching appointment and set you up with everything you need to meet your personal nutrition, activity and lifestyle goals. Let’s do this together.</p>
                <p>If you have any questions, contact our customer service department by calling 1-877-373-6069 or sending an email to <a href="mailto:info@profileplan.net">info@profileplan.net</a>. Our customer service hours are:</p>
                <p>Monday through Friday: 7 am to 7 pm (CST) <br />Saturday: 8am to 5 pm (CST)</p>
                <p>Thank you for choosing Profile! Your receipt is detailed below. Please keep a copy of this email for your records</p>
                <h5>Profile by Sanford Receipt</h5>
                Description: ' . $membership_name . ' Membership - ' . $membership . ' <br />
                Price: $' . $payment_price . ' <br />
                Tax: (' . $payment_taxRate . '%) $' . $payment_taxTotal . ' <br />
                Total: $' . $payment_total . ' <br />

                <h5>Billing Information</h5>
                ' . stripslashes($first_name) . ' ' . stripslashes($last_name) . ' <br />
                ' . stripslashes($address) . ' <br />
                ' . stripslashes($city) . ', ' . $state . ' ' . $zip . ' <br />
                United States <br />
                ' . $email . ' <br />

                <h5>Payment Information</h5>
                Transaction ID: ' . $authorize_id . ' <br />
                Authorization Code: ' . $authorize_code . ' <br />
                Payment Method: ' . $authorize_method;

            }
        break;

        default:
            return;
        break;
    }

    $content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
                    <html xmlns="http://www.w3.org/1999/xhtml">
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <meta name="viewport" content="width=device-width"/>
                        <style>


                    /* Client-specific Styles & Reset */

                    #outlook a { 
                      padding:0; 
                    } 

                    body{ 
                      width:100% !important; 
                      min-width: 100%;
                      -webkit-text-size-adjust:100%; 
                      -ms-text-size-adjust:100%; 
                      margin:0; 
                      padding:0;
                    }

                    .ExternalClass { 
                      width:100%;
                    } 

                    .ExternalClass, 
                    .ExternalClass p, 
                    .ExternalClass span, 
                    .ExternalClass font, 
                    .ExternalClass td, 
                    .ExternalClass div { 
                      line-height: 100%; 
                    } 

                    #backgroundTable { 
                      margin:0; 
                      padding:0; 
                      width:100% !important; 
                      line-height: 100% !important; 
                    }

                    img { 
                      outline:none; 
                      text-decoration:none; 
                      -ms-interpolation-mode: bicubic;
                      width: auto;
                      max-width: 100%; 
                      float: left; 
                      clear: both; 
                      display: block;
                    }

                    center {
                      width: 100%;
                      min-width: 580px;
                    }

                    a img { 
                      border: none;
                    }

                    p {
                      margin: 0 0 0 20px;
                    }

                    table {
                      border-spacing: 0;
                      border-collapse: collapse;
                    }

                    td { 
                      word-break: break-word;
                      -webkit-hyphens: auto;
                      -moz-hyphens: auto;
                      hyphens: auto;
                      border-collapse: collapse !important; 
                    }

                    table, tr, td {
                      padding: 0;
                      vertical-align: top;
                      text-align: left;
                    }

                    hr {
                      color: #d9d9d9; 
                      background-color: #d9d9d9; 
                      height: 1px; 
                      border: none;
                    }

                    /* Responsive Grid */

                    table.body {
                      background-color: #ea5915;
                      height: 100%;
                      width: 100%;
                    }

                    table.container {
                      border: 25px solid #FFFFFF;
                      background-color: #FFFFFF;
                      width: 580px;
                      margin: 40px auto;
                      text-align: inherit;
                    }

                    table.row { 
                      padding: 0px; 
                      width: 100%;
                      position: relative;
                    }

                    table.container table.row {
                      display: block;
                    }

                    td.wrapper {
                      padding: 10px 20px 0px 0px;
                      position: relative;
                    }

                    table.columns,
                    table.column {
                      margin: 0 auto;
                    }

                    table.columns td,
                    table.column td {
                      padding: 0px 0px 10px; 
                    }

                    table.row td.last,
                    table.container td.last {
                      padding-right: 0px;
                    }

                    table.twelve { width: 580px; }

                    table.twelve center { min-width: 580px; }

                    .body .columns td.twelve,
                    .body .column td.twelve { width: 100%; }

                    td.expander {
                      visibility: hidden;
                      width: 0px;
                      padding: 0 !important;
                    }

                    table.columns .text-pad,
                    table.column .text-pad {
                      padding-left: 10px;
                      padding-right: 10px;
                    }

                    table.columns .left-text-pad,
                    table.columns .text-pad-left,
                    table.column .left-text-pad,
                    table.column .text-pad-left {
                      padding-left: 10px;
                    }

                    table.columns .right-text-pad,
                    table.columns .text-pad-right,
                    table.column .right-text-pad,
                    table.column .text-pad-right {
                      padding-right: 10px;
                    }

                    /* Alignment & Visibility Classes */

                    table.center, td.center {
                      text-align: center;
                    }

                    h1.center,
                    h2.center,
                    h3.center,
                    h4.center,
                    h5.center,
                    h6.center {
                      text-align: center;
                    }

                    span.center {
                      display: block;
                      width: 100%;
                      text-align: center;
                    }

                    img.center {
                      margin: 0 auto;
                      float: none;
                    }

                    .show-for-small,
                    .hide-for-desktop {
                      display: none;
                    }

                    /* Typography */

                    body, table.body, h1, h2, h3, h4, h5, h6, p, td, span { 
                      color: #000000;
                      font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
                      font-weight: normal; 
                      padding:0; 
                      margin: 0;
                      text-align: left; 
                      line-height: 1.3;
                    }

                    h1, h2, h3, h4, h5, h6 {
                      word-break: normal;
                    }

                    h1 {
                        margin: 0 0 25px;
                        font-size: 22px;
                        text-transform: uppercase;
                        font-weight: bold;
                    }

                    h5 {
                        margin: 25px 0 0;
                        font-size: 14px;
                        text-transform: uppercase;
                        font-weight: bold;
                    }
                    body, table.body, p, td, span {font-size: 15px; line-height:1.45;}

                    p.lead, p.lede, p.leed {
                      font-size: 18px;
                      line-height:21px;
                    }

                    p { 
                      margin-bottom: 20px;
                    }

                    small {
                      font-size: 10px;
                    }

                    a {
                      color: #de5811 !important; 
                      text-decoration: none;
                    }

                    a:hover { 
                      color: #de5811 !important;
                    }

                    a:active { 
                      color: #de5811 !important;
                    }

                    a:visited { 
                      color: #de5811 !important;
                    }

                    .footer {
                      display: block;
                      margin: 50px 0 0;
                    }

                    /* Outlook First */

                    body.outlook p {
                      display: inline !important;
                    }

                    /*  Media Queries */

                    @media only screen and (max-width: 600px) {

                      table[class="body"] img {
                        width: auto !important;
                        height: auto !important;
                      }

                      table[class="body"] center {
                        min-width: 0 !important;
                      }

                      table[class="body"] .container {
                        width: 95% !important;
                      }

                      table[class="body"] .row {
                        width: 100% !important;
                        display: block !important;
                      }

                      table[class="body"] .wrapper {
                        display: block !important;
                        padding-right: 0 !important;
                      }

                      table[class="body"] .columns,
                      table[class="body"] .column {
                        table-layout: fixed !important;
                        float: none !important;
                        width: 100% !important;
                        padding-right: 0px !important;
                        padding-left: 0px !important;
                        display: block !important;
                      }

                      table[class="body"] .wrapper.first .columns,
                      table[class="body"] .wrapper.first .column {
                        display: table !important;
                      }

                      table[class="body"] table.columns td,
                      table[class="body"] table.column td {
                        width: 100% !important;
                      }

                      table[class="body"] .columns td.twelve,
                      table[class="body"] .column td.twelve { width: 100% !important; }

                      table[class="body"] table.columns td.expander {
                        width: 1px !important;
                      }

                      table[class="body"] .right-text-pad,
                      table[class="body"] .text-pad-right {
                        padding-left: 10px !important;
                      }

                      table[class="body"] .left-text-pad,
                      table[class="body"] .text-pad-left {
                        padding-right: 10px !important;
                      }

                      table[class="body"] .hide-for-small,
                      table[class="body"] .show-for-desktop {
                        display: none !important;
                      }

                      table[class="body"] .show-for-small,
                      table[class="body"] .hide-for-desktop {
                        display: inherit !important;
                      }
                    }

                    @media only screen and (max-width: 600px) {

                      table[class="body"] .right-text-pad {
                        padding-left: 10px !important;
                      }

                      table[class="body"] .left-text-pad {
                        padding-right: 10px !important;
                      }
                    }

                    </style>
                    </head>
                    <body>
                        <table class="body">
                            <tr>
                                <td class="center" align="center" valign="top">
                                <center>

                                  <table class="container">
                                    <tr>
                                      <td>

                                        <table class="row">
                                          <tr>
                                            <td class="wrapper last">

                                              <table class="twelve columns">
                                                <tr>
                                                  <td>
                                                    ' . $mailContent . '

                                                    <small class="footer">This no-reply email was sent by Profile by Sanford, 101 S. Reid St, Suite 202  Sioux Falls, SD 57103</small>
                                                  </td>
                                                  <td class="expander"></td>
                                                </tr>
                                              </table>

                                            </td>
                                          </tr>
                                        </table>

                                      <!-- container end below -->
                                      </td>
                                    </tr>
                                  </table>

                                </center>
                                </td>
                            </tr>
                        </table>
                    </body>
                    </html>';

    // Mail Form.
    if (mail($to, $subject, $content, $headers)) {
        echo 'Mailed';
    } else {
        echo "not Mailed";
    };

    // End AJAX Request.
    wp_die();
}
add_action('wp_ajax_webform_mailer_transaction', 'webformsMailer');
add_action('wp_ajax_nopriv_webform_mailer_transaction', 'webformsMailer');

