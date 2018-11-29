Plan for website:

*   ~~Change the grid to take attachment ids and look up `grid_size` images.~~
    *   https://developer.wordpress.org/reference/functions/wp_get_attachment_image_src/
*   ~~Change the product page to take attachment ids and look up `product_size`
    images.~~
    *   https://developer.wordpress.org/reference/functions/wp_get_attachment_image_src/
*   ~~Does the slider flash or transition too abruptly?~~
*   ~~Change the slider to show large or small images.~~  Using srcset instead.
    *   ~~Setup 2 divs, 1 small, 1 large.
    *   Change CSS to hide one of the divs depending on screen size.
    *   Query for both sets of images and set up slider twice.
    *   Unknown: how do I stop both sliders initialising and running?  It'll
        work if both are running, but it's crappy for users.  Maybe make the
        slider check if the div is visible?~~
*   ~~Run PageSpeed against the different pages.~~
    *   Stop Cart66 resources (JS, CSS) being requested unless the page needs
        those resources.  Not doing this: they're needed on almost every page.
    *   ~~Look at SEO report when it's available in stable Chrome.~~
*   ~~Figure out how to use HTTP2.~~  This appears to just work now that HTTP2
    works in general.
    *   ~~Send a location header if there isn't a cookie?
    *   How do I make Wordpress plugins use HTTP2?
    *   https://responsivedesign.is/articles/configuring-http2-push-wordpress/
    *   https://httpd.apache.org/docs/2.4/mod/mod_http2.html
    *   $isHttp2 = stristr($_SERVER["SERVER_PROTOCOL"], "HTTP/2") ? true : false;
    *   http://alistapart.com/article/using-http-2-responsibly-adapting-for-users
    *   http://alistapart.com/article/considering-how-we-use-http2
    *   https://make.wordpress.org/core/2016/07/06/resource-hints-in-4-6/
    *   https://www.sitespeed.io/~~
*   We need a Cart66 replacement sooner or later; either migrate to their hosted
    offering or find a similar solution.  Probably use Shopify,
    https://www.shopify.com/lite
*   Preloading in slider?
*   Check for TODOs.
