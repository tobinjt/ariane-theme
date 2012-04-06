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

// Copied from http://sedition.com/perl/javascript-fy.html
function fisherYates ( myArray ) {
  var i = myArray.length;
  if ( i == 0 ) return false;
  while ( --i ) {
     var j = Math.floor( Math.random() * ( i + 1 ) );
     var tempi = myArray[i];
     var tempj = myArray[j];
     myArray[i] = tempj;
     myArray[j] = tempi;
   }
}

function loggg(message) {
    console.log(new Date().toString() + message);
}

var image_dir = <?php echo "'" . get_bloginfo('template_directory') . '/slider/' . "'"; ?>;
var images = [
    // filename, width, height.  Improve this - it's terrible.
    ['fine-brooch.jpg',      '264', '648'],
    ['geometric-brooch.jpg', '404', '393'],
    ['gold-ring.jpg',        '399', '468'],
    ['pod-piece-1.jpg',      '800', '534'],
    ];
fisherYates(images);
images_to_preload = images.slice(1, images.length);
images_to_preload.push(images[0]);

var image_index = 0;
function change_image() {
    loggg('change_image called');
    loggg('height in change_image: ' + jQuery('#slider-div').css('height'));
    var margin_top = (parseInt(jQuery('#slider-div').css('height'))
                        - images[image_index][2]) / 2;
    loggg('margin_top in change_image: ' + margin_top);
    var image_url = image_dir + images[image_index][0];
    loggg('Displaying ' + image_url);
    jQuery('#slider-image').attr('src', image_url);
    jQuery('#slider-image').attr('width', images[image_index][1]);
    jQuery('#slider-image').attr('height', images[image_index][2]);
    jQuery('#slider-image').css('margin-top', margin_top);
    jQuery('#slider-div').css('width', images[image_index][1]);
    image_index = (image_index + 1) % images.length;
    loggg('change_image finished');
}

function preload_next_image() {
    loggg('preload_next_image called');
    if (images_to_preload.length) {
        var image_url = image_dir + images_to_preload.shift()[0];
        loggg('Preloading ' + image_url);
        var image = jQuery('<img />').attr('src', image_url);
    } else {
        loggg('Images are already preloaded.');
    }
    loggg('preload_next_image finished');
}

function fade_image_callback() {
    loggg('fade_image_callback called');
    change_image();
    jQuery('#slider-image').stop(true, true).fadeIn(
        1000, 'linear', preload_next_image);
    loggg('fade_image_callback finished');
}

function fade_image() {
    loggg('fade_image called');
    jQuery('#slider-image').stop(true, true).fadeOut(
        1000, 'linear', fade_image_callback);
    loggg('fade_image finished');
}

jQuery(document).ready(function() {
    loggg('slider setup started');
    if (jQuery('.single-page').has('#slider-image')) {
        loggg('We have a slider image!');
        var max_image_height = 0;
        // set div height.
        jQuery(images).each(function() {
            if (this[2] > max_image_height) {
                max_image_height = this[2];
            }
        });
        loggg('max_image_height = ' + max_image_height);
        jQuery('#slider-div').css('height', max_image_height);
        change_image();
        preload_next_image();
        // Update the image periodically.
        setInterval(fade_image, 5000);
        loggg('setInterval has been called');
    }
});

</script>

</body>

</html>
