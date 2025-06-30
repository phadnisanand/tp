/**
 * @file
 * Custom JavaScript for Layout Builder tweaks.
 */

(function ($, Drupal, drupalSettings, once) { // <--- Add 'once' as a parameter here

  Drupal.behaviors.myLayoutBuilderTweaks = {
    attach: function (context, settings) {
      // Find the elements using the new once() function.
      // The 'once' utility expects a unique ID string, a selector string, and a context.
      // It returns a plain JavaScript array of DOM elements.
      const elementsToProcess = once('layoutBuilderPreviewOff', '#layout-builder-content-preview', context);
console.log('elementsToProcess', elementsToProcess);
      // Iterate over the elements returned by once().
      // You can use forEach for plain JS, or wrap them in jQuery if you need jQuery methods.
      $(elementsToProcess).each(function () { // <--- Wrap with $() to get a jQuery object
        const $previewCheckbox = $(this); // Get the current element as a jQuery object

        if ($previewCheckbox.length) {
          // Set the checkbox to unchecked.
          $previewCheckbox.prop('checked', false);
          // Trigger a 'change' event to ensure Layout Builder's internal JavaScript
          // reacts to this change and hides the preview content.
          $previewCheckbox.trigger('change');

          // Optional: If you want to visually hide the checkbox itself:
          $previewCheckbox.closest('.form-item').hide(); // Adjust selector as needed
        }
      });
    }
  };

})(jQuery, Drupal, drupalSettings, once); // <--- Pass 'once' as an argument here