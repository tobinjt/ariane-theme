'use strict';
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
//
// See http://usejsdoc.org/ for how to write the function docs.
// TODO: the steps above somewhat duplicate the function documentation,
// rationalise them.
// TODO: think about the overall process and whether it can be simplified.

/**
 * Initialise a SliderConf.
 *
 * @constructor
 * @param {Object[]} images - images to display.  Other code needs each image
 *   to be an Object with 'width', 'height', and 'image_url' attributes.
 * @param {string} id_prefix - prefix of CSS id of each element accessed by this
 *   code.
 * @param {bool} images_are_links - whether images should link to pages.
 */
// One slider's configuration.
function SliderConf(images, id_prefix, images_are_links) {
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
  this.images_are_links = images_are_links;
  this.div_id = id_prefix + '-div';
  this.image_id = id_prefix + '-image';
  this.link_id = id_prefix + '-link';
  this.images = images;
  // Shuffle the images and set up preloading.
  Slider.fisherYates(this.images);
  this.image_index = 0;
  // Slider.initialise will load the 0th image, so we need to start preloading
  // with the 1st image.
  this.images_to_preload = this.images.slice(1, this.images.length);
  // Whether maybe_log() should log to console.
  this.log_to_console = false;
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
    console.log(date.toUTCString() + ' ' + diff + ' ' + message);
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
 * Change the image that's displayed, placing it appropriately within the div,
 * updating the link target, and so on.  Doesn't do fading - caller needs to do
 * that.  Doesn't set up timeouts or callbacks or anything, again that's the
 * caller's responsibility.
 *
 * @param {SliderConf} config - config to operate on.
 */
Slider.change_image = function(config) {
  Slider.maybe_log(config, 'change_image called');
  var margin_top = (parseInt(jQuery(config.div_id).css('height')) -
                      config.images[config.image_index].height) / 2;
  var image = config.images[config.image_index];
  jQuery(config.image_id
    ).attr('src', image.image_url
    ).attr('width', image.width
    ).attr('height', image.height
    // Limit the margin so that smaller images aren't pushed below the fold.
    ).css('margin-top', Math.min(margin_top, 100));
  if (config.images_are_links) {
    jQuery(config.link_id).attr('href', image.link_url);
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
    var image_url = config.images_to_preload.shift().image_url;
    var image = jQuery('<img />').attr('src', image_url);
  }
  Slider.maybe_log(config, 'preload_next_image returning');
};

/**
 * Callback that should be invoked when fading an image completes.  Changes the
 * current image to the next image using Slider.change_image, then fades in the
 * new image with a callback that preloads the next image.
 * @param {SliderConf} config - config to operate on.
 */
Slider.fade_image_callback = function(config) {
  Slider.maybe_log(config, 'fade_image_callback called');
  Slider.change_image(config);
  var callback = function() {
    Slider.maybe_log(config, 'fade_image_callback    callback called');
    Slider.preload_next_image(config);
    Slider.maybe_log(config, 'fade_image_callback    callback returning');
  };
  jQuery(config.image_id).stop(true, true).fadeIn(
    config.fade_duration, 'linear', callback);
  Slider.maybe_log(config, 'fade_image_callback returning');
};

/**
 * Fade out the current image with a callback to fade_image_callback.
 *
 * @param {SliderConf} config - config to operate on.
 */
Slider.fade_image = function(config) {
  Slider.maybe_log(config, 'fade_image called');
  var callback = function() {
    Slider.maybe_log(config, 'fade_image    callback called');
    Slider.fade_image_callback(config);
    Slider.maybe_log(config, 'fade_image    callback returning');
  };
  jQuery(config.image_id).stop(true, true).fadeOut(
    config.fade_duration, 'linear', callback);
  Slider.maybe_log(config, 'fade_image returning');
};

/**
 * This function is called every config.rotation_duration milliseconds.  It
 * resets logging and calls fade_image.
 *
 * @param {SliderConf} config - config to operate on.
 */
Slider.periodic_callback = function(config) {
  Slider.set_log_date_to_now(config);
  Slider.maybe_log(config, 'periodic_callback called');
  Slider.fade_image(config);
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
  Slider.fade_image(config);
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
 * @param {Object[]} images - images to display.  Other code needs each image
 *   to be an Object with 'width', 'height', and 'image_url' attributes.
 * @param {string} id_prefix - prefix of CSS id of each element accessed by this
 *   code.
 * @param {bool} images_are_links - whether images should link to pages.
 */
Slider.initialise = function(images, id_prefix, images_are_links) {
  var config = new SliderConf(images, id_prefix, images_are_links);
  Slider.maybe_log(config, 'initialise called');
  var max_image_height = 0;
  var max_image_width = 0;
  // Set div height and width:
  // - We manually position the top of the image so they don't jump around too
  //   much.
  // - We want the div to have a consistent width so that content on either side
  //   doesn't jump around too much
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
  var img = jQuery('<img>', {
    'id': config.image_id.replace('#', ''),
    'href': '#'
  });
  if (config.images_are_links) {
    var link = jQuery('<a>', {
      'id': config.link_id.replace('#', ''),
      'alt': 'Slider placeholder'
    });
    link.append(img);
    jQuery(config.div_id).append(link);
  } else {
    jQuery(config.div_id).append(img);
  }
  Slider.change_image(config);
  Slider.preload_next_image(config);
  // Start the slider in 3 seconds, because the images are only fully displayed
  // for 3 seconds - the other 2 seconds are fading in and fading out.
  var callback = function() {
    Slider.finish_initialisation(config);
  };
  setTimeout(callback, config.display_duration);
  Slider.maybe_log(config, 'initialise returning');
};
