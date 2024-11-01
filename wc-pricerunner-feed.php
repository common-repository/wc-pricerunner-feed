<?php
/**
 * Plugin Name: WC PriceRunner Feed
 * Plugin URI: http://dicm.dk/
 * Description: Creates a feed to integrate with your PriceRunner campaign
 * Author: Kim Vinberg
 * Author URI: http://dicm.dk/
 * Version: 1.0.2       
 * Text Domain: wcprf
 * Domain Path: /languages/ 
 */

define( 'WOO_PRF_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOO_PRF_URL', plugin_dir_url( __FILE__ ) );

/**
 * WooCommerce fallback notice.
 */
if(!function_exists("wcprf_woocommerce_fallback_notice")) { 
    function wcprf_woocommerce_fallback_notice() {
        echo '<div class="error"><p>' . sprintf( __( 'WooCommerce PriceRunner feed depends on the last version of %s to  work!', 'wcprf' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">' . __( 'WooCommerce', 'wcprf'    ) . '</a>' ) . '</p></div>';
    }
}
/**
 * Load functions.
 */
function wcprf_gateway_load() {

    /**
     * Load textdomain.
     */
    load_plugin_textdomain( 'wcprf', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

    // Checks with WooCommerce is installed.
    if ( ! class_exists( 'WC_Integration' ) ) {
        add_action( 'admin_notices', 'wcprf_woocommerce_fallback_notice' );

        return;
    }

    /**
     * Add a new integration to WooCommerce.
     *
     * @param  array $integrations WooCommerce integrations.
     *
     * @return array               Integrations with WooCommerce PriceRunner Feed.
     */
    function wcprf_add_integration( $integrations ) {
        $integrations[] = 'WC_PRF_FEED';

        return $integrations;
    }

    add_filter( 'woocommerce_integrations', 'wcprf_add_integration' );

    // Include integration class.
    require_once WOO_PRF_PATH . 'includes/class-wc-feed.php';
}

add_action( 'plugins_loaded', 'wcprf_gateway_load', 0 );


/**
 * Create feed page on plugin install.
 */
function wcprf_create_page() {
    $slug = sanitize_title( _x( 'pricerunner-feed', 'page slug', 'wcprf' ) );

    if ( ! get_page_by_path( $slug ) ) {
        $page = array(
            'post_title'     => _x( 'PriceRunner Feed', 'page name', 'wcprf' ),
            'post_name'      => $slug,
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
            'post_content'   => '',
        );

        wp_insert_post( $page );
    }
}

register_activation_hook( __FILE__, 'wcprf_create_page' );
