jQuery(document).ready(function ($) {
    const config = window.publishpressFutureActionsConfig || {};

    const closeLabel = config.closeLabel
        || (window.wp && window.wp.i18n ? window.wp.i18n.__('Close', 'post-expirator') : 'Close');

    const getDialogWidth = () => Math.min(800, window.innerWidth - 32);

    $(".publishpress-future-log-entries-popup").dialog(
        {
            autoOpen: false,
            modal: true,
            width: getDialogWidth(),
            title: config.dialogTitle,
            buttons: [
                {
                    text: closeLabel,
                    click: function () {
                        $(this).dialog("close");
                    }
                }
            ]
        }
    );

    const openLogDialog = function (opener) {
        const id = $(opener).data('id');
        const logElement = $.find(`.publishpress-future-log-${id}`);

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
