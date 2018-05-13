/**
 * @file
 */

//---------------------------------------------------------------------------------------------------
//IIFE FUnction
(function ($, window, Drupal, drupalSettings) {
    //Attach behaviours
    Drupal.behaviors.tonner_worker = {
        attach: function (context, drupalSettings) {
            alert("I am in bitches!!!!");
        }
    };
})(jQuery, window, Drupal, drupalSettings);
;
