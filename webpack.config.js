const path = require('path')
const webpackConfig = require('@nextcloud/webpack-vue-config')

webpackConfig.entry = {
	'main-settings': path.join(__dirname, 'src', 'main-settings.js'),
	'main-login-setup': path.join(__dirname, 'src', 'main-login-setup.js'),
}

module.exports = webpackConfig
