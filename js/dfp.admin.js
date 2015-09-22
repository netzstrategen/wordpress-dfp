(function ($, inlineEditTax) {
  // inlineEditTax does not invoke any events, but does ensure to stop
  // propagation to all other event handlers; swap it out.
  inlineEditTax.editPreDfp = inlineEditTax.edit;
  inlineEditTax.edit = function (link) {
    // Invoke original edit event handler.
    this.editPreDfp(link);

    var $outputContext = $(link).closest('tr[id]');
    var $inputContext = $outputContext.siblings('.inline-edit-row');
    $inputContext.find('[data-dfp-provider]').each(function () {
      var providerId = $(this).data('dfp-provider');
      var $oldValue = $outputContext.find('[data-dfp-provider="' + providerId + '"][data-dfp-unit]');
      if ($oldValue.length) {
        $(this).val($oldValue.data('dfp-unit'));
      }
    });
    return false;
  }
})(jQuery, inlineEditTax);
