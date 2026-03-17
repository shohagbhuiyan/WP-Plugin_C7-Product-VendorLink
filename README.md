# Commerce7 Vendor Linker

Integrates WordPress with the Commerce7 API to fetch vendors and dynamically hyperlink their names on the frontend.

## Installation & Setup
1. Upload the `commerce7-vendor-linker` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin via the WordPress admin area.
3. Navigate to **Settings > C7 Vendor Linker**.
4. Enter your Tenant ID, API Base URL, and API Token.
5. Define your CSS selectors (default: `.winelabel, #winelabel`).
6. Click **Save Changes** and optionally **Refresh Vendors Cache Now**.

## Options Used
* `c7vl_settings` (Array in wp_options)
* `c7vl_vendor_data` (Transient caching vendor API data)
* `c7vl_cron_refresh_vendors` (WP-Cron hook)