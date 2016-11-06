(function (OC) {
    'use strict';

    OC.Admin = OC.Admin || {};
    OC.Admin.TwoFactorTotp = OC.Admin.TwoFactorTotp || {};


    $(function(){
        var parent = $('#section-totp');

        parent.find('input[name=totp_type]').change(function(t){
            if((t=$(this)).is(':checked')){
                var url = OC.generateUrl('/apps/twofactor_totp/settings/setAdminConfig');
                var loading = $.ajax(url, {
                    method: 'POST',
                    data: {
                        type: t.val()
                    }
                });
            }
        });


        /*
        var url = OC.generateUrl('/apps/twofactor_totp/settings/getAdminConfig');
        var loading = $.ajax(url, {
            method: 'GET',
        });

        var _this = this;
        $.when(loading).done(function (data) {
            console.log(data);
        });

        $.when(loading).always(function () {
            _this._loading = false;
        });
        */
    })
})(OC);
