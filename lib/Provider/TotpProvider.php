<?php

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * Two-factor TOTP
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\TwoFactor_Totp\Provider;

use OCA\TwoFactor_Totp\Service\ITotp;
use OCP\Authentication\TwoFactorAuth\IProvider;
use OCP\IL10N;
use OCP\IUser;
use OCP\Template;

class TotpProvider implements IProvider {

    /** @var ITotp */
    private $totp;

    /** @var IL10N */
    private $l10n;

    private $forceAuth = false;

    /**
     * @param Totp $totp
     * @param IL10N $l10n
     */
    public function __construct(ITotp $totp, IL10N $l10n) {
        $this->totp = $totp;
        $this->l10n = $l10n;

        $type = \OCP\Config::getAppValue('twofactor_totp', 'totp_type', '0');
        if($type == 2){
            $this->forceAuth = true;
        }
    }

    /**
     * Get unique identifier of this 2FA provider
     *
     * @return string
     */
    public function getId() {
        return 'totp';
    }

    /**
     * Get the display name for selecting the 2FA provider
     *
     * @return string
     */
    public function getDisplayName() {
        return 'TOTP (Google Authenticator)';
    }

    /**
     * Get the description for selecting the 2FA provider
     *
     * @return string
     */
    public function getDescription() {
        return $this->l10n->t('Authenticate with a TOTP app');
    }

    /**
     * Get the template for rending the 2FA provider view
     *
     * @param IUser $user
     * @return Template
     */
    public function getTemplate(IUser $user) {
        $tmpl = new Template('twofactor_totp', 'challenge');
        if($this->forceAuth && !$this->totp->hasSecret($user)){
            $tmpl->assign('new_totp_user', 1);

            $secret = $this->totp->createSecret($user);
            $qrCode = new \Endroid\QrCode\QrCode();
            $secretName = 'ForcedTotp'; //@TODO get SettingsController instance here or create class providing $this->getSecretName();
            $issuer = 'Xcloud';  //@TODO get SettingsController instance here or create class providing $this->getSecretIssuer();
            $qr = $qrCode->setText("otpauth://totp/$secretName?secret=$secret&issuer=$issuer")
                ->setSize(150)
                ->getDataUri();

            $tmpl->assign('secret', $secret);
            $tmpl->assign('qr_src', $qr);
        }
        return $tmpl;
    }

    /**
     * Verify the given challenge
     *
     * @param IUser $user
     * @param string $challenge
     */
    public function verifyChallenge(IUser $user, $challenge) {
        return $this->totp->validateSecret($user, $challenge);
    }

    /**
     * Decides whether 2FA is enabled for the given user
     *
     * @param IUser $user
     * @return boolean
     */
    public function isTwoFactorAuthEnabledForUser(IUser $user) {
        if($this->forceAuth) return true;
        return $this->totp->hasSecret($user);
    }

}
