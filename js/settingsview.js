/* global Backbone, Handlebars, Promise, _ */

(function (OC, Backbone, Handlebars, $, _) {
	'use strict';

	OC.Settings = OC.Settings || {};
	OC.Settings.TwoFactorTotp = OC.Settings.TwoFactorTotp || {};

	var STATE_DISABLED = 0;
	var STATE_CREATED = 1;
	var STATE_ENABLED = 2;

	var TEMPLATE = ''
		+ '{{#if loading}}'
		+ '<span class="icon-loading-small totp-loading"></span>'
		+ '<span>' + t('twofactor_totp', 'Enable TOTP') + '</span>'
		+ '{{else}}'
		+ '<div>'
		+ '    <input type="checkbox" class="checkbox" id="totp-enabled" {{#if enabled}}checked{{/if}}>'
		+ '    <label for="totp-enabled">' + t('twofactor_totp', 'Enable TOTP') + '</label>'
		+ '</div>'
		+ '{{/if}}'
		+ '{{#if secret}}'
		+ '<div>'
		+ '    <span>' + t('twofactor_totp', 'This is your new TOTP secret:') + ' {{secret}}</span>'
		+ '</div>'
		+ '<div>'
		+ '    <span>' + t('twofactor_totp', 'Scan this QR code with your TOTP app') + '<span><br>'
		+ '    <img src="{{qr}}">'
		+ '</div>'
		+ '<span>' + t('twofactor_totp', 'Once you have configured your app, enter a test code below to ensure that your app has been configured correctly.') + '<span><br>'
		+ '<input id="totp-confirmation" type="tel" minlength="6" maxlength="10" autocomplete="off" autocapitalize="off" placeholder="' + t('twofactor_totp', 'Authentication code') + '">'
		+ '<input id="totp-confirmation-submit" type="button" value="' + t('twofactor_totp', 'Verify') + '">'
		+ '{{/if}}';

	var View = Backbone.View.extend({

		/** @type {function} */
		template: Handlebars.compile(TEMPLATE),

		/** @type {bool} */
		_loading: undefined,

		/** @type {string} */
		_qr: undefined,

		/** @type {string} */
		_secret: undefined,

		/** @type {int} */
		_state: undefined,

		events: {
			'change #totp-enabled': '_onToggleEnabled',
			'click #totp-confirmation-submit': '_enableTOTP',
			'keydown #totp-confirmation': '_onConfirmKeyDown'
		},

		/**
		 * @returns {undefined}
		 */
		initialize: function () {
			this._load();
		},

		/**
		 * @param {Object} data
		 * @returns {undefined}
		 */
		render: function () {
			this.$el.html(this.template({
				loading: this._loading,
				secret: this._secret,
				qr: this._qr,
				enabled: this._state === STATE_ENABLED
			}));
		},

		/**
		 * @returns {undefined}
		 */
		_load: function () {
			this._loading = true;
			this.render();

			var url = OC.generateUrl('/apps/twofactor_totp/settings/state');
			Promise.resolve($.ajax(url, {
				method: 'GET'
			})).then(function (data) {
				this._state = data.state ? STATE_ENABLED : STATE_DISABLED;
			}.bind(this), console.error.bind(this)).then(function () {
				this._loading = false;
				this.render();
			}.bind(this));
		},

		/**
		 * @returns {Promise}
		 */
		_onToggleEnabled: function () {
			if (this._loading) {
				// Ignore event
				return;
			}

			var enabled = this.$('#totp-enabled').is(':checked');

			if (!!enabled) {
				return this._createTOTP();
			} else {
				return this._disableTOTP();
			}
		},

		/**
		 * Create a new secret on the server, which will be inactive until the
		 * user confirms their app is working by providing a OTP once.
		 *
		 * @returns {Promise}
		 */
		_createTOTP: function () {
			this._loading = true;
			// Show loading spinner
			this.render();

			return this._updateServerState({
				state: STATE_CREATED
			}).then(function () {
				// If the stat could be changed, keep showing the loading
				// spinner until the user has finished the registration
				this._loading = this._state === STATE_CREATED;
				this.render();
			}.bind(this), function(e) {
				OC.Notification.showTemporary(t('twofactor_totp', 'Could not enable TOTP'));
				console.error('Could not enable TOTP', e);

				// Restore on error
				this._loading = false;
				this.render();
			}.bind(this)).catch(console.error.bind(this));
		},

		/**
		 * Also enable TOTP if the user presses enter inside the confirmation
		 * input
		 *
		 * @param {Event} e
		 * @returns {undefined}
		 */
		_onConfirmKeyDown: function(e) {
			if (e.which === 13) {
				this._enableTOTP();
			}
		},

		/**
		 * Enable the previously created TOTP secret by sending a OTP
		 * to the server for confirmation.
		 *
		 * @returns {Promise}
		 */
		_enableTOTP: function () {
			var key = this.$('#totp-confirmation').val();

			this._loading = true;
			// Show loading spinner and disable input elements
			this.render();
			this.$('input').prop('disabled', true);

			return this._updateServerState({
				state: STATE_ENABLED,
				key: key
			}).then(function () {
				this.$('input').prop('disabled', false);
				if (this._state === STATE_ENABLED) {
					// Success
					this._loading = false;
					this._qr = undefined;
					this._secret = undefined;
				} else {
					OC.Notification.showTemporary(t('twofactor_totp', 'Could not verify your key. Please try again'));
				}
				this.render();
			}.bind(this), console.error.bind(this));
		},

		/**
		 * @returns {Promise}
		 */
		_disableTOTP: function () {
			this._loading = true;
			// Show loading spinner
			this.render();

			return this._updateServerState({
				state: STATE_DISABLED
			}).then(function () {
				this._loading = false;
				this.render();
			}.bind(this), console.error.bind(this));
		},

		/**
		 * @param {Object} data
		 * @param {int} data.state
		 * @returns {Promise}
		 */
		_updateServerState: function (data) {
			var url = OC.generateUrl('/apps/twofactor_totp/settings/enable');
			return this._requirePasswordConfirmation().then(function () {
				return $.ajax(url, {
					method: 'POST',
					data: data
				});
			}).then(function (data) {
				this._state = data.state;
				// Optional response: qr, secret
				if (!_.isUndefined(data.qr) && !_.isUndefined(data.secret)) {
					this._qr = data.qr;
					this._secret = data.secret;
				}
			}.bind(this), function () {
				console.error(arguments);
				throw new Error('twofactor_totp', 'Error while communicating with the server');
			});
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
		}

	});

	OC.Settings.TwoFactorTotp.View = View;

})(OC, OC.Backbone, Handlebars, $, _);
