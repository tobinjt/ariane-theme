<?php

declare(strict_types=1);

/* Functions to help with configuring Wordpress.  Doesn't have any functions
 * that just call Wordpress functions because they aren't really testable.
 */

/* Remove the version strings from CSS and Javascript to improve browser
   caching.  Found by searching for "wordpress remove query strings from
   static resources".
   Args:
    $src: string, source URL.
   Returns: string, URL without any parameters.
 */
function remove_script_version(string $src): string
{
    $parts = explode('?', $src);
    return $parts[0];
}

/* Check if the Cookie Law Info cookie already exists, or if specific user-agent
 * strings from Page Speed Insights were used.
 * Returns: bool, true if the Cookie Law Info content should be removed.
 */
function ShouldRemoveCookieLawInfo(): bool
{
    if (isset($_COOKIE['viewed_cookie_policy'])) {
        return true;
    }
    // Page Speed doesn't set the cookie, so fake the typical user experience.
    if (! isset($_SERVER['HTTP_USER_AGENT'])) {
        return false;
    }
    $force_hide_UAs = ['Google Page Speed Insights', 'Chrome-Lighthouse'];
    foreach ($force_hide_UAs as $fhUA) {
        if (strpos($_SERVER['HTTP_USER_AGENT'], $fhUA) !== false) {
            return true;
        }
    }
    return false;
}

/* Output needs to be inserted in <head> to hide the text added to the footer by
 * the Cookie Law Info plugin.
 */
function HideCookieLawInfoInFooter(): string
{
    return <<<'END_OF_CSS'

<style>
  #cookie-law-info-bar, #cookie-law-info-again, .cli-modal-dialog,
      .cli-modal-backdrop {
    display: none;
  }
</style>

END_OF_CSS;
}

function MaybeRemoveCookieLawInfoFromHead(): void
{
    if (! ShouldRemoveCookieLawInfo()) {
        return;
    }
    // Remove the Javascript and CSS.  There will still be some Javascript
    // output directly in the page with the plugin settings, don't worry about
    // that.
    // To figure out the correct strings in future, add this at the end of
    // header.php:
    // <pre>
    // <?php
    //   global $wp_scripts;
    //   echo htmlspecialchars(print_r($wp_scripts, true));
    // ? >
    // </pre>
    // Then search the output for 'cookie-law-info' and look for "handle =
    // 'foo'", where 'foo' is the string you need.
    wp_dequeue_style('cookie-law-info');
    wp_deregister_style('cookie-law-info');
    wp_dequeue_style('cookie-law-info-gdpr');
    wp_deregister_style('cookie-law-info-gdpr');
    wp_dequeue_script('cookie-law-info');
    wp_deregister_script('cookie-law-info');
}
