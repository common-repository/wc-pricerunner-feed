<?php
/**
 * PartnerAds Feed XML.
 *
 * @since 1.0.0
 */
class WC_PAF_XML {

   

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
        $settings = get_option( 'woocommerce_partnerads_settings' );
        $items_total = "99999";

        // Get the currency
        $currency = get_option( 'woocommerce_currency' );

        // Create a Feed.
        $xml = '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"></rss>';
				
        $rss = new WC_PAF_SimpleXML( $xml );

        // Add the channel.
        $channel = $rss->addChild( 'produkter' );
       
        // Create a new WP_Query.
        if($settings["include_all_products"] == "yes") {
                    
        $feed_query = new WP_Query(
            array(
                'post_type' => 'product',
                'post_status' => 'publish',
                //'ignore_sticky_posts' => 1,
                //'meta_key' => 'wc_paf_active',
                'posts_per_page' => $items_total
            )
        );
            
        } else {
     
        $feed_query = new WP_Query(
            array(
                'post_type' => 'product',
                'post_status' => 'publish',
                'ignore_sticky_posts' => 1,
                'meta_key' => 'wc_paf_active',
                'posts_per_page' => $items_total
            )
        );            
            
        
        }

        //Defaults
        $default_description = $settings['default_description'];
        $default_category = $settings['default_category'];
        $default_brand = $settings['default_brand'];
        $default_shipping = $settings['default_shipping'];

        // Starts the Loop.
        while ( $feed_query->have_posts() ) {
            $feed_query->the_post();

            // Gets the product data.
            $product = get_product( get_the_ID() );

            $item = $channel->addChild( 'produkt' );
            $options = get_post_meta( get_the_ID(), 'wc_paf', true );

            // Basic Product Information.
			$category = get_the_category(get_the_ID()); 
			$product_cats = wp_get_post_terms( get_the_ID(), 'product_cat' );
			$single_cat = array_shift( $product_cats );
			

			if(@$options['category'] == '' && $single_cat->name == '') {
			$item->addChild( 'kategorinavn', '')->addCData($default_category);
			} else if(@$options['category'] == '') {
             $item->addChild( 'kategorinavn', '')->addCData($single_cat->name);
		   } else {
			 $item->addChild( 'kategorinavn', '')->addCData( sanitize_text_field( @$options['category'] ) );
		   } 
		   
		     
			 $brand = sanitize_text_field( @$options['brand'] );
			 if($brand == '') { 
                 
               $item->addChild( 'brand', '')->addCData($default_brand);  
                 $brand = $default_brand; 
                 
             } else { 
                 
                 $item->addChild( 'brand', '')->addCData($default_category);  
                 $brand = $default_brand;      
                 
             } 
            
		    
            $item->addChild( 'produktid', get_the_ID());
            $item->addChild( 'produktnavn' )->addCData( sanitize_text_field( get_the_title() ) );
            
            
            if(@$options['description'] == '') {
			 $item->addChild( 'produktbeskrivelse' )->addCData( sanitize_text_field( $default_description ) );
		   } else {
			  $item->addChild( 'produktbeskrivelse' )->addCData( sanitize_text_field( @$options['description'] ) );
		   } 
		   
	
		   
            // Price.

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

			

            $thumb = get_post_thumbnail_id();
            if ( $thumb && $settings['allow_images_in_feed'] == 'yes' ) {
                $image_url = wp_get_attachment_image_src( $thumb, 'shop_single' );
                $item->addChild( 'billedurl', $image_url[0]);
            }

			
         
				$item->addChild( 'vareurl', get_permalink() );
				
				$availability = $product->stock_status;
				$item->addChild( 'lagerstatus', $availability );

					$stock = $product->stock;
					if($stock == '0') { $stock = " "; } else { $stock = $stock; }
					$item->addChild( 'lagerantal',  $stock );
					
					if(@$options['shipping_cost'] == '') { $shipping_cost = "0.00"; } else { $shipping_cost = @$options['shipping_cost']; }
						$item->addChild( 'fragtomk', sanitize_text_field($shipping_cost) );
         
        }

        wp_reset_postdata();

        // Filter the RSS.
        $rss = apply_filters( 'wc_partnerads_Feed_xml', $rss );

        // Format and print the XML.
        $dom = dom_import_simplexml( $rss )->ownerDocument;
        $dom->formatOutput = true;
        return $dom->saveXML();
    }
}
