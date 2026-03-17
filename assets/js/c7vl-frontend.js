document.addEventListener("DOMContentLoaded", async function () {
    if (typeof C7VL_Data === "undefined") return;

    const { selectors, baseUrl, debug, vendorsJson, productsJson } = C7VL_Data;
    const log = (msg) => { if (debug) console.log("[C7VL]: " + msg); };

    log("Plugin JS Initialized. Using JSON File Cache Strategy.");

    const pathParts = window.location.pathname.split('/').filter(p => p !== '');
    const productIndex = pathParts.indexOf('product');

    if (productIndex === -1 || productIndex === pathParts.length - 1) {
        log("Not a product page. Exiting.");
        return;
    }

    const currentSlug = pathParts[productIndex + 1];
    log(`Current product slug detected: ${currentSlug}`);

    try {
        // Fetch both static JSON files at the same time
        log("Fetching static JSON files...");
        const [productsRes, vendorsRes] = await Promise.all([
            fetch(productsJson + "?v=" + new Date().getTime()), // Cache buster for testing
            fetch(vendorsJson + "?v=" + new Date().getTime())
        ]);

        if (!productsRes.ok || !vendorsRes.ok) throw new Error("Failed to load JSON files from uploads folder.");

        const allProducts = await productsRes.json();
        const allVendors = await vendorsRes.json();

        // Find the product by slug
        const product = allProducts.find(p => p.slug === currentSlug);
        if (!product) {
            log(`ERROR: Product with slug '${currentSlug}' not found in c7-products.json!`);
            return;
        }

        const vendorId = product.vendorId;
        log(`=> Commerce7 Product ID: ${product.id}`);
        log(`=> Commerce7 Vendor ID: ${vendorId}`);

        if (!vendorId) {
            log("ERROR: Product has no vendorId assigned.");
            return;
        }

        // Find the vendor by ID
        const vendor = allVendors.find(v => v.id === vendorId);
        if (!vendor) {
            log(`ERROR: Vendor with ID '${vendorId}' not found in c7-vendors.json!`);
            return;
        }

        const vendorTitle = vendor.title;
        log(`=> Matched Vendor Title: "${vendorTitle}"`);

        // DOM Replacement Logic
        function processElements() {
            const elements = document.querySelectorAll(selectors);
            elements.forEach(el => {
                if (el.dataset.c7vlProcessed === 'true') return;

                el.innerHTML = ''; 
                const link = document.createElement('a');
                link.href = `${baseUrl}?vendorId=${vendorId}`;
                link.className = 'c7vl-vendor-link';
                link.textContent = vendorTitle; 
                
                el.appendChild(link);
                el.dataset.c7vlProcessed = 'true';
                log(`SUCCESS: Replaced inner HTML of element and added anchor tag.`);
            });
        }

        processElements();

        const observer = new MutationObserver((mutations) => {
            let shouldProcess = false;
            for (let mutation of mutations) {
                if (mutation.addedNodes.length > 0) {
                    shouldProcess = true;
                    break;
                }
            }
            if (shouldProcess) processElements();
        });

        observer.observe(document.body, { childList: true, subtree: true });

    } catch (error) {
        log(`Error processing JSON Data: ${error.message}`);
    }
});