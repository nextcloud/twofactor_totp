/* global Backbone, Handlebars, Promise */

(function (OC, Backbone, Handlebars, $) {
	'use strict';

	OC.Settings = OC.Settings || {};
	OC.Settings.TwoFactorTotp = OC.Settings.TwoFactorTotp || {};

	var TEMPLATE = '<div>'
		+ '    <input type="checkbox" class="checkbox" id="totp-enabled">'
		+ '    <label for="totp-enabled">' + t('twofactor_totp', 'Enable TOTP') + '</label>'
		+ '</div>'
		+ '{{#if secret}}'
		+ '<div>'
		+ '    <span>' + t('twofactor_totp', 'This is your new TOTP secret:') + ' {{secret}}</span>'
		+ '</div>'
		+ '<div>'
		+ '    <span>' + t('twofactor_totp', 'Scan this QR code with your TOTP app') + '<span><br>'
		+ '    <img src="{{qr}}">'
		+ '    </div>'
		+ '{{/if}}';

	var View = Backbone.View.extend({
		template: Handlebars.compile(TEMPLATE),
		_loading: undefined,
		_enabled: undefined,
		events: {
			'change #totp-enabled': '_onToggleEnabled'
		},
		initialize: function () {
			this._load();
		},
		render: function (data) {
			this.$el.html(this.template(data));
		},
		_load: function () {
			this._loading = true;

			var url = OC.generateUrl('/apps/twofactor_totp/settings/state');
			var loading = $.ajax(url, {
				method: 'GET'
			});

			var _this = this;
			$.when(loading).done(function (data) {
				_this._enabled = data.enabled;
				_this.$('#totp-enabled').attr('checked', data.enabled);
			});
			$.when(loading).always(function () {
				_this._loading = false;
			});
		},
		_onToggleEnabled: function () {
			if (this._loading) {
				// Ignore event
				return;
			}

			var enabled = this.$('#totp-enabled').is(':checked');

			if (enabled !== this._enabled) {
				var url = OC.generateUrl('/apps/twofactor_totp/settings/enable');
				this._loading = true;
				this._enabled = enabled;

				var _this = this;
				this._requirePasswordConfirmation().then(function () {
					return Promise.resolve($.ajax(url, {
						method: 'POST',
						data: {
							state: enabled
						}
					}));
				}).then(function(data) {
					_this._enabled = data.enabled;
					_this._showQr(data);
					_this.$('#totp-enabled').attr('checked', data.enabled);
				}).catch(console.error.bind(this)).then(function() {
					_this._loading = false;
				});
			}
		},

		/**
		 * @returns {Promise}
		 */
		_requirePasswordConfirmation: function () {
			if (!OC.PasswordConfirmation.requiresPasswordConfirmation()) {
				return Promise.resolve();
			}
			return new Promise(function (resolve) {
				OC.PasswordConfirmation.requirePasswordConfirmation(resolve);
			});
		},

		/**
		 * @param {object} data
		 * @returns {undefined}
		 */
		_showQr: function (data) {
			this.render({
				secret: data.secret,
				qr: data.qr
			});
		}
	});

	OC.Settings.TwoFactorTotp.View = View;

})(OC, Backbone, Handlebars, $);