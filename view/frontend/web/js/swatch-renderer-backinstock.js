define([
    'jquery', 
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function($, modal, $t) {
    'use strict';

    /**
     * Extend swatches to inject some actions for back in stock alert
     */

    return function(swatchRenderer) {
        $.widget('magesuite.swatchRendererBackinstock', swatchRenderer, {
            options: {
                outOfStockSwatchesSelector: '.swatch-option[disabled]',
                showSubscriptionInModal: false,
                subscriptionFormClass: 'cs-product-stock-subscription',
                swatchClass: 'back-in-stock-alert',
                modalOptions: {
                    type: 'popup',
                    title: $t('Out of stock'),
                    modalClass: 'cs-product-stock-subscription__modal',
                    buttons: [],
                },
            },

            _init: function() {
                this._super();

                this.$subscriptionForm = $('.' + this.options.subscriptionFormClass);

                if (this.$subscriptionForm.length) {
                    this._prepareBackInStockNotifications();
                    this._resetBiSFormOnOptionChange();

                    this._Rebuild();
                }
            },

            /**
             * On every _Rebuild collect all out of stock options and bind (fresh) Click event to all of them 
             */
            _Rebuild: function () {
                this._super();
                
                this.$outOfStockOptions = this.element.find(this.options.outOfStockSwatchesSelector);

                if (this.$outOfStockOptions.length) {
                    this.$outOfStockOptions
                        .addClass(this.options.swatchClass)
                        .off('click');

                    this._setEvent();
                }
            },

            /**
             * Custom method.
             * If (newly introduced) showSubscriptionInModal option is true, create a modal to be ready
             */
            _prepareBackInStockNotifications: function() {
                var $form = this.$subscriptionForm,
                    popup;

                if (this.options.showSubscriptionInModal) {
                    popup = modal(this.options.modalOptions, $form);

                    $form.on('modalclosed', function() {
                        $form.removeClass('active');
                    });
                }
            },

            /**
             * Custom method
             * If (newly introduced) showSubscriptionInModal option is FALSE, on each change of value for any super attribute make sure Subscription Panel is closed until it's needed.
             */
            _resetBiSFormOnOptionChange: function() {
                var _self = this;

                this.element.on('click change', '.' + this.options.classes.optionClass, function() {
                    if (!_self.options.showSubscriptionInModal) {
                        _self.$subscriptionForm.addClass(_self.options.subscriptionFormClass + '--hidden');
                    }
                });
            },

            /**
             * Custom method.
             * On each (out of stock) swatch click set proper CSS class and show panel (optionally in modal)
             */
            _setEvent: function() {
                var _self = this;

                this.$outOfStockOptions.on('click', function(e) {
                    e.preventDefault();
                    $(this).parent().children().removeClass('bis-selected');
                    $(this).addClass('bis-selected');

                    if (_self.options.showSubscriptionInModal) {
                        _self.$subscriptionForm.modal('openModal');
                    } else {
                        _self.$subscriptionForm.removeClass(_self.options.subscriptionFormClass + '--hidden');
                    }
                });
            }
        });

        return $.magesuite.swatchRendererBackinstock;
    }
});