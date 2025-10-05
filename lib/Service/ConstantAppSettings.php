<?php

/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/main/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorEMail\Service;

final class ConstantAppSettings implements IAppSettings {
	public function getCodeValidSeconds(): int {
		return 60 * 60 * 24; // 1 day
	}
}
