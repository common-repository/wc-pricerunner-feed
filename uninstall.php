<?php
// Only allow wordpress uninstall
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) || ! WP_UNINSTALL_PLUGIN || dirname( WP_UNINSTALL_PLUGIN ) != dirname( plugin_basename( __FILE__ ) ) ) {

    status_header( 404 );
    exit; 
}

// Delete feed page.
$feed_slug = sanitize_title( _x( 'pricerunner-feed', 'page slug', 'wcprf' ) );
$feed_page = get_page_by_path( $feed_slug );
if ( $feed_page ) {
    wp_delete_post( $feed_page->ID, true );
}
