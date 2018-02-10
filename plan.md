Plan for website:

*   Change the grid to take attachment ids and look up `grid_size` images.
*   Change the product page to take attachment ids and look up `product_size`
    images.
*   Change the slider to show large or small images.
    *   Setup 2 divs, 1 small, 1 large.
    *   Change CSS to hide one of the divs depending on screen size.
    *   Query for both sets of images and set up slider twice.
    *   Unknown: how do I stop both sliders initialising and running?  It'll
        work if both are running, but it's crappy for users.  Maybe make the
        slider check if the div is visible?
*   Run PageSpeed against the different pages.
    *   Stop Cart66 resources (JS, CSS) being requested unless the page needs
        those resources.
    *   Look at SEO report when it's available in stable Chrome.
*   Figure out how to use HTTP2:
    *   Send a location header if there isn't a cookie?
    *   How do I make Wordpress plugins use HTTP2?
*   We need a Cart66 replacement sooner or later; either migrate to their hosted
    offering or find a similar solution.
