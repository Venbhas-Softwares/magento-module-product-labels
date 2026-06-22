define([
    'Magento_Ui/js/form/element/select',
    'uiRegistry'
], function (Select, registry) {
    'use strict';

    return Select.extend({
        defaults: {
            listens: {
                value: 'toggleDependentFields'
            }
        },

        /**
         * Toggle text/discount fields when label type changes.
         */
        toggleDependentFields: function () {
            var type = this.value(),
                textField = registry.get('index = text_content'),
                discountField = registry.get('index = discount_display');

            if (textField) {
                textField.visible(type === 'text');
            }
            if (discountField) {
                discountField.visible(type === 'discount');
            }
        },

        /**
         * @inheritdoc
         */
        setInitialValue: function () {
            this._super();
            this.toggleDependentFields();
            return this;
        }
    });
});
