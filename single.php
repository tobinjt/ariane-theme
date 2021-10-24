<?php get_header(); ?>
    <div id="main-content-post">
<?php
  while (have_posts()) {
    the_post();
?>
      <article <?php post_class(); ?> id="post-<?php the_ID(); ?>">
        <h1 class="grey"><?php the_title(); ?></h1>
        <div class="entry-content">
<?php
    the_content();
    wp_link_pages(array('before' => 'Pages: ', 'next_or_number' => 'number'));
    the_tags('Tags: ', ', ', '');
?>
        </div>
      </article>
<?php
  }
?>
    </div>
<?php
  get_footer();
?>
