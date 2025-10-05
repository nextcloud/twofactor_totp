<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/master/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TwoFactorEMail\Service;

interface ICodeGenerator {
	public function generateChallengeCode(): string;
}
