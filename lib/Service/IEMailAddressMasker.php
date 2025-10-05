<?php

/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/master/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorEMail\Service;

interface IEMailAddressMasker {
	public function maskForUI(string $emailAddress): string;
}
