<?php

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * Two-factor TOTP
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */
return [
    'routes' => [
        [
            'name' => 'settings#state',
            'url' => '/settings/state',
            'verb' => 'GET'
        ],
        [
            'name' => 'settings#enable',
            'url' => '/settings/enable',
            'verb' => 'POST'
        ],

        // Admin routes
        [
            'name' => 'settings#getAdminConfig',
            'url' => '/settings/getAdminConfig',
            'verb' => 'GET'
        ],
        [
            'name' => 'settings#setAdminConfig',
            'url' => '/settings/setAdminConfig',
            'verb' => 'POST'
        ],
        [
            'name' => 'settings#setGroups',
            'url' => '/settings/setGroups',
            'verb' => 'POST'
        ],
        [
            'name' => 'settings#setUsers',
            'url' => '/settings/setUsers',
            'verb' => 'POST'
        ],
    ]
];
