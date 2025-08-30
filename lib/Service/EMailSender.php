<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TwoFactorEMail\Service;

use Exception;
use OCA\TwoFactorEMail\Exception\EMailNotSetException;
use OCA\TwoFactorEMail\Exception\EMailTransportFailedException;
use OCP\Defaults;
use OCP\IL10N;
use OCP\IUser;
use OCP\Mail\IMailer;
use Psr\Log\LoggerInterface;

class EMailSender implements IEMailSender {
	public function __construct(
		private LoggerInterface $logger,
		private IL10N $l10n,
		private IMailer $mailer,
		private Defaults $defaults,
	) {
	}

	public function sendChallengeEMail(IUser $user, string $code): void {
		$email = $user->getEMailAddress();
		if (empty($email)) {
			throw new EMailNotSetException($user);
		}

		$this->logger->debug("sending email message to $email, code: $code");

		$template = $this->mailer->createEMailTemplate('twofactor_email.send');
		$user_at_cloud = $user->getDisplayName() . ' @ ' . $this->defaults->getName();
		$template->setSubject($this->l10n->t('Login attempt for %s', [$user_at_cloud]));
		$template->addHeader();
		$template->addHeading($this->l10n->t('Your two-factor authentication code is: %s', [$code]));
		$template->addBodyText($this->l10n->t('If you tried to login, please enter that code on %s. If you did not, somebody else did and knows your your e-mail address or username – and your password!', [$this->defaults->getName()]));
		$template->addFooter();

		$message = $this->mailer->createMessage();
		$message->setTo([ $email => $user->getDisplayName() ]);
		$message->useTemplate($template);

		try {
			$this->mailer->send($message);
		} catch (Exception $e) {
			$this->logger->error("failed sending email message to $email, code: $code");
			throw new EMailTransportFailedException(previous: $e);
		}
	}
}
