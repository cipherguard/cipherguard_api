<?php
declare(strict_types=1);

/**
 * Cipherguard ~ Open source password manager for teams
 * Copyright (c) Cipherguard SA (https://www.cipherguard.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cipherguard SA (https://www.cipherguard.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.cipherguard.com Cipherguard(tm)
 * @since         3.3.0
 */
namespace Cipherguard\JwtAuthentication\Controller;

use App\Controller\AppController;
use Cipherguard\JwtAuthentication\Service\RefreshToken\RefreshTokenAbstractService;
use Cipherguard\JwtAuthentication\Service\RefreshToken\RefreshTokenLogoutService;

class JwtLogoutController extends AppController
{
    /**
     * User logout post action
     *
     * @return void
     */
    public function logoutPost()
    {
        (new RefreshTokenLogoutService())->logout($this->User->id(), $this->getRequest());
        $this->removeRefreshTokenFromCookies();
        $this->Authentication->logout();
        $this->success();
    }

    /**
     * Ensures that no refresh token cookie is placed in the header
     *
     * @return void
     */
    protected function removeRefreshTokenFromCookies(): void
    {
        $cookiesCollection = $this->getResponse()->getCookieCollection()->remove(
            RefreshTokenAbstractService::REFRESH_TOKEN_COOKIE
        );
        $this->setResponse($this->getResponse()->withCookieCollection($cookiesCollection));
    }
}
