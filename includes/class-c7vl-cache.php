<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class C7VL_Cache {
    private $upload_dir;
    private $cache_dir;

    public function __construct() {
        $this->upload_dir = wp_upload_dir();
        $this->cache_dir  = trailingslashit( $this->upload_dir['basedir'] ) . 'c7vl/';
    }

    public function init() {
        add_action( 'c7vl_cron_refresh_vendors', [ $this, 'sync_all_data' ] );
    }

    public function sync_all_data() {
        // Check directory permissions first
        if ( ! file_exists( $this->cache_dir ) ) {
            if ( ! wp_mkdir_p( $this->cache_dir ) ) {
                return [ 'success' => false, 'error' => 'File Permission Error: Could not create directory at ' . $this->cache_dir ];
            }
        }

        $api = new C7VL_API();

        // 1. Fetch Vendors
        $v_res = $api->fetch_all_vendors();
        if ( ! $v_res['success'] ) return $v_res; // Stop and return the exact API error

        // 2. Fetch Products
        $p_res = $api->fetch_all_products();
        if ( ! $p_res['success'] ) return $p_res; // Stop and return the exact API error

        // 3. Save Vendors to File
        $v_file = $this->cache_dir . 'c7-vendors.json';
        if ( file_put_contents( $v_file, wp_json_encode( $v_res['data'], JSON_PRETTY_PRINT ) ) === false ) {
            return [ 'success' => false, 'error' => 'File Permission Error: Failed to write ' . $v_file ];
        }

        // 4. Save Products to File
        $p_file = $this->cache_dir . 'c7-products.json';
        if ( file_put_contents( $p_file, wp_json_encode( $p_res['data'], JSON_PRETTY_PRINT ) ) === false ) {
            return [ 'success' => false, 'error' => 'File Permission Error: Failed to write ' . $p_file ];
        }

        return [
            'success'  => true,
            'vendors'  => count( $v_res['data'] ),
            'products' => count( $p_res['data'] )
        ];
    }

    public function get_cache_urls() {
        $base_url = trailingslashit( $this->upload_dir['baseurl'] ) . 'c7vl/';
        return [
            'vendors'  => $base_url . 'c7-vendors.json',
            'products' => $base_url . 'c7-products.json'
        ];
    }
    
    public function get_cache_stats() {
        $stats = [];
        $files = [ 'vendors' => 'c7-vendors.json', 'products' => 'c7-products.json' ];
        
        foreach ( $files as $key => $filename ) {
            $path = $this->cache_dir . $filename;
            if ( file_exists( $path ) ) {
                $data = json_decode( file_get_contents( $path ), true );
                $stats[$key] = [
                    'exists' => true,
                    'count'  => is_array( $data ) ? count( $data ) : 0,
                    'time'   => filemtime( $path ),
                    'url'    => $this->get_cache_urls()[$key]
                ];
            } else {
                $stats[$key] = [ 'exists' => false ];
            }
        }
        return $stats;
    }
}