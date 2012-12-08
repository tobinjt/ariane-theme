<?php get_header(); ?>

    <div id="main-content-page">
<?php
    if (is_jewellery_page()) {
        echo '    <div id="jewellery-page">', "\n";
    }
?>

        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

            <article class="single-page" id="post-<?php the_ID(); ?>">

                <div class="entry">

<?php
// Save the page content so we can check for slider presence in the footer.
global $PAGE_CONTENT;
$PAGE_CONTENT = get_the_content();
echo $PAGE_CONTENT;
?>

                </div>

                <?php edit_post_link('Edit this entry.', '<p>', '</p>'); ?>

            </article>

        <?php endwhile; endif; ?>

<?php
    if (is_jewellery_page()) {
        echo '    </div>', "\n";
    }
?>
    </div>

<?php get_footer(); ?>
