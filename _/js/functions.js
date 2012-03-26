// remap jQuery to $
(function($){})(window.jQuery);


/* trigger when page is ready */
$(document).ready(function (){

	// your functions go here
    // Toggle the comment box to leave a reply.
    var toggle_comment_box = function () {
        $("#comment-form-container").toggle();
        $("#cancel-comment-form").toggle();
        $("#click-to-respond").toggle();
    }
    $("#click-to-respond").click(toggle_comment_box);
    $("#cancel-comment-form").click(toggle_comment_box);

});


/* optional triggers

$(window).load(function() {

});

$(window).resize(function() {

});

*/
