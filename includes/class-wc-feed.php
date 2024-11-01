<?php
/**
 * Ultimate PriceRunner Feed class.
 *
 * @since 1.0.0
 */
class WC_PRF_Feed extends WC_Integration {

    /**
     * Init and hook in the integration.
     *
     * @return void
     */
    public function __construct() {
        $this->id                 = 'pricerunner';
        $this->method_title       = __( 'PriceRunner', 'wcprf' );
        $this->method_description = __( 'Creates a Feed to integrate with your Partnerads.', 'wcprf' );

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables.
        $this->page         = apply_filters( 'wc_pricerunner_Feed_page', sanitize_title( _x( 'pricerunner-feed', 'page slug', 'wcprf' ) ) );
        $this->items_total  = $this->get_option( 'items_total' );
        $this->category     = $this->get_option( 'category' );
        $this->product_type = $this->get_option( 'product_type' );

        // Save integration options.
        add_action( 'woocommerce_update_options_integration_pricerunner', array( $this, 'process_admin_options' ) );

        // Add write panel tab.
        add_action( 'woocommerce_product_write_panel_tabs', array( &$this, 'add_tab' ) );

        // Create write panel.
        add_action( 'woocommerce_product_write_panels', array( &$this, 'tab_view' ) );

        // Save meta.
        add_action( 'woocommerce_process_product_meta', array( &$this, 'save_tab_options' ) );

        // Add page template.
        add_filter( 'page_template', array( &$this, 'feed_template' ) );

        // Load scripts.
        add_action( 'admin_enqueue_scripts', array( &$this, 'scripts' ) );
    }

    /**
     * Initialise Integration Settings Form Fields.
     *
     * @return void
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'items_total' => array(
                'title'       => __( 'Number of items in the feed', 'wcprf' ),
                'type'        => 'text',
                'description' => __( 'Total number of items that will be displayed in the feed', 'wcprf' ),
                'desc_tip'    => true,
                'default'     => '100'
            ),
            'defaults' => array(
                'title'       => __( 'Default Options', 'wcprf' ),
                'type'        => 'title',
                'description' => ''
            ),
            'category' => array(
                'title'       => __( 'Default Category', 'wcprf' ),
                'type'        => 'textarea',
                'default'     => ''
            ),
           
        );
    }

    /**
     * Load metabox scripts.
     *
     * @return void
     */
    public function scripts() {
        $screen = get_current_screen();
        if ( 'product' === $screen->id ) {
            
            wp_register_style( 'prf_style.css', WOO_PRF_URL . '/assets/css/prf_style.css', false, '1.0.0' );
            wp_enqueue_style( 'prf_style.css' );
            
        }
    }

    /**
     * Add new tab.
     */
    public function add_tab() {
        echo '<li class="advanced_tab pricerunner_options wc_prf_tab"><a href="#wc_prf_tab" class="prf_link">' . __( 'PriceRunner', 'wcprf' ) . '</a></li>';
    }

    /**
     * Tab content.
     */
    public function tab_view() {
        global $post;

        $options = get_post_meta( $post->ID, 'wc_prf', true );
        $active = get_post_meta( $post->ID, 'wc_prf_active', true );
        ?>
        <div id="wc_prf_tab" class="panel woocommerce_options_panel">
            <div id="wc_prf_tab_active" class="options_group">
                <?php
                    woocommerce_wp_checkbox(
                        array(
                            'id' => 'wc_prf_active',
                            'label' => __( 'Include in Product Feed?', 'wcprf' ),
                            'description' => __( 'Enable this option to include in this product in your Product Feed', 'wcprf' ),
                            'value' => isset( $active ) ? $active : ''
                        )
                    );
                ?>
                
            </div>
            <div id="wc_prf_items">
                <div id="wc_prf_tab_basic" class="options_group">
                    <p class="form-field"><strong><?php _e( 'Required Product Information', 'wcprf' ); ?></strong></p>
                    <?php
         // Category
                        woocommerce_wp_textarea_input(
                            array(
                                'id' => 'wc_prf[category]',
                                'label' => __( 'Category', 'wcprf' ),
                                'description' => __( '<b>Example:</b> Electronic > Digital Cameras > Accessories', 'wcprf' ),
                                'value' => isset( $options['category'] ) ? $options['category'] : ''
                            )
                        );


	   // Shipping cost.
                        woocommerce_wp_text_input( array(
                            'id' => 'wc_prf[shipping_cost]',
                            'label' => __( 'Shipping cost', 'wcprf' ),
                            'description' => __( 'Enter shipping price (Example: 0.00)', 'wcprf' ),
                            'value' => isset( $options['shipping_cost'] ) ? $options['shipping_cost'] : ''
                        ) );

        
        
	   // Manufacturer SKU
                        woocommerce_wp_text_input( array(
                            'id' => 'wc_prf[manufacturer_sku]',
                            'label' => __( 'Manufacturer SKU', 'wcprf' ),
                            'description' => __( '<b>Example:</b> 6559B027', 'wcprf' ),
                            'value' => isset( $options['manufacturer_sku'] ) ? $options['manufacturer_sku'] : ''
                        ) );
        
	   // Manufacturer
                        woocommerce_wp_text_input( array(
                            'id' => 'wc_prf[manufacturer]',
                            'label' => __( 'Manufacturer', 'wcprf' ),
                            'description' => __( '<b>Example:</b> Canon', 'wcprf' ),
                            'value' => isset( $options['manufacturer'] ) ? $options['manufacturer'] : ''
                        ) );
        
                	   // EAN or UPC
                        woocommerce_wp_text_input( array(
                            'id' => 'wc_prf[ean_or_upc]',
                            'label' => __( 'EAN or UPC', 'wcprf' ),
                            'description' => __( '<b>Example:</b> 8714574585567', 'wcprf' ),
                            'value' => isset( $options['ean_or_upc'] ) ? $options['ean_or_upc'] : ''
                        ) );
                    ?>
                </div>
                 <div id="wc_prf_tab_basic" class="options_group">
                    <p class="form-field"><strong><?php _e( 'Optional Product Information<br>These options require active subscription to work - Contact at <a href="http://dicm.dk/" target="_blank">http://dicm.dk/</a>', 'wcprf' ); ?></strong></p>
             
                <?php


                        // Description.
                        woocommerce_wp_textarea_input(
                            array(
                                'id' => 'wc_prf[description_pro]',
                                'label' => __( 'Description', 'wcprf' ),
                                'description' => __( '<b>Example:</b> Long sleeve zip-neck sweater. 100% cotton.', 'wcprf' ),
                                'value' => isset( $options['description'] ) ? $options['description'] : ''
                            )
                        );
        
            // Image URL
                        woocommerce_wp_textarea_input(
                            array(
                                'id' => 'wc_prf[image_url]',
                                'label' => __( 'Image URL', 'wcprf' ),
                                'description' => __( '<b>Example:</b> http://www.site.co.uk/images/ACB132.jpg', 'wcprf' ),
                                'value' => isset( $options['image_url'] ) ? $options['image_url'] : ''
                            )
                        ); 
            // Delivery time
                        woocommerce_wp_textarea_input(
                            array(
                                'id' => 'wc_prf[delivery_time]',
                                'label' => __( 'Delivery time', 'wcprf' ),
                                'description' => __( '<b>Example:</b> Delivers in 5-7 days', 'wcprf' ),
                                'value' => isset( $options['delivery_time'] ) ? $options['delivery_time'] : ''
                            )
                        );   
        // Retailer Message
                        woocommerce_wp_textarea_input(
                            array(
                                'id' => 'wc_prf[retailer_message]',
                                'label' => __( 'Retailer Message', 'wcprf' ),
                                'description' => __( '<b>Example:</b> Free shipping until ...', 'wcprf' ),
                                'value' => isset( $options['retailer_message'] ) ? $options['retailer_message'] : ''
                            )
                        );
        // Product State
                        woocommerce_wp_textarea_input(
                            array(
                                'id' => 'wc_prf[product_state]',
                                'label' => __( 'Product State', 'wcprf' ),
                                'description' => __( '<b>Example:</b> New', 'wcprf' ),
                                'value' => isset( $options['product_state'] ) ? $options['product_state'] : ''
                            )
                        );  
        // ISBN
                        woocommerce_wp_textarea_input(
                            array(
                                'id' => 'wc_prf[isbn]',
                                'label' => __( 'ISBN (Required for book retailers)', 'wcprf' ),
                                'description' => __( '<b>Example:</b> 0563389532', 'wcprf' ),
                                'value' => isset( $options['isbn'] ) ? $options['isbn'] : ''
                            )
                        );
        // Catalog Id
                        woocommerce_wp_textarea_input(
                            array(
                                'id' => 'wc_prf[catalog_id]',
                                'label' => __( 'Catalog Id (Only applies to CDs, DVD, HD-DVD and Blu-Ray films)', 'wcprf' ),
                                'description' => __( '<b>Example:</b> 73216', 'wcprf' ),
                                'value' => isset( $options['catalog_id'] ) ? $options['catalog_id'] : ''
                            )
                        ); 
        // Warranty
                        woocommerce_wp_textarea_input(
                            array(
                                'id' => 'wc_prf[warranty]',
                                'label' => __( 'Warranty', 'wcprf' ),
                                'description' => __( '<b>Example:</b> 1 year warranty', 'wcprf' ),
                                'value' => isset( $options['warranty'] ) ? $options['warranty'] : ''
                            )
                        );   
                ?>
                    <script type="text/javascript">

                        document.getElementById('wc_prf[description_pro]').disabled = true;
                        document.getElementById('wc_prf[image_url]').disabled = true;
                        document.getElementById('wc_prf[delivery_time]').disabled = true;
                        document.getElementById('wc_prf[retailer_message]').disabled = true;
                        document.getElementById('wc_prf[product_state]').disabled = true;         
                        document.getElementById('wc_prf[isbn]').disabled = true;
                        document.getElementById('wc_prf[catalog_id]').disabled = true;
                        document.getElementById('wc_prf[warranty]').disabled = true;
                    
                    </script>  
            </div>
        </div>
        <?php
    }

    /** 
     * Save tab meta.
     *
     * @return void
     */
    function save_tab_options( $post_id ) {
        if ( isset( $_POST['wc_prf_active'] ) ) {
            update_post_meta( $post_id, 'wc_prf_active', $_POST['wc_prf_active'] );
        } else {
            delete_post_meta( $post_id, 'wc_prf_active' );
        }

        if ( isset( $_POST['wc_prf'] ) ) {
            update_post_meta( $post_id, 'wc_prf', $_POST['wc_prf'] );
        }
    }

    /**
     * Add custom feed template page.
     *
     * @param string $page_template Template file path.
     *
     * @return string               Feed template file path.
     */
    public function feed_template( $page_template ) {
        if ( is_page( $this->page ) ) {
            $page_template = WOO_PRF_PATH . '/feed-template.php';
        }

        return $page_template;
    }
}
