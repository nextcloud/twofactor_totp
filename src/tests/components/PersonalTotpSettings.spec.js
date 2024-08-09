/**
 * @copyright 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

import { shallowMount, createLocalVue } from '@vue/test-utils'
import Vuex from 'vuex'

import PersonalTotpSettings from '../../components/PersonalTotpSettings.vue'

const localVue = createLocalVue()

localVue.use(Vuex)

describe('PersonalTotpSettings', () => {
	let actions
	let store

	beforeEach(() => {
		actions = {
			enable: () => {},
			confirm: () => {},
			disable: () => {},
		}
		store = new Vuex.Store({
			state: {},
			actions,
		})
	})

	it('does not load on start', () => {
		const settings = shallowMount(PersonalTotpSettings, {
			store,
			localVue,
		})

		expect(settings.vm.loading).to.be.false
	})
})
