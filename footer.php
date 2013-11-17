    <footer id="footer" class="source-org vcard copyright pink">
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
/* Set up toggling of the comment boxes. */
jQuery(document).ready(
  function (){
    // Toggle the comment box to leave a reply.
    var toggle_comment_box = function () {
      jQuery("#comment-form-container").toggle();
      jQuery("#cancel-comment-form").toggle();
      jQuery("#click-to-respond").toggle();
    }
    jQuery("#click-to-respond").click(toggle_comment_box);
    jQuery("#cancel-comment-form").click(toggle_comment_box);
  }
);

/* Set the width of an element based on the image inside it. */
jQuery(document).ready(
  function (){
    jQuery('.set-width-from-image').each(
      function() {
        var images = jQuery(this).find('img');
        var max_width = -1;
        for (var i = 0; i < images.length; i++) {
          var width = images[i].width;
          if (width > max_width) {
            max_width = width;
          }
        }
        if (max_width > 0) {
          jQuery(this).attr('style', 'max-width: ' + width + 'px');
        }
      });
  }
);
</script>

  </body>
</html>
