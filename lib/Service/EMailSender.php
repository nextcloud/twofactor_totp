<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/main/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TwoFactorEMail\Service;

use Exception;
use OCA\TwoFactorEMail\Exception\EMailNotSet;
use OCA\TwoFactorEMail\Exception\SendEMailFailed;
use OCP\Defaults;
use OCP\IL10N;
use OCP\IUser;
use OCP\Mail\IMailer;
use Psr\Log\LoggerInterface;

final class EMailSender implements IEMailSender {
	public function __construct(
		private LoggerInterface $logger,
		private IL10N $l10n,
		private IMailer $mailer,
		private Defaults $defaults,
	) {
	}

	public function sendChallengeEMail(IUser $user, string $code): void {
		$email = $user->getEMailAddress();
		if ($email === null) {
			throw new EMailNotSet($user);
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
			throw new SendEMailFailed(previous: $e);
		}
	}
}
