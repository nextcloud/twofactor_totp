const esModules = ['vue-material-design-icons', '@nextcloud/vue'].join('|')

module.exports = {
	verbose: true,
	moduleFileExtensions: ['js', 'vue'],
	setupFilesAfterEnv: ['<rootDir>/src/tests/jest.setup.js'],
	testEnvironment: 'jsdom',
	transform: {
		'.*\\.(js)$': 'babel-jest',
		'.*\\.(vue)$': '@vue/vue2-jest',
	},
	transformIgnorePatterns: [`/node_modules/(?!${esModules})`],
}
