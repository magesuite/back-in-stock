define([
    'jquery',
    'mage/mage',
    'loader'
], function($) {
    'use strict';

    /**
     * Back In Stock widget
     */

    $.widget('magesuite.backInStock', {
        options: {
            addToCartFormSelector: '#product_addtocart_form',
            ajaxResponseMessageWrapperSelector: '.cs-product-stock-subscription__msg .message',
            ajaxResponseMessageTextSelector: '.cs-messages__text',
            successClass: 'message-success success cs-messages__message--success',
            errorClass: 'message-error error cs-messages__message--error',
            loaderIcon: ''
        },

        _create: function() {
            var _self = this;

            this.$submitButton = this.element.find('button[type="submit"]');
            this.$responseElWrapper = $(this.options.ajaxResponseMessageWrapperSelector);
            this.$responseElText = this.$responseElWrapper.find(this.options.ajaxResponseMessageTextSelector);
            this.useLoader = this.options.loaderIcon !== '';
            this.$addToCartForm = $(this.options.addToCartFormSelector);

            // Create loader if needed (the one from Magento)
            if (this.useLoader) {
                this.element.loader({
                    icon: this.options.loaderIcon
                });
            }

            // Set submit handler for the form. Validation is initialized directly in template.
            this.element.mage('validation', {
                errorClass: 'mage-error',
                submitHandler: function(form, e) {
                    e.preventDefault();
                    _self._submitHandler();
                }
            });

            // Catch potential push notification event to trigger form submission
            $('body').on('push:subscribed', function() {
                this._submitHandler('push');
            }.bind(this));
        },

        /**
         * Collects form data.
         * Product form is merged to the back in stock form to get potential super_attributes
         * @param {String} notificationChannel - either push or email
         */
        _getFormData: function(notificationChannel) {
            var formData = new FormData(this.$addToCartForm[0]),
                $selectedOutOfStockSwatch = this.$addToCartForm.find('.bis-selected');

            if ($selectedOutOfStockSwatch.length) {
                var optionId = $selectedOutOfStockSwatch.data('option-id'),
                    attributeId = $selectedOutOfStockSwatch
                        .parents('.swatch-attribute')
                        .first()
                        .data('attribute-id');

                if (formData.get('super_attribute['+attributeId+']') !== null) {
                    formData.set('super_attribute['+attributeId+']', optionId);
                } else {
                    formData.append('super_attribute['+attributeId+']', optionId);
                }
            }

            // If no argument is passed, set default
            if (notificationChannel == null) {
                notificationChannel = 'email';
            }

            formData.append('notification_channel', notificationChannel);

            return formData;
        },

        /**
         * Form submission via AJAX
         * @param {String} notificationChannel - either push or email
         */
        _submitHandler: function(notificationChannel) {
            var formData = this._getFormData(notificationChannel);

            if (formData.get('notification_channel') === 'email') {
                $(this.element).find('input:not([type="hidden"])').each(function() {
                    formData.append($(this).attr('name'), $(this).val());
                });
            }

            $.ajax({
                method: 'POST',
                url: this.element.prop('action'),
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: this._beforeSend()
            }).done(function(response) {
                this._onDoneHandler(response);
            }.bind(this));
        },

        /**
         * Disable submit button to prevent multiple submissions and reset component state before ajax request
         */
        _beforeSend: function() {
            this.$submitButton.prop('disabled', true);

            if (this.useLoader) {
                this.element.loader('show');
            }

            if (this.$responseElWrapper.length) {
                this.$responseElWrapper.removeClass(this.options.successClass + this.options.errorClass);
            }

            if (this.$responseElText.length) {
                this.$responseElText.html('');
            }
        },

        /**
         * After AJAX request returned with data - place feedcback for user and reset form status
         * @param {object} response - ajax response
         */
        _onDoneHandler(response) {
            var feedbackClass = response.success ?
                this.options.successClass :
                this.options.errorClass;

            this.$submitButton.prop('disabled', false);

            if (this.useLoader) {
                this.element.loader('hide');
            }

            if (this.$responseElWrapper.length) {
                this.$responseElWrapper.addClass(feedbackClass);
            } else {
                alert(msg);
            }

            if (this.$responseElText.length) {
                this.$responseElText.html(response.message);
            }
        }
    });

    return $.magesuite.backInStock;
});
