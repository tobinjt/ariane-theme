<?php get_header(); ?>

    <div id="main-content">

        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

            <article class="single-page" id="post-<?php the_ID(); ?>">

                <h2><?php the_title(); ?></h2>

                <div class="entry">

                    <?php the_content(); ?>

                    <?php wp_link_pages(array('before' => 'Pages: ', 'next_or_number' => 'number')); ?>

                </div>

                <?php edit_post_link('Edit this entry.', '<p>', '</p>'); ?>

            </article>

        <?php endwhile; endif; ?>

    </div>

<?php get_footer(); ?>
