<?php
/*
 Plugin Name: WooCommerce Alert Existing SKU
 Author: Darlan ten Caten
 Author URI: http://www.i9solucoesdigitais.com.br
 Text Domain: waes
 Plugin URI: https://github.com/darlantc/WooCommerce-Alert-Existing-SKU
 Description: WooCommerce Addon to verify if the new SKU is unique 
 Version: 1.1.2
 
 ----

 /**
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * **********************************************************************
 */

/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) { 
    if ( ! class_exists( 'WooCommerceAlertExistingSKU' ) ) {
        /**
         * Localisation
         **/
        load_plugin_textdomain( 'waes', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

        class WooCommerceAlertExistingSKU {
            public function __construct() {
                // called only after woocommerce has finished loading
                add_action( 'woocommerce_init', array( &$this, 'woocommerce_loaded' ) );
                
                /******************************
                * Includes
                ******************************/
                include('includes/scripts.php'); // this controls all JS / CSS 
            }
            
            /**
             * Take care of anything that needs woocommerce to be loaded.  
             * For instance, if you need access to the $woocommerce global
             */
            public function woocommerce_loaded() {
                /*************************************/
                /* Adding AJAX function */
                /*************************************/
                add_action( 'init', 'waes_ajax_function' );
                function waes_ajax_function() {
                    add_action('wp_ajax_waes_load_sku', 'waes_load_sku');
                }
            }
        }

        // finally instantiate our plugin class and add it to the set of globals
        $GLOBALS['waes'] = new WooCommerceAlertExistingSKU();
    }
}

 /*************************************/
/* Functions */
/*************************************/
function waes_load_sku () {
    $valid = true;
    $message = __( 'Código de REFERÊNCIA válido.', 'waes' );

    $new_sku = sanitize_text_field( $_POST['newSKU'] );

    $args = array(
        'post_type' => 'product',
        'meta_key' => '_sku',
        'meta_query' => array(
            'relation' => 'AND',
                array(
                    'key' => '_sku',
                    'value' => $new_sku,
                    'compare' => 'LIKE'
                )
        ),
        'posts_per_page'   =>  10,
        'post_status' => 'publish',
        'orderby'     => 'title', 
        'order'       => 'ASC'
    );

    $sku_list = array();

    $posts = get_posts( $args );
    foreach ( $posts as $post ) {
        $meta = get_post_meta( $post->ID, '_sku', true );
        $sku_list[] = esc_html( $meta );
        if ( strtolower( $new_sku ) == strtolower( $meta ) ) {
            $valid = false;
            $message = __( 'Código de REFERÊNCIA já existe. Não é permitido duplicar.', 'waes' );
        }
    } 
    $result = array( 'valid' => $valid, 'message' => $message, 'sku_list' => $sku_list );

    echo json_encode( $result );
    die();
}