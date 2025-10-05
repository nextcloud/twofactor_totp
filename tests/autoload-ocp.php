<?php

/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/master/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

spl_autoload_register(function (string $class) {
	if (str_starts_with($class, 'OCP\\') || str_starts_with($class, 'NCU\\')) {
		include_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
			. 'vendor' . DIRECTORY_SEPARATOR . 'nextcloud' . DIRECTORY_SEPARATOR . 'ocp' . DIRECTORY_SEPARATOR
			. str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
	}
});
