define([
    'ko'
], function (ko) {
    'use strict';

    var mixin = {

        initialize: function () {
            // set visible to be initially false to have your step show first
            this.visible = ko.observable(false);
            this._super();

            return this;
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});