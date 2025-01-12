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
 * @since         4.7.0
 */

namespace Cipherguard\SmtpSettings\Service\Healthcheck;

use App\Service\Healthcheck\HealthcheckCliInterface;
use App\Service\Healthcheck\HealthcheckServiceCollector;
use App\Service\Healthcheck\HealthcheckServiceInterface;
use Cake\Core\Configure;
use Cipherguard\SmtpSettings\Middleware\SmtpSettingsSecurityMiddleware;

class SmtpSettingsEndpointsDisabledHealthcheck implements HealthcheckServiceInterface, HealthcheckCliInterface
{
    /**
     * Status of this health check if it is passed or failed.
     *
     * @var bool
     */
    private bool $status = false;

    /**
     * @inheritDoc
     */
    public function check(): HealthcheckServiceInterface
    {
        $this->status = Configure::read(
            SmtpSettingsSecurityMiddleware::CIPHERGUARD_SECURITY_SMTP_SETTINGS_ENDPOINTS_DISABLED
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function domain(): string
    {
        return HealthcheckServiceCollector::DOMAIN_SMTP_SETTINGS;
    }

    /**
     * @inheritDoc
     */
    public function isPassed(): bool
    {
        return $this->status;
    }

    /**
     * @inheritDoc
     */
    public function level(): string
    {
        return HealthcheckServiceCollector::LEVEL_WARNING;
    }

    /**
     * @inheritDoc
     */
    public function getSuccessMessage(): string
    {
        return __('The {0} plugin endpoints are disabled.', 'SMTP Settings');
    }

    /**
     * @inheritDoc
     */
    public function getFailureMessage(): string
    {
        return __('The {0} plugin endpoints are enabled.', 'SMTP Settings');
    }

    /**
     * @inheritDoc
     */
    public function getHelpMessage(): array
    {
        return [
            __('It is recommended to disable the plugin endpoints.'),
            __('Set the CIPHERGUARD_SECURITY_SMTP_SETTINGS_ENDPOINTS_DISABLED environment variable to true.'),
            __('Or set cipherguard.security.smtpSettings.endpointsDisabled to true in {0}.', CONFIG . 'cipherguard.php'),
        ];
    }

    /**
     * CLI Option for this check.
     *
     * @return string
     */
    public function cliOption(): string
    {
        return HealthcheckServiceCollector::DOMAIN_SMTP_SETTINGS;
    }

    /**
     * @inheritDoc
     */
    public function getLegacyArrayKey(): string
    {
        return 'areEndpointsDisabled';
    }
}
