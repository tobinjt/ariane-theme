// Javascript implementing the slider functionality.
// Flow of control:
// 1) Slider.initialise(images, id_prefix) is called, which sets up everything,
//    including displaying the first image and preloading the next image.
//    The page must contain a single <div> with the name id_prefix + '-div'.
// 2) The last action taken by Slider.initialise() is to set up a once-off
//    function to be called in Slider.display_duration milliseconds.  This will
//    a) call Slider.fade_image() to rotate the slider the first time, and will
//    b) call setInterval so that Slider.fade_image() is called every
//       Slider.rotation_duration milliseconds.
// 3) Slider.fade_image() causes the image to fade out over Slider.fade_duration
//    milliseconds, and sets Slider.fade_image_callback() as the callback.
// 4) Slider.fade_image_callback() calls Slider.change_image() to change the
//    image.  Then it causes the image to fade in over Slider.fade_duration
//    milliseconds, and sets Slider.preload_next_image() as the callback.
// 5) Slider.preload_next_image() will preload the next image if they haven't
//    all been preloaded.
// 6) Nothing happens for a while - the image is being displayed.
// 7) The interval timer calls Slider.fade_image() and steps 3-7 repeat forever.

// One slider's configuration.
function SliderConf(images, id_prefix) {
  // The time period for the image to fade in or out.
  this.fade_duration = 1000;
  // How long to fully display the image.
  this.display_duration = 3000;
  // How long between slider rotations.
  this.rotation_duration = (
      this.display_duration + (this.fade_duration * 2));
  // id_prefix identifies the elements to change; id_prefix-div,
  // id_prefix-image, and id_prefix-link will be changed.
  this.id_prefix = id_prefix;
  this.images = images;
  // Shuffle the images and set up preloading.
  Slider.fisherYates(this.images);
  this.image_index = 0;
  // Slider.initialise will load the 0th image, so we need to start preloading
  // with the 1st image.
  this.images_to_preload = this.images.slice(1, this.images.length);
}

// Namespace for functions.
var Slider = {};

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

// config is a SliderConf.
Slider.change_image = function(config) {
  var margin_top = (parseInt(jQuery(config.id_prefix + '-div').css('height'))
                      - config.images[config.image_index]['height']) / 2;
  jQuery(config.id_prefix + '-image'
    ).attr('src', config.images[config.image_index]['image_url']
    ).attr('width', config.images[config.image_index]['width']
    ).attr('height', config.images[config.image_index]['height']
    // Limit the margin so that smaller images aren't pushed below the fold.
    ).css('margin-top', Math.min(margin_top, 100));
  jQuery(config.id_prefix + '-link'
    ).attr('href', config.images[config.image_index]['link_url']);
  jQuery(config.id_prefix + '-div'
    ).css('width', config.images[config.image_index]['width']);
  config.image_index = (config.image_index + 1) % config.images.length;
};

Slider.preload_next_image = function(config) {
  if (config.images_to_preload.length) {
    var image_url = config.images_to_preload.shift()['image_url'];
    var image = jQuery('<img />').attr('src', image_url);
  }
};

Slider.fade_image_callback = function(config) {
  Slider.change_image(config);
  callback = function() {
    Slider.preload_next_image(config);
  };
  jQuery(config.id_prefix + '-image').stop(true, true).fadeIn(
    config.fade_duration, 'linear', callback);
};

Slider.fade_image = function(config) {
  callback = function() {
    Slider.fade_image_callback(config);
  };
  jQuery(config.id_prefix + '-image').stop(true, true).fadeOut(
    Slider.fade_duration, 'linear', callback);
};

// Each element in the array must be a dictionary with elements:
// image_url, width, height.
Slider.initialise = function(images, id_prefix) {
  config = new SliderConf(images, id_prefix);
  var max_image_height = 0;
  // Set div height.
  jQuery(config.images).each(function() {
    if (this['height'] > max_image_height) {
      max_image_height = this['height'];
    }
  });
  jQuery(config.id_prefix + '-div').css('height', max_image_height);
  id_prefix_no_hash = config.id_prefix.replace('#', '');
  jQuery(config.id_prefix + '-div').append(
      '<a id="' + id_prefix_no_hash + '-link" href="#">'
      + '<img id="' + id_prefix_no_hash + '-image" alt="Slider placeholder" />'
      + '</a>');
  Slider.change_image(config);
  Slider.preload_next_image(config);
  // Start the slider in 3 seconds, because the images are only fully displayed
  // for 3 seconds - the other 2 seconds are fading in and fading out.
  setTimeout(
    function() {
      Slider.fade_image(config);
      callback = function() {
        Slider.fade_image(config);
      };
      // Update the slider periodically.
      setInterval(callback, config.rotation_duration);
    }, config.display_duration);
};
