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
                swatchAlertClass: 'back-in-stock-alert',
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
                this.canApplyMixin = this.$subscriptionForm.length;

                if (this.canApplyMixin) {
                    this._prepareBackInStockNotifications();
                    this._resetBiSFormOnOptionChange();

                    this._Rebuild();
                }
            },

            /**
             * On every _Rebuild reset css classes of bis-selected and collect all out of stock options and bind (fresh) Click event to all of them.
             */
            _Rebuild: function () {
                this._super();

                if (this.canApplyMixin) {
                    this.element.find('.bis-selected').removeClass('bis-selected');

                    this.$outOfStockOptions = this.element.find(this.options.outOfStockSwatchesSelector);

                    if (this.$outOfStockOptions.length) {
                        this.$outOfStockOptions
                            .addClass(this.options.swatchAlertClass)
                            .off('click');

                        this._setEvent();
                    }
                }
            },

            /**
             * Rewind options for controls.
             * Mixed to remove not only 'disabled' but also all BiS classes too
             */
            _Rewind: function (controls) {
                this._super(controls);

                if (this.canApplyMixin) {
                    controls.find('div[data-option-id], option[data-option-id]').removeClass(this.options.swatchAlertClass);
                    controls.find('div[data-option-empty], option[data-option-empty]').addClass(this.options.swatchAlertClass);
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
                this.element.on('click change', '.' + this.options.classes.optionClass, function() {
                    if (!this.options.showSubscriptionInModal) {
                        this.$subscriptionForm.addClass(this.options.subscriptionFormClass + '--hidden');
                    }
                }.bind(this));
            },

            /**
             * Custom method.
             * On each (out of stock) swatch click set proper CSS class (remove from siblings first) and show panel (optionally in modal)
             */
            _setEvent: function() {
                var $widget = this;

                this.$outOfStockOptions.on('click', function(e) {
                    e.preventDefault();

                    var $this = $(this),
                        $parent = $this.parents('.' + $widget.options.classes.attributeClass);

                    if ($parent.find('.' + $widget.options.classes.optionClass + '.selected').length) {
                        $parent.removeAttr('data-option-selected').find('.selected').removeClass('selected');
                        $parent.find('.' + $widget.options.classes.attributeInput).val('');
                        $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass).text('');
                        $this.attr('aria-checked', false);
                    }

                    $parent.find('.bis-selected').removeClass('bis-selected');
                    $this.addClass('bis-selected');

                    if ($widget.options.showSubscriptionInModal) {
                        $widget.$subscriptionForm.modal('openModal');
                    } else {
                        $widget.$subscriptionForm.removeClass($widget.options.subscriptionFormClass + '--hidden');
                    }
                });
            }
        });

        return $.magesuite.swatchRendererBackinstock;
    }
});