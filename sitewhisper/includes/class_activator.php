<?php
defined( 'ABSPATH' ) || exit;

class SW_Deactivator {

    // Plugin paused — we keep all data safe
    public static function deactivate() {
        // Clear scheduled cron jobs if any (nothing yet)
        // Table and options stay untouched
    }

}