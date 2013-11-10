<?php get_header(); ?>
  <div id="main-content">
    <h2>Search Results</h2>
<?php
  if (have_posts()) {
    include (TEMPLATEPATH . '/_/inc/nav.php' );
    while (have_posts()) {
      the_post();
?>
    <article <?php post_class() ?> id="post-<?php the_ID(); ?>">
      <h2><?php the_title(); ?></h2>
      <?php include (TEMPLATEPATH . '/_/inc/meta.php' ); ?>
      <div class="entry">
        <?php the_excerpt(); ?>
      </div>
    </article>
<?php
    }
    include (TEMPLATEPATH . '/_/inc/nav.php' );
  } else {
    echo '      <h2>No posts found.</h2>', "\n";
  }
?>
  </div>
<?php
  get_sidebar();
  get_footer();
?>
