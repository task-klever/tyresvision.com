/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
define(
    [
        'jquery',
        'uiRegistry',
        'MageWorx_OrderEditor/js/order/edit/form/account/modal',
        'MageWorx_OrderEditor/js/order/edit/form/base',
        'jquery/ui'
    ],
    function ($, uiRegistry) {
        'use strict';

        /**
         * Creating account editor widget
         */
        $.widget('mage.mageworxOrderEditorAccount', $.mage.mageworxOrderEditorBase, {
            params: {
                cancelButtonId: '#account-cancel',
                submitButtonId: '#account-submit',
                editLinkId: '#ordereditor-account-link',
                blockId: 'account',
                formId: '#account_edit_form',
                formContainerId: '.order-account-information',
                linkContainerId: '.admin__page-section-item-title',
                blockContainerId: '.admin__page-section-item-content',
                modalButton: '#select_customer',
                renderGridUrl: ''
            },

            /**
             * Initialize account edit widget
             *
             * @param params
             */
            init: function (params) {
                this.params = this._mergeParams(this.params, params);
                this._initParams(params);
                if (params.isAllowed){this._initActions();}
                uiRegistry.set('mageworxOrderEditorAccount', this);
            },

            /**
             * Main (critical) request parameters
             *
             * @returns {{form_key, order_id: *, block_id: string}}
             */
            getLoadFormParams: function () {
                return {
                    'form_key': FORM_KEY,
                    'order_id': this.getCurrentOrderId(),
                    'block_id': this.params.blockId
                };
            },

            /**
             * Load edit form from the backend and render it
             *
             * @private
             */
            _initloadEditForm: function () {
                var self = this;
                $(document).off('click', this.params.editLinkId);
                $(document).on('click', this.params.editLinkId, function () {
                    self.cancel();
                    var data = self.getLoadFormParams(),
                        el = $(this);

                    $.ajax({
                        url: self.params.loadFormUrl,
                        data: data,
                        type: 'post',
                        dataType: 'json',
                        beforeSend: function () {
                            self.showPreLoader();
                        },
                        success: function (response) {
                            if (!response.error && response.status == true) {
                                var $fieldsetParent = $(el).parent().parent();
                                $fieldsetParent.children(self.params.blockContainerId)
                                    .addClass('ordereditor-hidden-fieldset').hide();
                                $fieldsetParent.children(self.params.blockContainerId).after(
                                    "<div class='" +
                                    self.params.blockContainerId.substring(1) +
                                    " ordereditor-fieldset'></div>"
                                );
                                $fieldsetParent.children(".ordereditor-fieldset").html(response.result);

                                $(self.params.modalButton).modalForm({
                                    modalClass: 'select-a-customer-from-grid',
                                    title: $.mage.__('Please select a customer'),
                                    type: 'progress',
                                    renderGridUrl: self.params.renderGridUrl
                                });

                                self.hidePreLoader();
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            console.log(textStatus);
                            console.log(errorThrown);
                            self.throwError(
                                {
                                    "error": true,
                                    "message": textStatus
                                }
                            );
                            self.hidePreLoader();
                        }
                    });
                    return false;
                });
            },

            /**
             * Parent method overwriten:
             * obtain data from form to send them later to the backend to perform update order
             *
             * @returns {*}
             */
            getConfirmUpdateData: function () {
                var data = this.getLoadFormParams(),
                    formData = this.getFormData(this.params.formId);

                return this._mergeParams(data, formData);
            },

            /**
             * Form validation method
             *
             * @returns {jQuery}
             */
            validateForm: function () {
                return $(this.params.formId).validate().form();
            },
        });

        return $.mage.mageworxOrderEditorAccount;
    }
);

