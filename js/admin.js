(function (OC) {
    'use strict';

    OC.Admin = OC.Admin || {};
    OC.Admin.TwoFactorTotp = /*OC.Admin.TwoFactorTotp || */{
        /**
         * Setup selection box for group selection.
         *
         * Values need to be separated by a pipe "|" character.
         * (mostly because a comma is more likely to be used
         * for groups)
         *
         * @param $elements jQuery element (hidden input) to setup select2 on
         * @param {Array} [extraOptions] extra options hash to pass to select2
         * @param {Array} [options] extra options
         * @param {Array} [options.excludeAdmins=false] flag whether to exclude admin groups
         */
        setupGroupsSelect: function($elements, extraOptions, options) {
            var self = this;
            options = options || {};
            if ($elements.length > 0) {
                // note: settings are saved through a "change" event registered
                // on all input fields
                $elements.select2(_.extend({
                    placeholder: t('core', 'Groups'),
                    allowClear: true,
                    multiple: true,
                    separator: '|',
                    query: _.debounce(function(query) {
                        var queryData = {};
                        if (self._cachedGroups && query.term === '') {
                            query.callback({results: self._cachedGroups});
                            return;
                        }
                        if (query.term !== '') {
                            queryData = {
                                pattern: query.term,
                                //filterGroups: 1
                            };
                        }
                        $.ajax({
                            url: OC.generateUrl('/settings/users/groups'),
                            data: queryData,
                            dataType: 'json',
                            success: function(data) {
                                var results = [];

                                // add groups
                                if (!options.excludeAdmins) {
                                    $.each(data.data.adminGroups, function(i, group) {
                                        results.push({id:group.id, displayname:group.name});
                                    });
                                }
                                $.each(data.data.groups, function(i, group) {
                                    results.push({id:group.id, displayname:group.name});
                                });

                                if (query.term === '') {
                                    // cache full list
                                    self._cachedGroups = results;
                                }
                                query.callback({results: results});
                            }
                        });
                    }, 100, true),
                    id: function(element) {
                        return element.id;
                    },
                    initSelection: function(element, callback) {
                        var selection =
                            _.map(($(element).val() || []).split('|').sort(),
                                function(groupName) {
                            return {
                                id: groupName,
                                displayname: groupName
                            };
                        });
                        callback(selection);
                    },
                    formatResult: function (element) {
                        return escapeHTML(element.displayname);
                    },
                    formatSelection: function (element) {
                        return escapeHTML(element.displayname);
                    },
                    escapeMarkup: function(m) {
                        // prevent double markup escape
                        return m;
                    }
                }, extraOptions || {}));
            }
        },
        /**
         * Setup selection box for user selection.
         *
         * Values need to be separated by a pipe "|" character.
         * (mostly because a comma is more likely to be used
         * for groups)
         *
         * @param $elements jQuery element (hidden input) to setup select2 on
         * @param {Array} [extraOptions] extra options hash to pass to select2
         * @param {Array} [options] extra options
         */
        setupUsersSelect: function($elements, extraOptions, options) {
            var self = this;
            options = options || {};
            if ($elements.length > 0) {
                // note: settings are saved through a "change" event registered
                // on all input fields
                $elements.select2(_.extend({
                    placeholder: t('core', 'Users'),
                    allowClear: true,
                    multiple: true,
                    separator: '|',
                    query: _.debounce(function(query) {
                        var queryData = {};
                        if (self._cachedUsers && query.term === '') {
                            query.callback({results: self._cachedUsers});
                            return;
                        }
                        if (query.term !== '') {
                            queryData = {
                                pattern: query.term,
                                filterUsers: 1
                            };
                        }
                        $.ajax({
                            url: OC.generateUrl('/settings/users/users'),
                            data: queryData,
                            dataType: 'json',
                            success: function(data) {
                                var results = [];

                                $.each(data, function(i, user) {
                                    results.push({id:user.name, displayname:user.displayname});
                                });

                                if (query.term === '') {
                                    // cache full list
                                    self._cachedUsers = results;
                                }
                                query.callback({results: results});
                            }
                        });
                    }, 100, true),
                    id: function(element) {
                        return element.id;
                    },
                    initSelection: function(element, callback) {
                        var selection =
                            _.map(($(element).val() || []).split('|').sort(),
                                function(groupName) {
                            return {
                                id: groupName,
                                displayname: groupName
                            };
                        });
                        callback(selection);
                    },
                    formatResult: function (element) {
                        return escapeHTML(element.displayname);
                    },
                    formatSelection: function (element) {
                        return escapeHTML(element.displayname);
                    },
                    escapeMarkup: function(m) {
                        // prevent double markup escape
                        return m;
                    }
                }, extraOptions || {}));
            }
        }
    };


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

        OC.Admin.TwoFactorTotp.setupGroupsSelect($('#totp_sharee_search_groups'));
        OC.Admin.TwoFactorTotp.setupUsersSelect($('#totp_sharee_search_users'));
        $('#totp_sharee_search_groups, #totp_sharee_search_users').change(function(){
            var self = $(this);
            var url = OC.generateUrl('/apps/twofactor_totp/settings/set'+ (this.id=='totp_sharee_search_users' ? 'Users' : 'Groups'));
            var loading = $.ajax(url, {
                method: 'POST',
                data: {
                    data: self.val()
                }
            });
        })
    })
})(OC);
