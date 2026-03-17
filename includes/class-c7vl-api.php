<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class C7VL_API {
    
    private function get_request_args() {
        $options   = get_option( 'c7vl_settings' );
        $tenant    = $options['tenant'] ?? '';
        $app_id    = $options['app_id'] ?? ''; 
        $api_token = $options['api_token'] ?? '';

        $args = [
            'headers' => [
                'Content-Type'  => 'application/json',
                'tenant'        => $tenant,
            ],
            'timeout' => 45, // Boosted timeout for heavy API fetches
        ];

        if ( ! empty( $app_id ) && ! empty( $api_token ) ) {
            $auth_string = $app_id . ':' . $api_token;
            $args['headers']['Authorization'] = 'Basic ' . base64_encode( $auth_string );
        }

        return $args;
    }

    private function get_api_base() {
        $options = get_option( 'c7vl_settings' );
        return rtrim( $options['api_base'] ?? 'https://api.commerce7.com/v1', '/' );
    }

    public function fetch_all_vendors() {
        $url  = $this->get_api_base() . '/Vendor';
        $response = wp_remote_get( $url, $this->get_request_args() );

        if ( is_wp_error( $response ) ) {
            return [ 'success' => false, 'error' => 'WP Error: ' . $response->get_error_message() ];
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( $code !== 200 ) {
            return [ 'success' => false, 'error' => "Vendor API HTTP $code: " . wp_remote_retrieve_body( $response ) ];
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        return [ 'success' => true, 'data' => $data['vendors'] ?? [] ];
    }

    public function fetch_all_products() {
        $all_products = [];
        $page = 1;
        $limit = 50; // FIXED: Commerce7 maximum limit is 50

        while ( $page <= 100 ) { // Increased safety max pages to 100 (5,000 products total)
            $url  = $this->get_api_base() . "/product?page={$page}&limit={$limit}";
            $response = wp_remote_get( $url, $this->get_request_args() );

            if ( is_wp_error( $response ) ) {
                return [ 'success' => false, 'error' => 'WP Error: ' . $response->get_error_message() ];
            }

            $code = wp_remote_retrieve_response_code( $response );
            if ( $code !== 200 ) {
                return [ 'success' => false, 'error' => "Product API HTTP $code: " . wp_remote_retrieve_body( $response ) ];
            }

            $data = json_decode( wp_remote_retrieve_body( $response ), true );
            
            if ( empty( $data['products'] ) ) break;

            $all_products = array_merge( $all_products, $data['products'] );

            // Stop if we have fetched the total available products
            if ( isset( $data['total'] ) && count( $all_products ) >= $data['total'] ) break;

            $page++;
        }

        return [ 'success' => true, 'data' => $all_products ];
    }
}