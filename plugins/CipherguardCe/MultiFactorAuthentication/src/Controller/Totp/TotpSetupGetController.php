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

use Cake\Http\Exception\BadRequestException;
use Cipherguard\MultiFactorAuthentication\Controller\MfaSetupController;
use Cipherguard\MultiFactorAuthentication\Form\MfaFormInterface;
use Cipherguard\MultiFactorAuthentication\Utility\MfaOtpFactory;
use Cipherguard\MultiFactorAuthentication\Utility\MfaSettings;

class TotpSetupGetController extends MfaSetupController
{
    /**
     * Totp Get Qr Code and provisioning urls
     *
     * @param \Cipherguard\MultiFactorAuthentication\Form\MfaFormInterface $setupForm MFA Form
     * @return void
     */
    public function get(MfaFormInterface $setupForm)
    {
        $this->_orgAllowProviderOrFail(MfaSettings::PROVIDER_TOTP);
        try {
            $this->_notAlreadySetupOrFail(MfaSettings::PROVIDER_TOTP);
            $this->_handleGetNewSettings($setupForm);
        } catch (BadRequestException $exception) {
            $this->_handleGetExistingSettings(MfaSettings::PROVIDER_TOTP);
        }
    }

    /**
     * Display start page (with how to diagram)
     *
     * @return void
     */
    public function start()
    {
        $this->_orgAllowProviderOrFail(MfaSettings::PROVIDER_TOTP);
        try {
            $this->_notAlreadySetupOrFail(MfaSettings::PROVIDER_TOTP);
            $this->_handleGetStart();
        } catch (BadRequestException $exception) {
            $this->_handleGetExistingSettings(MfaSettings::PROVIDER_TOTP);
        }
    }

    /**
     * Display start page
     *
     * @return void
     */
    protected function _handleGetStart()
    {
        if (!$this->request->is('json')) {
            $this->set('theme', $this->User->theme());
            $this->viewBuilder()
                ->setLayout('mfa_setup')
                ->setTemplatePath(ucfirst(MfaSettings::PROVIDER_TOTP))
                ->setTemplate('setupStart');
        }
        $this->success(__('Please setup the TOTP application.'));
    }

    /**
     * Handle get request when new settings are needed
     *
     * @param \Cipherguard\MultiFactorAuthentication\Form\MfaFormInterface $totpSetupForm MFA Form
     * @return void
     */
    protected function _handleGetNewSettings(MfaFormInterface $totpSetupForm)
    {
        // Build and return some URI and QR code to work from
        // even though they can be set manually in the post as well
        $uac = $this->User->getAccessControl();
        $uri = MfaOtpFactory::generateTOTP($uac);
        $qrCode = MfaOtpFactory::getQrCodeInlineSvg($uri);

        if (!$this->request->is('json')) {
            $this->set('totpSetupForm', $totpSetupForm);
            $this->set('theme', $this->User->theme());
            $this->request = $this->request
                ->withData('otpQrCodeSvg', $qrCode)
                ->withData('otpProvisioningUri', $uri);
            $this->viewBuilder()
                ->setLayout('mfa_setup')
                ->setTemplatePath(ucfirst(MfaSettings::PROVIDER_TOTP))
                ->setTemplate('setupForm');
        } else {
            $data = [
                'otpQrCodeSvg' => $qrCode,
                'otpProvisioningUri' => $uri,
            ];
            $this->success(__('Please setup the TOTP application.'), $data);
        }
    }
}
