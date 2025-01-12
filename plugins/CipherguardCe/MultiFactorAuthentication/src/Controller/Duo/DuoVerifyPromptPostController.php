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
 * @since         3.11.0
 */
namespace Cipherguard\MultiFactorAuthentication\Controller\Duo;

use App\Authenticator\SessionIdentificationServiceInterface;
use App\Model\Entity\AuthenticationToken;
use App\Service\Cookie\AbstractSecureCookieService;
use Cake\Http\Exception\ServiceUnavailableException;
use Cake\Http\Response;
use Cipherguard\MultiFactorAuthentication\Controller\MfaVerifyController;
use Cipherguard\MultiFactorAuthentication\Service\Duo\MfaDuoStartDuoAuthenticationService;
use Cipherguard\MultiFactorAuthentication\Service\Duo\MfaDuoStateCookieService;
use Cipherguard\MultiFactorAuthentication\Utility\MfaSettings;
use Duo\DuoUniversal\Client;

/**
 * @property \App\Controller\Component\SanitizeUrlComponent $SanitizeUrl
 */
class DuoVerifyPromptPostController extends MfaVerifyController
{
    /**
     * @return void
     * @throws \Exception
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('SanitizeUrl');
    }

    /**
     * Handle Duo verify prompt POST request.
     *
     * @param \App\Authenticator\SessionIdentificationServiceInterface $sessionIdentificationService Session
     * @param \Duo\DuoUniversal\Client|null $duoSdkClient Duo SDK Client
     * @return \Cake\Http\Response|null
     */
    public function post(
        SessionIdentificationServiceInterface $sessionIdentificationService,
        ?Client $duoSdkClient = null
    ): ?Response {
        $this->_assertRequestNotJson();
        $this->_handleVerifiedNotRequired($sessionIdentificationService);
        $redirect = $this->_handleInvalidSettings(MfaSettings::PROVIDER_DUO);
        if ($redirect) {
            return $redirect;
        }

        $redirectParam = $this->SanitizeUrl->sanitizeRedirect('/mfa/verify', true);
        $startAuthService = new MfaDuoStartDuoAuthenticationService(
            AuthenticationToken::TYPE_MFA_VERIFY,
            $duoSdkClient
        );
        try {
            $duoAuthenticationRequest = $startAuthService->start(
                $this->User->getAccessControl(),
                $redirectParam
            );
        } catch (ServiceUnavailableException $e) {
            $this->Flash->error($e->getMessage());

            return $this->redirect($redirectParam);
        }
        $cookie = (new MfaDuoStateCookieService())->createDuoStateCookie(
            $duoAuthenticationRequest->authenticationToken->token,
            AbstractSecureCookieService::isSslOrCookiesSecure($this->getRequest())
        );

        $this->setResponse($this->getResponse()->withCookie($cookie));

        return $this->redirect($duoAuthenticationRequest->duoAuthenticationUrl);
    }
}
