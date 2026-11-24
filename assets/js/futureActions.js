/******/ (() => { // webpackBootstrap
/*!***************************************!*\
  !*** ./assets/jsx/future-actions.jsx ***!
  \***************************************/
jQuery(document).ready(function ($) {
  var config = window.publishpressFutureActionsConfig || {};
  var closeLabel = config.closeLabel || (window.wp && window.wp.i18n ? window.wp.i18n.__('Close', 'post-expirator') : 'Close');
  var getDialogWidth = function getDialogWidth() {
    return Math.min(800, window.innerWidth - 32);
  };
  $(".publishpress-future-log-entries-popup").dialog({
    autoOpen: false,
    modal: true,
    width: getDialogWidth(),
    title: config.dialogTitle,
    buttons: [{
      text: closeLabel,
      click: function click() {
        $(this).dialog("close");
      }
    }]
  });
  var openLogDialog = function openLogDialog(opener) {
    var id = $(opener).data('id');
    var logElement = $.find(".publishpress-future-log-".concat(id));
    $(logElement).dialog("option", "width", getDialogWidth());
    $(logElement).dialog("open");
  };

  // Bound by class (no tag) so the opener works as an <a role="button"> or a <button>.
  $(document).on('click', '.publishpres-future-view-log', function (e) {
    e.preventDefault();
    openLogDialog(this);
  });

  // A link with role="button" only gets Enter natively; add Space (and handle Enter
  // here too). A real <button> already turns both keys into a click.
  $(document).on('keydown', '.publishpres-future-view-log', function (e) {
    if (this.tagName === 'BUTTON') {
      return;
    }
    if (e.key === ' ' || e.key === 'Spacebar' || e.key === 'Enter') {
      e.preventDefault();
      openLogDialog(this);
    }
  });
});
/******/ })()
;
//# sourceMappingURL=futureActions.js.map