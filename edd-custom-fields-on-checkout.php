<?php
/*
Plugin Name: Easy Digital Downloads - Custom Fields On Checkout
Plugin URI: https://github.com/dipakcg/edd-custom-fields-on-checkout
Description: Adds 'Website' and 'Extra note' custom fields to the checkout screen in Easy Digital Downloads.
Version: 1.1
Author: Dipak C. Gajjar
Author URI: https://dipakgajjar.com
License: Licence: GPLv2
*/

// Output custom field HTML on checkout
function dcg_edd_custom_checkout_fields() {
?>
    <p>
        <label class="edd-label" for="edd-website"><?php _e('Website', 'edd'); ?> <span class="edd-required-indicator">*</span> </label>
        <span class="edd-description">Website, you're ordering service for.</span>
        <input class="edd-input" type="text" name="edd_website" id="edd-website" value="" />
    </p>
    <p>
        <label class="edd-label" for="edd-message"><?php _e('Extra note', 'edd'); ?></label>
        <span class="edd-description">Anything you feel we need to know.</span>
        <textarea class="edd-input" name="edd_customer_message" id="edd-customer-message"></textarea>
    </p>
<?php
}
add_action( 'edd_purchase_form_user_info_fields', 'dcg_edd_custom_checkout_fields' );

// Check for errors with custom fields
function dcg_edd_validate_custom_fields( $valid_data, $data ) {
    if ( empty( $data['edd_website'] ) ) {
        edd_set_error( 'invalid_website', 'Please enter a valid website url.' );
    }
}
add_action( 'edd_checkout_error_checks', 'dcg_edd_validate_custom_fields', 10, 2 );

// Store custom field data in the payment meta
function dcg_edd_store_custom_fields( $payment_meta ) {
    $payment_meta['website'] = isset($_POST['edd_website']) ? $_POST['edd_website'] : '';
    $payment_meta['customer_message'] = isset($_POST['edd_customer_message']) ? $_POST['edd_customer_message'] : '';
    return $payment_meta;
}
add_filter('edd_payment_meta', 'dcg_edd_store_custom_fields');

// Show custom fields in the "View Order Details"
function dcg_edd_view_order_details($payment_meta, $user_info) {
	$website = isset( $payment_meta['website'] ) ? $payment_meta['website'] : 'None';
	?>
	 <div class="column-container">
    	<div class="column">
    		<strong><?php echo 'Website: '; ?></strong>
            <input type="text" name="edd_website" value="<?php esc_attr_e( $website ); ?>" class="medium-text" />
            <p class="description"><?php _e( 'Website, service ordered for.', 'edd' ); ?></p>
    	</div>
    </div>
	<?php
}
add_action('edd_payment_personal_details_list', 'dcg_edd_view_order_details', 10, 2);

// Save custom field data when it's modified via view order details
function dcg_edd_updated_edited_purchase( $payment_id ) {
    $payment_meta = edd_get_payment_meta( $payment_id );
    $payment_meta['website'] = isset( $_POST['edd_website'] ) ? $_POST['edd_website'] : false;
    update_post_meta( $payment_id, '_edd_payment_meta', $payment_meta );
}
add_action( 'edd_updated_edited_purchase', 'dcg_edd_updated_edited_purchase' );

// Add a {website} tag to use in either the purchase receipt email or admin notification emails
if ( function_exists( 'edd_add_email_tag' ) ) {
    edd_add_email_tag( 'website', 'Website, service ordered for.', 'dcg_edd_email_tag_website' );
    edd_add_email_tag( 'customer_message', 'Customer message submitted during checkout.', 'dcg_edd_email_tag_customer_message' );
}
// {website} email tag
function dcg_edd_email_tag_website( $payment_id ) {
    $payment_data = edd_get_payment_meta( $payment_id );
    return $payment_data['website'];
}
// {customer_message} email tag
function dcg_edd_email_tag_customer_message( $payment_id ) {
    $payment_data = edd_get_payment_meta( $payment_id );
    return $payment_data['customer_message'];
}
?>
