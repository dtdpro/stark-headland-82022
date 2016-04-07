/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function ($) {
    $(document).ready(function () {
        hljs.initHighlightingOnLoad();

        // Datetime picker initialization.
        // See http://eonasdan.github.io/bootstrap-datetimepicker/
        $('[data-toggle="datetimepicker"]').datetimepicker({
            icons: {
                time: 'fa fa-clock-o',
                date: 'fa fa-calendar',
                up: 'fa fa-chevron-up',
                down: 'fa fa-chevron-down',
                previous: 'fa fa-chevron-left',
                next: 'fa fa-chevron-right',
                today: 'fa fa-check-circle-o',
                clear: 'fa fa-trash',
                close: 'fa fa-remove'
            },
            format: 'LLL'
        });
    });

    // Handling the modal confirmation message.
    $(document).on('submit', 'form[data-confirmation]', function (event) {
        var $form = $(this),
            $confirm = $('#confirmationModal');

        if ($confirm.data('result') !== 'yes') {
            //cancel submit event
            event.preventDefault();

            $confirm
                .off('click', '#btnYes')
                .on('click', '#btnYes', function () {
                    $confirm.data('result', 'yes');
                    $form.find('input[type="submit"]').attr('disabled', 'disabled');
                    $form.submit();
                })
                .modal('show');
        }
    });
})(window.jQuery);

var loadingDialog = loadingDialog || (function ($) {
        'use strict';

        // Creating modal dialog's DOM
        var $dialog = $(
            '<div class="modal fade" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true" style="padding-top:15%; overflow-y:visible;">' +
            '<div class="modal-dialog modal-sm"><div class="modal-content">' +
            '<div class="modal-header"><h3 class="text-center" style="margin:0;">Loading...</h3></div>' +
            '<div class="modal-body text-center"><i class="fa fa-spinner fa-spin fa-5x"></i></div>' +
            '</div></div>');

        return {
            /**
             * Opens our dialog
             * @param options Custom options:
             */
            show: function (options) {
                // Assigning defaults
                if (typeof options === 'undefined') {
                    options = {};
                }
                var settings = $.extend({
                    onHide: null // This callback runs after the dialog was hidden
                }, options);

                // Adding callbacks
                if (typeof settings.onHide === 'function') {
                    $dialog.off('hidden.bs.modal').on('hidden.bs.modal', function (e) {
                        settings.onHide.call($dialog);
                    });
                }
                // Opening dialog
                $dialog.modal();
            },
            /**
             * Closes dialog
             */
            hide: function () {
                $dialog.modal('hide');
            }
        };

    })(jQuery);
