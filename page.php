<?php

declare(strict_types=1);

get_header(); ?>
  <main>
      <p class="text-centered larger-text grey">
        Ariane is on sabbatical and is not accepting commissions or selling from
        the website.  Thanks!
      </p>
<?php
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
