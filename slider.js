// Javascript implementing the slider functionality.
// Flow of control:
// 1) Slider.initialise() is called, which sets up everything except the image
//    array.
// 2) The last action taken by Slider.initialise() is to set up a once-off
//    function to be called in Slider.display_duration milliseconds.  This will
//    a) call Slider.fade_image() to rotate the slider the first time, and will
//    b) call setInterval so that Slider.fade_image() is called every
//    Slider.rotation_duration milliseconds.
// 3) Slider.fade_image() causes the image to fade out over Slider.fade_duration
//    milliseconds, and sets Slider.fade_image_callback() as the callback.
// 4) Slider.fade_image_callback() calls Slider.change_image() to change the
//    image.  Then it causes the image to fade in over Slider.fade_duration
//    milliseconds, and sets Slider.preload_next_image() as the callback.
// 5) Slider.preload_next_image() will preload the next image if they haven't
//    all been preloaded.
// 6) Nothing happens for a while - the image is being displayed.
// 7) The interval timer calls Slider.fade_image() and steps 3-7 repeat forever.

var Slider = {};
// The time period for the image to fade in or out.
Slider.fade_duration = 1000;
// How long to fully display the image.
Slider.display_duration = 3000;
// How long between slider rotations.
Slider.rotation_duration = (
    Slider.display_duration + Slider.fade_duration + Slider.fade_duration);

// Copied from http://sedition.com/perl/javascript-fy.html
Slider.fisherYates = function(myArray) {
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
};

Slider.change_image = function() {
  var margin_top = (parseInt(jQuery('#slider-div').css('height'))
                      - Slider.images[Slider.image_index]['height']) / 2;
  // Limit the margin so that smaller images aren't pushed below the fold.
  var bounded_margin_top = Math.min(margin_top, 100);
  var image_url = Slider.images[Slider.image_index]['image_url'];
  var link_url = Slider.images[Slider.image_index]['link_url'];
  jQuery('#slider-image'
    ).attr('src', image_url
    ).attr('width', Slider.images[Slider.image_index]['width']
    ).attr('height', Slider.images[Slider.image_index]['height']
    ).css('margin-top', bounded_margin_top);
  jQuery('#slider-link'
    ).attr('href', link_url);
  jQuery('#slider-div').css('width', Slider.images[Slider.image_index]['width']);
  Slider.image_index = (Slider.image_index + 1) % Slider.images.length;
};

Slider.preload_next_image = function() {
  if (Slider.images_to_preload.length) {
    var image_url = Slider.images_to_preload.shift()['image_url'];
    var image = jQuery('<img />').attr('src', image_url);
  }
};

Slider.fade_image_callback = function() {
  Slider.change_image();
  jQuery('#slider-image').stop(true, true).fadeIn(
    Slider.fade_duration, 'linear', Slider.preload_next_image);
};

Slider.fade_image = function() {
  jQuery('#slider-image').stop(true, true).fadeOut(
    Slider.fade_duration, 'linear', Slider.fade_image_callback);
};

// Each element in the array must be a dictionary with elements:
// image_url, width, height.
Slider.initialise = function(images) {
  Slider.images = images;
  Slider.fisherYates(Slider.images);
  Slider.image_index = 0;
  // Slider.change_image will load the 0th image, so we need to preload the 1st
  // image.
  Slider.images_to_preload = Slider.images.slice(1, Slider.images.length);
  var max_image_height = 0;
  // Set div height.
  jQuery(Slider.images).each(function() {
    if (this['height'] > max_image_height) {
      max_image_height = this['height'];
    }
  });
  jQuery('#slider-div').css('height', max_image_height);
  jQuery('#slider-div').append(
      '<a id="slider-link" href="#">'
      + '<img id="slider-image" alt="Slider placeholder" />'
      + '</a>');
  Slider.change_image();
  Slider.preload_next_image();
  // Start the slider in 3 seconds, because the images are only fully displayed
  // for 3 seconds - the other 2 seconds are fading in and fading out.
  setTimeout(
    function() {
      Slider.fade_image();
      // Update the slider periodically.
      setInterval(Slider.fade_image, Slider.rotation_duration);
    }, Slider.display_duration);
};
