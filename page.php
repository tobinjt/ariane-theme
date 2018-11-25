<?php get_header(); ?>
  <div id="main-content-page">
    <main>
<?php
  echo get_messages_for_top_of_page();
  if (is_jewellery_page()) {
    echo '      <div id="jewellery-page">', "\n";
    echo '        <br id="add-extra-space-before-description"/>', "\n";
  }
  while (have_posts()) {
    the_post();
?>
        <article class="single-page">
          <div class="entry">
          <?php the_content(); ?>
          </div>
        </article>
<?php
  }
  if (is_jewellery_page()) {
    echo '      </div>', "\n";
  }
?>
    </main>
  </div>

<?php get_footer(); ?>
