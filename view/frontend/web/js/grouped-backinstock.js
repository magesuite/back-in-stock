define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function($, modal, $t) {
    'use strict';

    /**
     * Wrap backinstock functionality into modal
     */
    $.widget('magesuite.backInStockModal', {
        options: {
            subscriptionFormClass: '.cs-product-stock-subscription',
            modalTriggerSelector: '.back-in-stock-modal-trigger',
            modalOptions: {
                type: 'popup',
                title: $t('Out of stock'),
                modalClass: 'cs-product-stock-subscription__modal',
                buttons: [],
            },
        },

        _init: function() {
            this.$subscriptionForm = $(this.options.subscriptionFormClass);
            this.$modalTrigger = $(this.options.modalTriggerSelector);
            this.$addToCartForm = $('#product_addtocart_form');

            if (this.$modalTrigger.length) {
                this._createBackInStockModal();
                this._setEvent();
            }
        },

        /**
         * Create a modal to be ready
         */
        _createBackInStockModal: function() {
            var popup = this.$subscriptionForm.modal(this.options.modalOptions);

            popup.on('modalclosed', function() {
                $('body').trigger('bis:modalclosed');
                this._resetForm();
            }.bind(this));
        },

        /**
         * Reset product input of add to cart form to default value
         */
        _resetForm: function() {
            this.$addToCartForm.find('input[name="simple_id"]').remove();
        },

        /**
         * Modify add-to-cart form to be able to send back-in-stock notification request for single product
         * Replace value of grouped product input with simple product id
         */
        _modifyForm: function(simpleId) {
            this.$addToCartForm.append('<input hidden type="text" name="simple_id" value="' + simpleId + '" />');
        },

        /**
         * On each (out of stock item click show modal
         */
        _setEvent: function() {
            var $widget = this;

            this.$modalTrigger.on('click', function(e) {
                e.preventDefault();
                var simpleId = $(this).parents('.associated-product').data('simple-id');
                $widget._modifyForm(simpleId);
                $widget.$subscriptionForm.modal('openModal');
            });
        }

    });

    return $.magesuite.backInStockModal;
});
