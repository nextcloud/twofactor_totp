<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorTOTP\Activity;

use OCP\Activity\Exceptions\UnknownActivityException;
use OCP\Activity\IEvent;
use OCP\Activity\IProvider;
use OCP\IURLGenerator;
use OCP\L10N\IFactory as L10nFactory;

class Provider implements IProvider {

	public function __construct(
		private L10nFactory $l10n,
		private IURLGenerator $urlGenerator,
	) {
	}

	public function parse($language, IEvent $event, ?IEvent $previousEvent = null): IEvent {
		if ($event->getApp() !== 'twofactor_totp') {
			throw new UnknownActivityException();
		}

		$l = $this->l10n->get('twofactor_totp', $language);

		$event->setIcon($this->urlGenerator->getAbsoluteURL($this->urlGenerator->imagePath('core', 'actions/password.svg')));
		$event->setSubject(match ($event->getSubject()) {
			'totp_enabled_subject' => $l->t('You enabled TOTP two-factor authentication for your account'),
			'totp_disabled_subject' => $l->t('You disabled TOTP two-factor authentication for your account'),
			'totp_disabled_by_admin' => $l->t('TOTP two-factor authentication disabled by the administration'),
			default => throw new UnknownActivityException(),
		});
		return $event;
	}
}
