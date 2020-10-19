<?php
/*
Plugin Name: ajax demo
*/
// enqueue scripts
function ajax_admin_enqueue_scripts() {
	// define script url
	$script_url = plugins_url( '/ajax-admin.js', __FILE__ );
	wp_enqueue_script( 'ajax-admin', $script_url, array( 'jquery' ) );
	// create nonce
	$nonce = wp_create_nonce( 'ajax_admin' );
	$script = array( 'nonce' => $nonce );
	// localize script
	wp_localize_script( 'ajax-admin', 'ajax_admin', $script );
}
add_action( 'admin_enqueue_scripts', 'ajax_admin_enqueue_scripts' );

// process ajax request
function ajax_admin_handler() {

	// check nonce
	check_ajax_referer( 'ajax_admin', 'nonce' );

	// check user
	if ( ! current_user_can( 'manage_options' ) ) return;

	// define the url
	$url = isset( $_POST['url'] ) ? esc_url_raw( $_POST['url'] ) : false;

	// make head request
	$response = wp_safe_remote_get( $url, array( 'method' => 'HEAD' ) );

	// get response headers
	$headers = wp_remote_retrieve_headers( $response );
	// output the results
	echo '<pre>';
	if ( ! empty( $headers ) ) {
		echo 'Response headers for: '. $url . "\n\n";
		print_r( $headers );
	} else {
		echo 'No results. Please check the URL and try again.';
	}
	echo '</pre>';
	// end processing
	wp_die();

}
// ajax hook for logged-in users: wp_ajax_{action}
add_action( 'wp_ajax_admin_hook', 'ajax_admin_handler' );
// display form and results
function ajax_admin_display_form() {

	?>

	<style>
		.ajax-form-wrap { width: 100%; overflow: hidden; margin: 0 0 20px 0; }
		.ajax-form { float: left; width: 400px; }
		.examples  { float: left; width: 200px; }
		pre {
			width: 95%; overflow: auto; margin: 20px 0; padding: 20px;
			color: #fff; background-color: #424242;
		}
	</style>

	<h3>enter something like https://google.com</h3>
	<div class="ajax-form-wrap">

		<form class="ajax-form" method="post">
			<p><label for="url">Enter any valid URL:</label></p>
			<p><input id="url" name="url" type="text" class="regular-text"></p>
			<input type="submit" value="Check Headers" class="button button-primary">
		</form>
	</div>

	<div class="ajax-response"></div>

<?php

}
// add top-level administrative menu
function ajax_admin_menu() {

	add_menu_page(
		'Ajax Demo: Admin Area',
		'Ajax Demo',
		'manage_options',
		'ajax-admin-demo',
		'ajax_admin_display_functionality_page',
		'dashicons-admin-generic',
		null
	);

}
add_action( 'admin_menu', 'ajax_admin_menu' );
// display the plugin settings page
function ajax_admin_display_functionality_page() {
	// check if user is allowed access
	if ( ! current_user_can( 'manage_options' ) ) return;
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<?php echo ajax_admin_display_form(); ?>
	</div>
<?php
}