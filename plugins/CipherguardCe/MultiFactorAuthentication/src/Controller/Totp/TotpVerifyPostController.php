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
 * @since         2.4.0
 */
namespace Cipherguard\MultiFactorAuthentication\Controller\Totp;

use App\Authenticator\SessionIdentificationServiceInterface;
use App\Error\Exception\CustomValidationException;
use Cake\Http\Exception\BadRequestException;
use Cipherguard\JwtAuthentication\Service\Middleware\JwtAuthenticationService;
use Cipherguard\JwtAuthentication\Service\RefreshToken\RefreshTokenAbstractService;
use Cipherguard\JwtAuthentication\Service\RefreshToken\RefreshTokenLogoutService;
use Cipherguard\MultiFactorAuthentication\Controller\MfaVerifyController;
use Cipherguard\MultiFactorAuthentication\Form\MfaFormInterface;
use Cipherguard\MultiFactorAuthentication\Service\MfaPolicies\RememberAMonthSettingInterface;
use Cipherguard\MultiFactorAuthentication\Service\MfaRateLimiterService;
use Cipherguard\MultiFactorAuthentication\Utility\MfaSettings;

class TotpVerifyPostController extends MfaVerifyController
{
    /**
     * Totp Verify Post
     *
     * @param \App\Authenticator\SessionIdentificationServiceInterface $sessionIdentificationService Session ID service
     * @param \Cipherguard\MultiFactorAuthentication\Form\MfaFormInterface $verifyForm MFA Form
     * @param \Cipherguard\MultiFactorAuthentication\Service\MfaPolicies\RememberAMonthSettingInterface $rememberMeForAMonthSetting Remember a month setting.
     * @throws \Cake\Http\Exception\InternalErrorException
     * @throws \Cake\Http\Exception\BadRequestException
     * @return void
     */
    public function post(
        SessionIdentificationServiceInterface $sessionIdentificationService,
        MfaFormInterface $verifyForm,
        RememberAMonthSettingInterface $rememberMeForAMonthSetting
    ) {
        $this->_handleVerifiedNotRequired($sessionIdentificationService);
        $this->_handleInvalidSettings(MfaSettings::PROVIDER_TOTP);

        // Verify totp
        try {
            $verifyForm->execute($this->request->getData());
        } catch (CustomValidationException $exception) {
            $this->_handleMaxFailedAttempts();

            if ($this->request->is('json')) {
                throw $exception;
            }
            // Display form with error msg
            $this->set('providers', $this->mfaSettings->getEnabledProviders());
            $this->set('verifyForm', $verifyForm);
            $this->set('isRememberMeForAMonthEnabled', $rememberMeForAMonthSetting->isEnabled());
            $this->viewBuilder()
                ->setLayout('mfa_verify')
                ->setTemplatePath(ucfirst(MfaSettings::PROVIDER_TOTP))
                ->setTemplate('verifyForm');

            return;
        }

        // Build verified proof token and associated cookie and add it to request
        $this->_generateMfaToken(
            MfaSettings::PROVIDER_TOTP,
            $sessionIdentificationService,
            $rememberMeForAMonthSetting
        );
        $this->_handleVerifySuccess();
    }

    /**
     * Logout if user exceeded max failed attempts.
     *
     * @return \Cake\Http\Response|null
     * @throws \Cake\Http\Exception\BadRequestException If failed attempts exceeded and request is JSON.
     */
    protected function _handleMaxFailedAttempts()
    {
        // Determines if current authentication is SESSION based or JWT based.
        $isJwtAuth = ($this->getRequest()->getAttribute('authentication') instanceof JwtAuthenticationService);

        $isFailedAttemptExceeded = (new MfaRateLimiterService())->isFailedAttemptsExceeded(
            $this->User->id(),
            $isJwtAuth,
            true // Consider this as a failed attempt too.
        );

        if (!$isFailedAttemptExceeded) {
            return null;
        }

        if (!$this->request->is('json')) {
            // Logout and redirect
            return $this->redirect($this->Authentication->logout());
        }

        if ($isJwtAuth) {
            (new RefreshTokenLogoutService())->logout($this->User->id(), $this->getRequest());
            $cookiesCollection = $this->getResponse()->getCookieCollection()->remove(
                RefreshTokenAbstractService::REFRESH_TOKEN_COOKIE
            );
            $this->setResponse($this->getResponse()->withCookieCollection($cookiesCollection));
        }

        $this->Authentication->logout();

        throw new BadRequestException(__('You have been logged out due to too many failed attempts.'));
    }
}
