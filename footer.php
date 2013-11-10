    <footer id="footer" class="source-org vcard copyright">
      <span>&copy;2011-<?php echo date("Y"); echo " ", strtolower(get_bloginfo('name')); ?></span>
      <span>phone: 00353 86 834 6825</span>
      <span>email: ariane at arianetobin.ie</span>
    </footer>

  </div>
<?php
  if (current_user_can('edit_pages')) {
    echo <<<VALIDATOR
    <div><a href="http://validator.w3.org/check?uri=referer">Validate</a></div>
VALIDATOR;
  }
  wp_footer();
?>

<script type="text/javascript">
/* trigger when page is ready */
jQuery(document).ready(function (){
  // Toggle the comment box to leave a reply.
  var toggle_comment_box = function () {
    jQuery("#comment-form-container").toggle();
    jQuery("#cancel-comment-form").toggle();
    jQuery("#click-to-respond").toggle();
  }
  jQuery("#click-to-respond").click(toggle_comment_box);
  jQuery("#cancel-comment-form").click(toggle_comment_box);
});
</script>

<?php
global $PAGE_CONTENT;
$LOAD_SLIDER = preg_match('/slider-image/', $PAGE_CONTENT);
if ($LOAD_SLIDER) {
  // Only include the Javascript if we're actually displaying the slider.
  echo "<script type='text/javascript' src='",
    get_bloginfo('template_directory'), "/slider.js",
    "'></script>", "\n";
  // Dynamically build the Javascript array of images when displaying the
  // slider.
  $media_query = new WP_Query(
    array(
      'post_type'      => 'attachment',
      'post_status'    => 'any',
      'posts_per_page' => -1,
    )
  );
  echo '<script type="text/javascript">', "\n";
  echo "// URL, width, height\n";
  echo "var images = [\n";
  foreach ($media_query->posts as $post) {
    if (preg_match('/^\s*slider\s*$/', $post->post_content)) {
      $image_stats = wp_get_attachment_metadata($post->ID);
      // Escaping?
      $url = wp_get_attachment_url($post->ID);
      if ($url && $image_stats
          && $image_stats['width'] && $image_stats['height']) {
        echo "    ['"
          . $url . "', "
          . $image_stats['width'] . ', '
          . $image_stats['height']
          . "],\n";
      }
    }
  }
  echo "];\n";
?>

// The images array ends with a comma, and IE 8 adds a null or undefined
// element after the comma, so we remove that element.
if (images[images.length - 1] === null
      || images[images.length - 1] === undefined) {
  images.pop();
}
<?php
}
?>
</script>
  </body>
</html>
