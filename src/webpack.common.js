const path = require('path');
const { VueLoaderPlugin } = require('vue-loader');

module.exports = {
	entry: path.join(__dirname, 'main-settings.js'),
	output: {
		path: path.resolve(__dirname, '../js'),
		publicPath: '/js/',
		filename: 'settings.js'
	},
	module: {
		rules: [
			{
				test: /\.css$/,
				use: ['vue-style-loader', 'css-loader']
			},
			{
				test: /\.scss$/,
				use: ['vue-style-loader', 'css-loader', 'sass-loader']
			},
			{
				test: /\.vue$/,
				loader: 'vue-loader'
			},
			{
				test: /\.js$/,
				loader: 'babel-loader',
				exclude: /node_modules/
			},
			{
				test: /\.(png|jpg|gif)$/,
				loader: 'file-loader',
				options: {
					name: '[name].[ext]?[hash]'
				}
			},
			{
				test: /\.(svg)$/i,
				use: [
					{
						loader: 'url-loader'
					}
				]
			}
		]
	},
	plugins: [new VueLoaderPlugin()],
	resolve: {
		extensions: ['*', '.js', '.vue']
	}
};