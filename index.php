<?php

declare(strict_types=1);

get_header(); ?>

    <div>
<?php
  if (! have_posts()) {
      echo '      <h2>Not Found</h2>', "\n";
  }
while (have_posts()) {
    the_post();
    ?>
      <article <?php post_class(); ?> id="post-<?php the_ID(); ?>">
        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
        <div class="entry">
          <?php the_content(); ?>
        </div>
        <footer class="postmetadata">
          <?php the_tags('Tags: ', ', ', '<br />'); ?>
          Posted in <?php the_category(', '); ?> |
        </footer>
      </article>
<?php
}
include TEMPLATEPATH . '/nav.php';
?>
    </div>

<?php
  get_footer();
?>
