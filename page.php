<?php get_header(); ?>

    <div id="main-content-page">

        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

            <article class="single-page" id="post-<?php the_ID(); ?>">

                <div class="entry">

                    <?php the_content(); ?>

                </div>

                <?php edit_post_link('Edit this entry.', '<p>', '</p>'); ?>

            </article>

        <?php endwhile; endif; ?>

    </div>

<?php get_footer(); ?>
