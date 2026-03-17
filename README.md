# Commerce7 Vendor Linker

Integrates WordPress with the Commerce7 API to dynamically link vendor names on frontend product detail pages. 

Instead of relying on fragile text-matching, this plugin uses a robust ID-matching system. It bulk-fetches your Commerce7 products and vendors, caches them as lightning-fast static JSON files, and uses JavaScript to dynamically inject the correct vendor link based on the product URL.

## How It Works
1. **Data Sync**: The plugin queries the Commerce7 API (respecting the 50-item pagination limit) and downloads your entire product and vendor catalogs.
2. **JSON Caching**: The data is saved as static `.json` files in your `wp-content/uploads/c7vl/` directory. This avoids WordPress database bloat and ensures ultra-fast frontend performance.
3. **Frontend Matching**: When a user visits a product page (e.g., `/product/my-wine-slug`), the frontend JavaScript:
   - Reads the slug from the URL.
   - Looks up the product in the cached JSON to find its `vendorId`.
   - Looks up the `vendorId` in the vendor JSON to find the exact Vendor Title.
   - Replaces the contents of your target CSS selector (e.g., `.winelabel`) with a hyperlink to the vendor's collection.

## Installation & Setup
1. Upload the `commerce7-vendor-linker` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin via the WordPress admin area.
3. Navigate to **Settings > C7 Vendor Linker**.
4. Enter your Commerce7 API details:
   - **Tenant ID** (e.g., `your-tenant-name`)
   - **App ID (Username)**
   - **API Key (Password)**
5. Define your target **CSS Selectors** (default: `.winelabel, #winelabel`).
6. Click **Save Changes**.
7. Scroll down to the **Data Sync (JSON Cache)** section and click **Sync Commerce7 Data Now**. Verify that both the Vendor and Product JSON files show an "Active" status.

## Plugin Settings
* **API Base URL**: `https://api.commerce7.com/v1`
* **Collection Base URL**: The base path where your vendor links should point (e.g., `/collection/wines`). The plugin will append `?vendorId=...` to this URL.
* **Debug Mode**: Enable this to print detailed matching steps in your browser's developer console. Highly recommended when initially setting up the plugin or modifying selectors.

## Files & Storage
* **Uploads Directory**: The plugin automatically creates a folder at `/wp-content/uploads/c7vl/`.
* **Vendor Data**: Stored in `c7-vendors.json`.
* **Product Data**: Stored in `c7-products.json`.

## Requirements & Assumptions
* The product detail page URL must contain `/product/{slug}` for the JavaScript to detect the current product.
* The frontend elements you are targeting (e.g., `<div class="winelabel"></div>`) must exist on the page. The plugin uses a `MutationObserver` to ensure it catches elements even if they load late via page builders like Divi.

## Troubleshooting
If links are not appearing:
1. Ensure you have clicked **Sync Commerce7 Data Now** and no red errors appeared.
2. Check the browser console (with Debug Mode enabled) to see exactly where the matching process is stopping.
3. Verify your CSS selectors match the actual HTML structure of your product pages.
