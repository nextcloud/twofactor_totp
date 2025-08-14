<?php

namespace OCA\TwoFactorEMail\Service;

use NCU\Config\IUserConfig;
use NCU\Config\ValueType;
use OCA\TwoFactorEMail\AppInfo\Application;

class PreferencesCodeStorage implements ICodeStorage
{
	private const KEY_CODE = 'code';
	private const KEY_CREATED_AT = 'code_created_at';

	public function __construct(
		private IApplicationSettings $settings,
		private IUserConfig $config,
	)
	{
	}

	public function readCode(string $userId): ?string
	{
		$expiresBefore = time() - $this->settings->getCodeValidSeconds();
		$createdAt = $this->config->getValueInt($userId, Application::APP_ID, self::KEY_CREATED_AT);
		if ($createdAt < $expiresBefore) {
			$this->deleteCode($userId);
			return null;
		}

		$code = $this->config->getValueString($userId, Application::APP_ID, self::KEY_CODE);
		if ($code === '') {
			$this->deleteCode($userId);
			return null;
		}
		return $code;
	}

	public function writeCode(string $userId, string $code, ?int $createdAt = null): void
	{
		$createdAt ??= time();
		$this->config->setValueString($userId, Application::APP_ID, self::KEY_CODE, $code);
		$this->config->setValueInt($userId, Application::APP_ID, self::KEY_CREATED_AT, $createdAt);
	}

	public function deleteExpired(): void
	{
		$expiresBefore = time() - $this->settings->getCodeValidSeconds();
		$creationTime = $this->config->getValuesByUsers(Application::APP_ID, self::KEY_CREATED_AT, ValueType::INT);

		foreach ($creationTime as $userId => $createdAt) {
			if ($createdAt < $expiresBefore) {
				$this->deleteCode($userId);
			}
		}
	}

	public function deleteCode(string $userId): void
	{
		$this->config->deleteUserConfig($userId, Application::APP_ID, self::KEY_CODE);
		$this->config->deleteUserConfig($userId, Application::APP_ID, self::KEY_CREATED_AT);
	}
}
