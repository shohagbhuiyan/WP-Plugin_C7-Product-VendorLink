<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class C7VL_Core {
    public function init() {
        $admin    = new C7VL_Admin();
        $cache    = new C7VL_Cache();
        $frontend = new C7VL_Frontend();

        $admin->init();
        $cache->init();
        $frontend->init();
    }
}