<?php
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
function remove_script_version(string $src): string {
  $parts = explode('?', $src);
  return $parts[0];
}

/* Check if the Cookie Law Info cookie already exists, or if specific user-agent
 * strings from Page Speed Insights were used.
 * Returns: bool, true if the Cookie Law Info content should be removed.
 */
function ShouldRemoveCookieLawInfo(): bool {
  $hide = false;
  if (isset($_COOKIE['viewed_cookie_policy'])) {
    $hide = true;
  }
  // Page Speed doesn't set the cookie, so fake the typical user experience.
  $force_hide_UAs = array('Google Page Speed Insights', 'Chrome-Lighthouse');
  $user_agent = $_SERVER['HTTP_USER_AGENT'];
  if (is_null($user_agent)) {
    $user_agent = 'fake user agent';
  }
  foreach ($force_hide_UAs as $fhUA) {
    if (strpos($user_agent, $fhUA)) {
      $hide = true;
    }
  }
  return $hide;
}

/* If the Cookie Law Info cookie already exists, remove the Javascript and CSS
 * it wants to load.  Output needs to be inserted in <head> to hide the text
 * added to the footer by the plugin.
 */
function MaybeHideCookieLawInfoInFooter(): string {
  if (!ShouldRemoveCookieLawInfo()) {
    return "";
  }
  return <<<END_OF_CSS

<style>
  #cookie-law-info-bar {
    display: none;
  }
</style>

END_OF_CSS;
}
?>
