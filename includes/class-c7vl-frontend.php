<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class C7VL_Frontend {
    public function init() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    public function enqueue_scripts() {
        $options   = get_option( 'c7vl_settings' );
        $selectors = $options['css_selectors'] ?? '.winelabel, #winelabel';
        $base_url  = $options['collection_url'] ?? '/collection/wines';
        $debug     = isset( $options['debug_mode'] ) && $options['debug_mode'] === '1';

        $cache = new C7VL_Cache();
        $urls  = $cache->get_cache_urls();

        wp_enqueue_script( 'c7vl-frontend-js', C7VL_PLUGIN_URL . 'assets/js/c7vl-frontend.js', [], C7VL_VERSION, true );
        
        wp_localize_script( 'c7vl-frontend-js', 'C7VL_Data', [
            'selectors'    => sanitize_text_field( $selectors ),
            'baseUrl'      => esc_url( $base_url ),
            'debug'        => $debug,
            'vendorsJson'  => $urls['vendors'],
            'productsJson' => $urls['products']
        ]);
    }
}