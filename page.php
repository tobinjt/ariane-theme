<?php get_header(); ?>
  <div id="main-content-page">
<?php
  if (is_jewellery_page()) {
    echo '    <div id="jewellery-page">', "\n";
  }
  while (have_posts()) {
    the_post();
?>
      <article class="single-page">
        <div class="entry">
<?php
    // Save the page content so we can check for slider presence in the footer.
    global $PAGE_CONTENT;
    $PAGE_CONTENT = get_the_content();
    if (preg_match('/^!!JEWELLERY GRID!!/', $PAGE_CONTENT)) {
      echo MakeJewelleryGrid($PAGE_CONTENT);
    } else {
      // get_the_content() returns unfiltered content; in particular, shortcodes are
      // not expanded.  Use the_content() instead of processing $PAGE_CONTENT and
      // needing to keep that processing up to date; I assume that getting the page
      // content is reasonably well cached.
      the_content();
    }
?>
        </div>
        <?php edit_post_link('Edit this entry.', '<p>', '</p>'); ?>
      </article>
<?php
  }
  if (is_jewellery_page()) {
    echo '    </div>', "\n";
  }
?>
  </div>

<?php get_footer(); ?>
