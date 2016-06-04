<?php

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * ownCloud - Two-factor TOTP
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

namespace OCA\TwoFactorTotp\Controller;

use OCA\TwoFactorTotp\Service\ITotp;
use OCA\TwoFactorTotp\Service\Totp;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IUserSession;

class SettingsController extends Controller {

    /** @var ITotp */
    private $totp;

    /** @var IUserSession */
    private $userSession;

    public function __construct($appName, IRequest $request, IUserSession $userSession, Totp $totp) {
        parent::__construct($appName, $request);
        $this->userSession = $userSession;
        $this->totp = $totp;
    }

    /**
     * @NoAdminRequired
     * @return JSONResponse
     */
    public function state() {
        $user = $this->userSession->getUser();
        return [
            'enabled' => $this->totp->hasSecret($user),
        ];
    }

    /**
     * @NoAdminRequired
     * @param bool $state
     * @return JSONResponse
     */
    public function enable($state) {
        $user = $this->userSession->getUser();
        if ($state) {
            $qr = $this->totp->createSecret($user);
            return [
                'enabled' => true,
                'qr' => $qr,
            ];
        }

        $this->totp->deleteSecret($user);
        return [
            'enabled' => false,
            'qr' => null,
        ];
    }

}
