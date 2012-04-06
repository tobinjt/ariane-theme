		<footer id="footer" class="source-org vcard copyright">
			<small>&copy;<?php echo date("Y"); echo " "; bloginfo('name'); ?></small>
		</footer>

	</div>

	<?php wp_footer(); ?>

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

<?php
global $PAGE_CONTENT;
$LOAD_SLIDER = preg_match('/slider-image/', $PAGE_CONTENT);
?>
<?php
if ($LOAD_SLIDER) {
    // Dynamically build the Javascript array of images when displaying the
    // slider.
    $media_query = new WP_Query(
        array(
            'post_type'      => 'attachment',
            'post_status'    => 'any',
            'posts_per_page' => -1,
        )
    );
    echo "// URL, width, height\n";
    echo "var images = [\n";
    foreach ($media_query->posts as $post) {
        if (preg_match('/^\s*slider\s*$/', $post->post_content)) {
            $image_stats = wp_get_attachment_metadata($post->ID);
            echo "    ['"
                . wp_get_attachment_url($post->ID) . "', "
                . $image_stats['width'] . ', '
                . $image_stats['height']
                . "],\n";
        }
    }
    echo "];\n";
}
?>

<?php
// Only include the Javascript if we're actually displying the slider.
if ($LOAD_SLIDER) {
?>
// Copied from http://sedition.com/perl/javascript-fy.html
function fisherYates (myArray) {
  var i = myArray.length;
  if (i == 0) {
      return false;
  }
  while (--i) {
     var j = Math.floor(Math.random() * (i + 1));
     var tempi = myArray[i];
     var tempj = myArray[j];
     myArray[i] = tempj;
     myArray[j] = tempi;
   }
}

fisherYates(images);
var image_index = 0;
function change_image() {
    var margin_top = (parseInt(jQuery('#slider-div').css('height'))
                        - images[image_index][2]) / 2;
    var image_url = images[image_index][0];
    jQuery('#slider-image'
        ).attr('src', image_url
        ).attr('width', images[image_index][1]
        ).attr('height', images[image_index][2]
        ).css('margin-top', margin_top);
    jQuery('#slider-div').css('width', images[image_index][1]);
    image_index = (image_index + 1) % images.length;
}

images_to_preload = images.slice(1, images.length);
images_to_preload.push(images[0]);
function preload_next_image() {
    if (images_to_preload.length) {
        var image_url = images_to_preload.shift()[0];
        var image = jQuery('<img />').attr('src', image_url);
    } else {
    }
}

function fade_image_callback() {
    change_image();
    jQuery('#slider-image').stop(true, true).fadeIn(
        1000, 'linear', preload_next_image);
}

function fade_image() {
    jQuery('#slider-image').stop(true, true).fadeOut(
        1000, 'linear', fade_image_callback);
}

jQuery(document).ready(function() {
    if (jQuery('.single-page').has('#slider-image')) {
        var max_image_height = 0;
        // set div height.
        jQuery(images).each(function() {
            if (this[2] > max_image_height) {
                max_image_height = this[2];
            }
        });
        jQuery('#slider-div').css('height', max_image_height);
        change_image();
        preload_next_image();
        // Update the image periodically.
        setInterval(fade_image, 5000);
    }
});

<?php
}
?>

</script>

</body>

</html>
