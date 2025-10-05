<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/master/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

/*
 * Nextcloud USES the class 'StateController(.php)' as the 'name' refers to 'State' here.
 * 'update' is a method thereof.
 */

return [
	'routes' => [
		[
			'name' => 'State#update',
			'url' => '/personal_settings/state',
			'verb' => 'POST',
		],
	]
];
