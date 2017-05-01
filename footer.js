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
</script>
