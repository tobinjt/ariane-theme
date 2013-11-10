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
        <?php the_content(); ?>
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
