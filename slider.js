'use strict';
// Javascript implementing the slider functionality.
// Flow of control:
// 1) Slider.initialise(images, id_prefix) is called, which sets up everything,
//    including displaying the first image and preloading the next image.
//    The page must contain the necessary HTML for images and links.
// 2) The last action taken by Slider.initialise() is to set up a once-off
//    function to be called in Slider.display_duration milliseconds.  This will
//    a) call Slider.fade_image_then_change_image() to rotate the slider the
//       first time, and will
//    b) call setInterval so that Slider.fade_image_then_change_image() is
//       called every Slider.rotation_duration milliseconds.
// 3) Slider.fade_image_then_change_image() causes the image to fade out over
//    Slider.fade_duration milliseconds, and sets
//    Slider.change_image_and_fade_in() as the callback.
// 4) Slider.change_image_and_fade_in() calls Slider.change_image() to change
//    the image.  Then it causes the image to fade in over Slider.fade_duration
//    milliseconds, and calls Slider.preload_next_image().
// 5) Slider.preload_next_image() will preload the next image if they haven't
//    all been preloaded.
// 6) Nothing happens for a while - the image is being displayed.
// 7) The interval timer calls Slider.fade_image_then_change_image() and steps
//    3-7 repeat forever.
//
// See http://usejsdoc.org/ for how to write the function docs.
// TODO: the steps above somewhat duplicate the function documentation,
// rationalise them.

/**
 * Initialise a SliderConf.
 *
 * @constructor
 * @param {Object} input_config - config containing id_prefix and other options.
 * @param {Object[]} images - images to display.  Other code needs each image
 *   to be an Object with 'width', 'height', and 'src' attributes.
 */
// One slider's configuration.
function SliderConf(input_config, images) {
  // Base values that can be overridden by input_config.
  // The time period for the image to fade in or out.
  this.fade_duration = 1000;
  // How long to fully display the image.
  this.display_duration = 3000;
  // id_prefix identifies the elements to change; id_prefix-div,
  // id_prefix-image, and id_prefix-link will be changed.
  this.id_prefix = 'id_prefix must be supplied in input_config';
  // Whether maybe_log() should log to console.
  this.log_to_console = false;

  for (var attrname in input_config) {
    this[attrname] = input_config[attrname];
  }

  // Derived values.
  // How long between slider rotations.
  this.rotation_duration = (
      this.display_duration + (this.fade_duration * 2));
  this.div_id = this.id_prefix + '-div';
  this.image_id = this.id_prefix + '-image';
  this.link_id = this.id_prefix + '-link';

  if (!jQuery(this.image_id).length) {
    alert('Did not find img id ' + this.image_id);
  }
  // The first image will already have been displayed, so make sure it's the
  // last image in the queue.
  this.images = images;
  var first_image = this.images.shift();
  // Shuffle the images and set up preloading.
  Slider.fisherYates(this.images);
  this.images.push(first_image);
  this.image_index = 0;

  // Copy the array because preloading modifies it inplace.
  this.images_to_preload = this.images.slice(0, this.images.length);
  this.last_log_date = new Date();
}

// Namespace for functions.
var Slider = {};

/**
 * Log a message plus timestamp to the console if config.log_to_console is true.
 *
 * @param {SliderConf} config - config to operate on.
 * @param {string} message - message to log.
 */
Slider.maybe_log = function(config, message) {
  if (config.log_to_console) {
    var date = new Date();
    var diff = date - config.last_log_date;
    console.log(config.id_prefix + ' ' + date.toString() + ' ' + diff +
                ' ' + message);
  }
};

/**
 * Set the base date for timestamp differences in maybe_log() to now.
 *
 * @param {SliderConf} config - config to operate on.
 */
Slider.set_log_date_to_now = function(config) {
  config.last_log_date = new Date();
};

/**
 * In-place random permutation of the input array.
 * https://en.wikipedia.org/wiki/Fisher%E2%80%93Yates_shuffle
 * Copied from http://sedition.com/perl/javascript-fy.html
 *
 * @param {Object[]} myArray - an array of anything.
 */
Slider.fisherYates = function(myArray) {
  var i = myArray.length;
  while (--i > 0) {
    var j = Math.floor(Math.random() * (i + 1));
    var tempi = myArray[i];
    var tempj = myArray[j];
    myArray[i] = tempj;
    myArray[j] = tempi;
  }
};

/**
 * Copy attributes from an image to an img object.
 *
 * @param {Object} image - a single image.
 * @param {Object} img - an img object.
 */

Slider.copy_attributes = function(image, img) {
  var attrs = ['height', 'width', 'src', 'srcset', 'sizes'];
  for (var i = 0; i < attrs.length; i++) {
    if (attrs[i] in image) {
      img.attr(attrs[i], image[attrs[i]]);
    }
  }
};

/**
 * Change the image that's displayed, placing it appropriately within the div,
 * updating the link target, and so on.  Doesn't do fading - caller needs to do
 * that.  Doesn't set up timeouts or callbacks or anything, again that's the
 * caller's responsibility.
 *
 * @param {SliderConf} config - config to operate on.
 */
Slider.change_image = function(config) {
  Slider.maybe_log(config, 'change_image called');
  var image = config.images[config.image_index];
  var img = jQuery(config.image_id);
  Slider.copy_attributes(image, img);
  if ('href' in image) {
    jQuery(config.link_id).attr('href', image.href);
  }
  config.image_index = (config.image_index + 1) % config.images.length;
  Slider.maybe_log(config, 'change_image returning');
};

/**
 * Preload the next image if there are any images left to preload.
 *
 * @param {SliderConf} config - config to operate on.
 */
Slider.preload_next_image = function(config) {
  Slider.maybe_log(config, 'preload_next_image called');
  if (config.images_to_preload.length) {
    var image = config.images_to_preload.shift();
    var img = jQuery('<img />');
    Slider.copy_attributes(image, img);
  }
  Slider.maybe_log(config, 'preload_next_image returning');
};

/**
 * Callback that should be invoked when fading an image completes.  Changes the
 * current image to the next image using Slider.change_image, then fades in the
 * new image, and finally preloads the next imagei while the current image is
 * fading in.
 * @param {SliderConf} config - config to operate on.
 */
Slider.change_image_and_fade_in = function(config) {
  Slider.maybe_log(config, 'change_image_and_fade_in called');
  Slider.change_image(config);
  jQuery(config.image_id).stop(true, true).fadeIn(
    config.fade_duration, 'linear');
  Slider.preload_next_image(config);
  Slider.maybe_log(config, 'change_image_and_fade_in returning');
};

/**
 * Fade out the current image with a callback to change_image_and_fade_in.
 *
 * @param {SliderConf} config - config to operate on.
 */
Slider.fade_image_then_change_image = function(config) {
  Slider.maybe_log(config, 'fade_image_then_change_image called');
  var callback = function() {
    Slider.change_image_and_fade_in(config);
  };
  jQuery(config.image_id).stop(true, true).fadeOut(
    config.fade_duration, 'linear', callback);
  Slider.maybe_log(config, 'fade_image_then_change_image returning');
};

/**
 * This function is called every config.rotation_duration milliseconds.  It
 * resets logging and calls fade_image_then_change_image.
 *
 * @param {SliderConf} config - config to operate on.
 */
Slider.periodic_callback = function(config) {
  Slider.set_log_date_to_now(config);
  Slider.maybe_log(config, 'periodic_callback called');
  Slider.fade_image_then_change_image(config);
  Slider.maybe_log(config, 'periodic_callback returning');
};

/**
 * Finish initialisation config.display_duration milliseconds after displaying
 * the first image.
 * - Trigger the first image change.
 * - Set up a timer to call periodic_callback regularly.
 *
 * @param {SliderConf} config - config to operate on.
 */
Slider.finish_initialisation = function(config) {
  Slider.maybe_log(config, 'finish_initialisation called');
  // Trigger the first image change.  Later image changes will be triggered by
  // periodic_callback.
  Slider.fade_image_then_change_image(config);
  var callback = function() {
    Slider.periodic_callback(config);
  };
  // Update the slider periodically.
  setInterval(callback, config.rotation_duration);
  Slider.maybe_log(config, 'finish_initialisation returning');
};

/** Initialise the slider:
 * - Create a SliderConf.
 * - Figure out the height and width of the div.
 * - Replace the placeholder div with the necessary elements.
 * - Display the first image.
 * - Preload the next image.
 * - Set up a timeout to call finish_initialisation.
 *
 * @param {Object} input_config - config containing id_prefix and other options.
 * @param {Object[]} images - images to display.
 */
Slider.initialise = function(input_config, images) {
  var config = new SliderConf(input_config, images);
  Slider.maybe_log(config, 'initialise called');

  // Front page slider doesn't use width and height because it uses srcset and
  // sizes.  Product page sliders do use width and height.
  if ('width' in images[0]) {
    var max_image_height = 0;
    var max_image_width = 0;
    // Set div height and width:
    // - We manually position the top of the image so they don't jump around too
    //   much.
    // - We want the div to have a consistent width so that content on either
    //   side doesn't jump around too much
    jQuery(config.images).each(function() {
      if (this.height > max_image_height) {
        max_image_height = this.height;
      }
      if (this.width > max_image_width) {
        max_image_width = this.width;
      }
    });
    jQuery(config.div_id
      ).css('height', max_image_height
      ).css('width', max_image_width);
  }

  Slider.preload_next_image(config);
  // Start the slider in display_duration seconds, because the images are only
  // fully displayed for display_duration seconds - the other seconds in
  // rotation_duration are fading in and fading out.
  var callback = function() {
    Slider.finish_initialisation(config);
  };
  setTimeout(callback, config.display_duration);
  Slider.maybe_log(config, 'initialise returning');
};
