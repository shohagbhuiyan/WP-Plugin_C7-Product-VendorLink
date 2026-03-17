<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class C7VL_Admin {
    public function init() {
        add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
        add_action( 'wp_ajax_c7vl_sync_data', [ $this, 'ajax_sync_data' ] );
    }

    public function add_settings_page() {
        add_options_page( 'Commerce7 Vendor Linker', 'C7 Vendor Linker', 'manage_options', 'c7vl-settings', [ $this, 'render_settings_page' ] );
    }

    public function register_settings() {
        register_setting( 'c7vl_settings_group', 'c7vl_settings' );
        add_settings_section( 'c7vl_main_section', 'API & Display Settings', null, 'c7vl-settings' );

        $fields = [
            'api_base'       => [ 'title' => 'API Base URL', 'default' => 'https://api.commerce7.com/v1' ],
            'tenant'         => [ 'title' => 'Tenant ID', 'default' => '' ],
            'app_id'         => [ 'title' => 'App ID (Username)', 'default' => '' ], 
            'api_token'      => [ 'title' => 'API Key (Password)', 'default' => '' ], 
            'collection_url' => [ 'title' => 'Collection Base URL', 'default' => '/collection/wines' ],
            'css_selectors'  => [ 'title' => 'CSS Selectors', 'default' => '.winelabel, #winelabel' ],
            'debug_mode'     => [ 'title' => 'Debug Mode', 'default' => '0', 'type' => 'checkbox' ],
        ];

        foreach ( $fields as $id => $field ) {
            add_settings_field( $id, $field['title'], [ $this, 'render_field' ], 'c7vl-settings', 'c7vl_main_section', [ 'id' => $id, 'default' => $field['default'], 'type' => $field['type'] ?? 'text' ] );
        }
    }

    public function render_field( $args ) {
        $options = get_option( 'c7vl_settings' );
        $val     = $options[ $args['id'] ] ?? $args['default'];
        if ( isset( $args['type'] ) && $args['type'] === 'checkbox' ) {
            echo '<input type="checkbox" name="c7vl_settings[' . esc_attr( $args['id'] ) . ']" value="1" ' . checked( 1, $val, false ) . ' />';
        } else {
            echo '<input type="text" name="c7vl_settings[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( $val ) . '" class="regular-text" />';
        }
    }

    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        $cache = new C7VL_Cache();
        $stats = $cache->get_cache_stats();
        ?>
        <div class="wrap">
            <h1>Commerce7 Vendor Linker</h1>
            <form action="options.php" method="post">
                <?php settings_fields( 'c7vl_settings_group' ); do_settings_sections( 'c7vl-settings' ); submit_button(); ?>
            </form>
            <hr>
            <h2>Data Sync (JSON Cache)</h2>
            <p>Fetch the complete payloads from Commerce7 and save them as static JSON files in your uploads folder.</p>
            
            <button id="c7vl-sync-btn" class="button button-primary button-large">Sync Commerce7 Data Now</button>
            <span id="c7vl-action-status" style="margin-left: 10px; font-weight: bold;"></span>

            <div style="display: flex; gap: 20px; margin-top: 20px;">
                <div style="flex: 1; background: #fff; padding: 15px; border: 1px solid #ccd0d4;">
                    <h3>Vendor JSON File</h3>
                    <?php if ( $stats['vendors']['exists'] ) : ?>
                        <p><strong>Status:</strong> <span style="color: green;">Active</span></p>
                        <p><strong>Total Items:</strong> <?php echo esc_html( $stats['vendors']['count'] ); ?></p>
                        <p><strong>Last Synced:</strong> <?php echo esc_html( wp_date( 'Y-m-d H:i:s', $stats['vendors']['time'] ) ); ?></p>
                        <p><a href="<?php echo esc_url( $stats['vendors']['url'] ); ?>" target="_blank" class="button">View c7-vendors.json</a></p>
                    <?php else: ?>
                        <p style="color: red;">File not created yet. Please Sync.</p>
                    <?php endif; ?>
                </div>
                <div style="flex: 1; background: #fff; padding: 15px; border: 1px solid #ccd0d4;">
                    <h3>Product JSON File</h3>
                    <?php if ( $stats['products']['exists'] ) : ?>
                        <p><strong>Status:</strong> <span style="color: green;">Active</span></p>
                        <p><strong>Total Items:</strong> <?php echo esc_html( $stats['products']['count'] ); ?></p>
                        <p><strong>Last Synced:</strong> <?php echo esc_html( wp_date( 'Y-m-d H:i:s', $stats['products']['time'] ) ); ?></p>
                        <p><a href="<?php echo esc_url( $stats['products']['url'] ); ?>" target="_blank" class="button">View c7-products.json</a></p>
                    <?php else: ?>
                        <p style="color: red;">File not created yet. Please Sync.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    public function enqueue_admin_scripts( $hook ) {
        if ( 'settings_page_c7vl-settings' !== $hook ) return;
        wp_enqueue_script( 'c7vl-admin-js', C7VL_PLUGIN_URL . 'assets/js/c7vl-admin.js', ['jquery'], C7VL_VERSION, true );
        wp_localize_script( 'c7vl-admin-js', 'c7vlAdminData', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'c7vl_admin_nonce' )
        ]);
    }

    public function ajax_sync_data() {
        check_ajax_referer( 'c7vl_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Unauthorized' );

        $cache = new C7VL_Cache();
        $results = $cache->sync_all_data();

        // Now we check our new detailed success flag
        if ( $results['success'] ) {
            wp_send_json_success( "Success! Synced {$results['products']} products and {$results['vendors']} vendors." );
        } else {
            // Output the EXACT error message from the API or File System
            wp_send_json_error( $results['error'] );
        }
    }
}