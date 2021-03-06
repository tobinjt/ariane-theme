<?php
  if (!empty($_SERVER['SCRIPT_FILENAME'])
      && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME'])) {
    die('Please do not load this page directly. Thanks!');
  }

  if (post_password_required()) {
    echo '<p>This post is password protected.';
    echo 'Enter the password to view comments.</p>';
    return;
  }

  if (have_comments()) {
?>
  <h2 id="comments"><?php comments_number('No Responses', 'One Response', '% Responses');?></h2>
  <div class="navigation">
    <div class="next-posts"><?php previous_comments_link(); ?></div>
    <div class="prev-posts"><?php next_comments_link(); ?></div>
  </div>

  <ol class="commentlist">
    <?php wp_list_comments(); ?>
  </ol>

  <div class="navigation">
    <div class="next-posts"><?php previous_comments_link(); ?></div>
    <div class="prev-posts"><?php next_comments_link(); ?></div>
  </div>
 <?php
  }

  if (!comments_open()) {
    echo '  <p>Comments are closed.</p>';
    return;
  }
?>
  <div id="respond">
    <button id="click-to-respond">
      <?php comment_form_title('Leave a Reply', 'Leave a Reply to %s'); ?>
    </button>
    <button id="cancel-comment-form">
      Hide comment form
    </button>
    <div id="comment-form-container">
      <div class="cancel-comment-reply">
        <?php cancel_comment_reply_link(); ?>
      </div>

<?php
  if (get_option('comment_registration') && !is_user_logged_in()) {
?>
      <p>You must be <a href="<?php echo wp_login_url(get_permalink());?>">
      logged in</a> to post a comment.</p>'
<?php
  } else {
?>
      <form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php"
        method="post" id="commentform">
<?php
    if (is_user_logged_in()) {
?>
        <p>Logged in as <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php">
          <?php echo $user_identity; ?></a>.
          <a href="<?php echo wp_logout_url(get_permalink()); ?>"
            title="Log out of this account">Log out</a>
        </p>
<?php
    } else {
?>
          <div>
            <input type="text" name="author" id="author" size="22" tabindex="1"
              value="<?php echo esc_attr($comment_author); ?>" />
            <label for="author">Name (required)</label>
          </div>

          <div>
            <input type="text" name="email" id="email"  size="22" tabindex="2"
              value="<?php echo esc_attr($comment_author_email); ?>" />
            <label for="email">Email address (required; will not be published)</label>
          </div>

          <div>
            <input type="text" name="url" id="url" size="22" tabindex="3"
              value="<?php echo esc_attr($comment_author_url); ?>" />
            <label for="url">Website</label>
          </div>
<?php
    }
?>

        <div>
          <textarea name="comment" id="comment" cols="58" rows="10" tabindex="4"></textarea>
        </div>

        <div>
          <input name="submit" type="submit" id="submit" tabindex="5" value="Submit Comment" />
          <?php comment_id_fields(); ?>
        </div>

        <?php do_action('comment_form', $post->ID); ?>

      </form>
    </div>

<?php
  } // If registration required and not logged in
?>
  </div>
