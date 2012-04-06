<?php
// Create a dummy page contents so we can check for slider presence in the
// footer; we know it'll never happen outside a page.
$PAGE_CONTENT = '';
?>

<?php get_header(); ?>

    <div id="make-space-for-sidebar">
        <div id="main-content-blog">
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

                <article <?php post_class() ?> id="post-<?php the_ID(); ?>">

                    <h2><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h2>

                    <?php include (TEMPLATEPATH . '/_/inc/meta.php' ); ?>

                    <div class="entry">
                        <?php the_content(); ?>
                    </div>

                    <footer class="postmetadata">
                        <?php the_tags('Tags: ', ', ', '<br />'); ?>
                        Posted in <?php the_category(', ') ?> |
                        <?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); ?>
                    </footer>

                </article>

            <?php endwhile; ?>

            <?php include (TEMPLATEPATH . '/_/inc/nav.php' ); ?>

            <?php else : ?>

                <h2>Not Found</h2>

            <?php endif; ?>
        </div>
    </div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
