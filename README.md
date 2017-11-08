# Profileplan-Webforms
ProfilePlan.net - Webforms ~ Join Now, Discovery Session, Meet with Coach


## Installation

#### Yarn Install - Installs Node Dependencies.

`yarn install`


#### Composer Install - Installs Composer Package Dependencies.

`composer install`


#### Yarn Commands

Starts BrowserSync with local js files being rendered. These files are built in the `js/local` directory.

`yarn starts`


Builds out production minified version of the js script. This file is located in the `js/build` directory.

`yarn build`


#### INIT.

##### init.php

This file does all the heavy lifting, you'll need to include this from the `functions.php` file. Functionality:

- Creates Webfrom Shortcode
```
Create Shortcode webform
Use the shortcode: [webform form="join-now|discovery-session|meet-with-coach"]
```
- Include/Exclude Assets on pages
- Add Preload Script to Form Pages
- Add Prefetch Script to all other pages as low priority to cache in users session for quick load.
- Creates Tax Rate Tables for Store CPTs
- Build out Discounts Page in Admin
- Creats Discounts Transients on Discounts Publish
- Includes ajax calls for Wordpress use
- Create a Section ACF Box for the CTAs on Home, Planing & Prices, Profile Way Pages.


#### API AJAX Calls.

The API calls will not work because this isn't connected to a server. The API AJax calls can be found in the `/webforms/ajax-***`.

##### ajax-authorize.php

This talks to the Authorize.net using the PHP Authorize Composer API Package found in the vendor folder after `composer install` The commented out lines are for the Production Authorize.net account, the ones being used are for the Sandbox Authorize.net Account.

```
$merchantAuthentication->setName('6ENM26hn24'); <- Sandbox
$merchantAuthentication->setTransactionKey('247W8J8Yv3YJv6m2'); <- Sandbox
$response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX); <- Sandbox
    

// $merchantAuthentication->setName('6q7ApZy9rt'); <- Production
// $merchantAuthentication->setTransactionKey('9qEf737S6y579EJH'); <- Production
// $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION); <- Production
```    

##### ajax-data.php

This brings in the data from the live site. The data:

- Store Locations + Events: SQL Query grab from database
```
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
````
- Partners: SQL Query grab from database: 
```
SELECT DISTINCT post.post_title AS title
        FROM wp_posts post
        WHERE post.post_status = 'publish' 
        AND post.post_type = 'partner'
        ORDER BY post.post_title ASC
```
- Promo Codes: Uses transient `discounts_promos` from options table. `get_transient` Method.*
- Limited Time Offers: Uses transient `discounts_limited_time_offer` from options table. `get_transient` Method.*
- Location Offers: Uses transient `discounts_location_offers` from options table. `get_transient` Method.*

* Note: If the transient table doens't exist in DB, it'll grab it using the ACF `get_field` way which takes longer. The way these Transients are created is through the Administrator -> Discounts `Publish`


##### ajax-mailer.php

This sends emails out to the users, there are 4 different emails sent:

- Meet with Coach
- Discovery Session
- Join Now: Create Account
- Join Now: Payment Receipt

###### ajax-wordpressdb.php

This sanitizes the form fields, creates/updates user records as they fill out the forms.


#### Pages & Partials

##### page-webform.php

Created a `page-webform.php`, this pretty much removes everythinge except for the form itself. This is to reduce all the clutter and unnecessary code. Used for Join Now page, with the `[webforms type="join-now"]` shortcode.

The Discovery Session and Meet with Coach pages are on the `full width template`.

##### partial-cta.php

This is for the CTA Section on the Home, Price & Planing, Profile Way Pages. If the ACF Section on the backened of those pages are setup they will display the `partial-cta` within the page.


#### Assets

The production built js `scripts.js`, `styles.css`, `imgs`, and `styles-cta.css` all live here. FYI. Once you build out the JS script(using `yarn build`) you'll move it from the build folder into this directory, overwritting the `scripts.js`.


#### For Further Information:

CONTACT: Jason Comes - `jasonwcomes@gmail.com` or `605-728-2868`


