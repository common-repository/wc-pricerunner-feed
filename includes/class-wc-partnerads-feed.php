<?php
/**
 * PartnerAds Feed class.
 *
 * @since 1.0.0
 */
class WC_partnerads_Feed extends WC_Integration {

    /**
     * Init and hook in the integration.
     *
     * @return void
     */
    public function __construct() {
        $this->id                 = 'partnerads';
        $this->method_title       = __( 'Partnerads', 'wcpaf' );
        $this->method_description = __( 'Creates a Feed to integrate with your Partnerads.', 'wcpaf' );

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables.
        $this->page         = apply_filters( 'wc_partnerads_Feed_page', sanitize_title( _x( 'partnerads-feed', 'page slug', 'wcpaf' ) ) );
        $this->include_all_products = $this->get_option( 'include_all_products' );

        // Save integration options.
        add_action( 'woocommerce_update_options_integration_partnerads', array( $this, 'process_admin_options' ) );

        // Add write panel tab.
        add_action( 'woocommerce_product_write_panel_tabs', array( &$this, 'add_tab' ) );

        // Create write panel.
        add_action( 'woocommerce_product_write_panels', array( &$this, 'tab_view' ) );

        // Save meta.
        add_action( 'woocommerce_process_product_meta', array( &$this, 'save_tab_options' ) );

        // Add page template.
        add_filter( 'page_template', array( &$this, 'feed_template' ) );

    }

    /**
     * Initialise Integration Settings Form Fields.
     *
     * @return void
     */
    public function init_form_fields() {
     $this->form_fields = array(
            'include_all_products' => array(
                'title'       => __( 'Include all products in the feed', 'wcpaf' ),
                'type'        => 'checkbox',
                'description' => __( 'Activate this option if you want all products in the feed.', 'wcpaf' ),
                'desc_tip'    => true,
                'default'     => ''
            ),
            'default_description' => array(
                'title'       => __( 'Default description', 'wcpaf' ),
                'type'        => 'textarea',
                'description' => __( 'If no description is set for a prodcut this will be used.', 'wcpaf' ),
                'desc_tip'    => true,
                'default'     => __( 'There is no description for this product' )
            ),
            'default_category' => array(
                'title'       => __( 'Default category', 'wcpaf' ),
                'type'        => 'text',
                'description' => __( 'Set the default category', 'wcpaf' ),
                'desc_tip'    => true,
                'default'     => 'products'
            ),
            'default_brand' => array(
                'title'       => __( 'Default brand name', 'wcpaf' ),
                'type'        => 'text',
                'description' => __( 'Set the default brand name', 'wcpaf' ),
                'desc_tip'    => true,
                'default'     => 'noname'
            ),
            'default_shipping' => array(
                'title'       => __( 'Default shipping cost', 'wcpaf' ),
                'type'        => 'text',
                'description' => __( 'Set the default shipping cost (Example: 39.00)', 'wcpaf' ),
                'desc_tip'    => true,
                'default'     => ''
            ),
            'allow_images_in_feed' => array(
                'title'       => __( 'Allow images in feed?', 'wcpaf' ),
                'type'        => 'checkbox',
                'description' => __( 'If you allow images to be viewed in the feed, then activate the option here.', 'wcpaf' ),
                'desc_tip'    => true,
                'default'     => ''
            ),    
     );
        
        
    }


    /**
     * Add new tab.
     */
    public function add_tab() {
        echo '<li class="advanced_tab advanced_options wc_paf_tab"><a href="#wc_paf_tab">' . __( 'PartnerAds', 'wcpaf' ) . '</a></li>';
    }

    /**
     * Tab content.
     */
    public function tab_view() {
        global $post;

        $options = get_post_meta( $post->ID, 'wc_paf', true );
        $active = get_post_meta( $post->ID, 'wc_paf_active', true );
        ?>
        <div id="wc_paf_tab" class="panel woocommerce_options_panel">
            <div id="wc_paf_tab_active" class="options_group">
                <?php
                    woocommerce_wp_checkbox(
                        array(
                            'id' => 'wc_paf_active',
                            'label' => __( 'Include in Product Feed?', 'wcpaf' ),
                            'description' => __( 'Enable this option to include in this product in your Product Feed. If the option "
Include all products in the feed" is active, this option wont have any effect.', 'wcpaf' ),
                            'value' => isset( $active ) ? $active : ''
                        )
                    );
                ?>
                
            </div> 

            <div id="wc_paf_items">
                <div id="wc_paf_tab_basic" class="options_group">
                    <p class="form-field"><strong><?php _e( 'Basic Product Information', 'wcpaf' ); ?></strong></p>
                    <?php
                        // Description.
                        woocommerce_wp_textarea_input(
                            array(
                                'id' => 'wc_paf[description]',
                                'label' => __( 'Description', 'wcpaf' ),
                                'description' => __( 'Description of the item', 'wcpaf' ),
                                'value' => isset( $options['description'] ) ? $options['description'] : ''
                            )
                        );

                        // Category.
                        woocommerce_wp_textarea_input( array(
                            'id' => 'wc_paf[category]',
                            'label' => __( 'Category', 'wcpaf' ),
                            'description' => __( '', 'wcpaf' ),
                            'value' => isset( $options['category'] ) ? $options['category'] : $this->category
                        ) );
						
						   // Brand cost.
                        woocommerce_wp_text_input( array(
                            'id' => 'wc_paf[brand_name]',
                            'label' => __( 'Brand name', 'wcpaf' ),
                            'description' => __( 'Brand name (Example: Armani)', 'wcpaf' ),
                            'value' => isset( $options['brand_name'] ) ? $options['brand_name'] : ''
                        ) );
						
						   // Shipping cost.
                        woocommerce_wp_text_input( array(
                            'id' => 'wc_paf[shipping_cost]',
                            'label' => __( 'Shipping cost', 'wcpaf' ),
                            'description' => __( 'Enter shipping price (Example: 0.00)', 'wcpaf' ),
                            'value' => isset( $options['shipping_cost'] ) ? $options['shipping_cost'] : ''
                        ) );


                      

                    ?>
                </div>
                              
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
        if ( isset( $_POST['wc_paf_active'] ) ) {
            update_post_meta( $post_id, 'wc_paf_active', $_POST['wc_paf_active'] );
        } else {
            delete_post_meta( $post_id, 'wc_paf_active' );
        }

        if ( isset( $_POST['wc_paf'] ) ) {
            update_post_meta( $post_id, 'wc_paf', $_POST['wc_paf'] );
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
            $page_template = WOO_PAF_PATH . '/feed-template.php';
        }

        return $page_template;
    }
}
