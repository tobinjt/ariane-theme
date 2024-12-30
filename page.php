<?php

declare(strict_types=1);

get_header(); ?>
  <main>
<?php
  echo get_banner_message();
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

<?php get_footer(); ?>
