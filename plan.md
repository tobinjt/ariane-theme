Plan for images:

*   ~~Add more image sizes: slider_large, slider_small, product_size,
    grid_size.~~
*   ~~Install plugin to generate missing image sizes and run it.~~
*   ~~Change the slider to use `wp_get_attachment_image_src()` to get
    `slider_large` images.~~
*   ~~Move dev to a different database.  Move production to a different
    database.~~
*   ~~Dump production and restore it to dev.  Link dev images directory to www
    images directory.  Script this.~~
*   ~~Set up check-links for dev.~~
*   Change the grid to take attachment ids and look up `grid_size` images.
*   Change the product page to take attachment ids and look up `product_size`
    images.
*   Change the slider to show large or small images.
    *   Setup 2 divs, 1 small, 1 large.
    *   Change CSS to hide one of the divs depending on screen size.
    *   Query for both sets of images and set up slider twice.
    *   Unknown: how do I stop both sliders initialising and running?  It;ll
        work if both are running, but it's crappy for users.  Maybe make the
        slider check if the div is visible?
*   Run PageSpeed against the different pages.
    *   Stop cart66 resources (JS, CSS) being requested unless the page needs
        those resources.
    *   Look at SEO report when it's available in stable Chrome.
