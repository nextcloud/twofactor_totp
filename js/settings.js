(function (OC) {
    'use strict';

    OC.Settings = OC.Settings || {};
    OC.Settings.TwoFactorTotp = OC.Settings.TwoFactorTotp || {};

    $(function () {
        var view = new OC.Settings.TwoFactorTotp.View({
            el: $('#twofactor-totp-settings')
        });
        view.render();
    });
})(OC);

