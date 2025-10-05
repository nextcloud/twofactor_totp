<?php

declare(strict_types = 1);

/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/main/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorEMail\Service;

interface ICodeStorage {
	public function readCode(string $userId): ?string;
	public function writeCode(string $userId, string $code, ?int $createdAt = null): void;
	public function deleteCode(string $userId): void;
	public function deleteExpired(): void;
}
