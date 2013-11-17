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
