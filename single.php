<?php get_header(); ?>
  <div id="make-space-for-sidebar">
    <div id="main-content-post">
<?php
  while (have_posts()) {
    the_post();
?>
      <article <?php post_class() ?> id="post-<?php the_ID(); ?>">
        <h1 class="entry-title"><?php the_title(); ?></h1>
        <div class="entry-content">
<?php
    the_content();
    wp_link_pages(array('before' => 'Pages: ', 'next_or_number' => 'number'));
    the_tags( 'Tags: ', ', ', '');
    include (TEMPLATEPATH . '/_/inc/meta.php' );
?>
        </div>
      </article>
<?php
    comments_template();
  }
?>
    </div>
  </div>
<?php
  get_sidebar();
  get_footer();
?>
