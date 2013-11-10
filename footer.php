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
  if (preg_match('/slider-image/', $PAGE_CONTENT)) {
    // Only include the Javascript if we're actually displaying the slider.
    echo SliderSetup();
  }
?>
  </body>
</html>
