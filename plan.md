Plan for website:

*   Classes rather than arrays:
    *   Change $SLIDER_IMAGES etc.
    *   product_id should be an int everywhere.
    *   Look at any remaining places I use cast(), intval(), or strval().

*   Replace Cart66 with Shopify
    *   https://www.shopify.com/lite
    *   https://help.shopify.com/en/manual/sell-online/buy-button/add-embed-code#add-script-tags-separately
    *   Add the Javascript to all the product pages.
    *   Generate the data Shopify needs for each product; it's very verbose, dunno
        how repetitive or how different it is, only time will tell.
    *   Add compatibility code so that I can set up all the Shopify stuff and enable
        it later.
    *   Profit?
