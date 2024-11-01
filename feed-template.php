<?php
/**
 * Template Name: PartnerAds Feed
 */ 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Sets the charset.
@ob_clean();
header('Content-type: application/xml');

// Helpers classes.
require_once WOO_PRF_PATH . 'includes/class-wc-prf-simplexml.php';
require_once WOO_PRF_PATH . 'includes/class-wc-prf-xml.php';

$feed = new WC_PRF_XML;
return $feed->render();
