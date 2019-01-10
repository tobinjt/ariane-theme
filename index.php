<?php get_header(); ?>

    <div id="main-content-blog">
<?php
  if (!have_posts()) {
    echo '      <h2>Not Found</h2>', "\n";
  }
  while (have_posts()) {
    the_post();
?>
      <article <?php post_class(); ?> id="post-<?php the_ID(); ?>">
        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
        <?php include(TEMPLATEPATH . '/_/inc/meta.php'); ?>
        <div class="entry">
          <?php the_content(); ?>
        </div>
        <footer class="postmetadata">
          <?php the_tags('Tags: ', ', ', '<br />'); ?>
          Posted in <?php the_category(', '); ?> |
          <?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); ?>
        </footer>
      </article>
<?php
  }
  include(TEMPLATEPATH . '/_/inc/nav.php');
?>
    </div>

<?php
  get_footer();
?>
