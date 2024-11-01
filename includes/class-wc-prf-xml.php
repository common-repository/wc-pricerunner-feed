<?php
/**
 * PartnerAds Feed XML.
 *
 * @since 1.0.0
 */
class WC_PRF_XML {

   

    /**
     * Build the tax.
     *
     * @param  string $values Tax in string format.
     *
     * @return array          Tax in array format.
     */
    protected function build_tax( $values ) {
        $tax = array();

        $values = explode( ',', $values );

        foreach ( $values as $value ) {
            $tax[] = explode( ':', $value );
        }

        return $tax;
    } 

    /**
     * Fix date.
     *
     * @param  string $from From date.
     * @param  string $to   To date.
     *
     * @return string       Fixed date.
     */
    protected function fix_date( $from, $to ) {
        return date( 'Y-m-d', $from ) . 'T00:00-0000/' . date( 'Y-m-d', $to ) . 'T24:00-0000';
    }

    /**
     * Render the XML.
     *
     * @return string XML/RSS.
     */
    public function render() {
        // Settings.
        $settings = get_option( 'woocommerce_pricerunner_settings' );
        $items_total = "99999";

        // Get the currency
        $currency = get_option( 'woocommerce_currency' );

        // Create a Feed.
        $xml = '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"></rss>';
				
        $rss = new WC_PRF_SimpleXML( $xml );

        // Add the channel.
        $channel = $rss->addChild( 'products' );
       
        // Create a new WP_Query.
        $feed_query = new WP_Query(
            array(
                'post_type' => 'product',
                'post_status' => 'publish',
                //'ignore_sticky_posts' => 1,
                'meta_key' => 'wc_prf_active',
                'posts_per_page' => $items_total
            )
        );

        // Starts the Loop.
        while ( $feed_query->have_posts() ) {
            $feed_query->the_post();

       
            // Gets the product data.
            $product = get_product( get_the_ID() );
            $item = $channel->addChild( 'product' );
            $options = get_post_meta( get_the_ID(), 'wc_prf', true );

            
            //Category
            			$category = get_the_category(get_the_ID()); 
			$product_cats = wp_get_post_terms( get_the_ID(), 'product_cat' );
			$single_cat = array_shift( $product_cats );
			

            if(@$options['category'] == '' && $single_cat->name == '') {
			$item->addChild( 'categoryn', '')->addCData("default");
			} else if(@$options['category'] == '') {
             $item->addChild( 'category', '')->addCData($single_cat->name);
		   } else {
			 $item->addChild( 'category', '')->addCData( sanitize_text_field( @$options['category'] ) );
		   } 
            //Name
             $item->addChild( 'productName' )->addCData( sanitize_text_field( get_the_title() ) );
            
            //SKU
            $item->addChild( 'sku', get_the_ID());
                
            //Price
              if ( $product->is_type( 'variable' ) ) {
                if ( $product->is_on_sale() ) {
				$item->addChild( 'pris', $product->min_variation_price);
                    $item->addChild( 'gammelpris', $product->min_variation_regular_price);
                    
                } else {
                    $item->addChild( 'pris', $product->get_price());
                }
            } else {
                if ( $product->is_on_sale() ) {
					$item->addChild( 'pris', $product->sale_price);
                    $item->addChild( 'gammelpris', $product->regular_price);
                    
                } else {
                    $item->addChild( 'pris', $product->get_price());
                }
            }
            
            
            if(@$options['shipping_cost'] == '') { $shipping_cost = "0.00"; } else { $shipping_cost = @$options['shipping_cost']; }
			$item->addChild( 'shippingCost', sanitize_text_field($shipping_cost) );
            
            //	Product URL
            $item->addChild( 'productUrl', get_permalink() );
				
            //Manufacturer SKU
             $item->addChild( 'manufactureSku', @$options['manufacturer_sku'] );
            //Manufacturer
            $item->addChild( 'manufacture', @$options['manufacturer'] );
            //	EAN or UPC
            $item->addChild( 'manufactureSku', @$options['ean_or_upc'] );
            
            
      
         
        }
        


        wp_reset_postdata();

        // Filter the RSS.
        $rss = apply_filters( 'wc_pricerunner_feed_xml', $rss );

        // Format and print the XML.
       $dom = dom_import_simplexml( $rss )->ownerDocument; 
      $dom->formatOutput = true;
   
       echo $dom->saveXML();

    }
}
