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
 * @since         3.8.0
 */
namespace Cipherguard\SmtpSettings\Service;

use App\Error\Exception\FormValidationException;
use App\Utility\Application\FeaturePluginAwareTrait;
use Cake\Mailer\Mailer;
use Cake\Mailer\TransportFactory;
use Cipherguard\SmtpSettings\Form\EmailConfigurationForm;

class SmtpSettingsGetService
{
    use FeaturePluginAwareTrait;
    use SmtpSettingsServiceTrait;

    public const SMTP_SETTINGS_SOURCE_FILE = 'file';
    public const SMTP_SETTINGS_SOURCE_DB = 'db';
    public const SMTP_SETTINGS_SOURCE_ENV = 'env';
    public const SMTP_SETTINGS_SOURCE_UNDEFINED = 'undefined';

    /**
     * @var string
     */
    private $cipherguardFileName;

    /**
     * @param string $cipherguardFileName The cipherguard config file, modifiable for unit test purpose.
     */
    public function __construct(string $cipherguardFileName = CONFIG . DS . 'cipherguard.php')
    {
        $this->cipherguardFileName = $cipherguardFileName;
    }

    /**
     * Read SMTP settings in the DB, or in file.
     * Validates the setting and return them
     *
     * @return array
     * @throws \App\Error\Exception\FormValidationException if the data does not validate the EmailConfigurationForm
     * @throws \Cake\Http\Exception\InternalErrorException if the data in the DB cannot be decrypted
     */
    public function getSettings(): array
    {
        $form = new EmailConfigurationForm();
        $data = $this->readConfigInDbOrFile();

        if (!$form->execute($data)) {
            throw new FormValidationException(__('Could not validate the smtp settings.'), $form);
        }

        $allowedFields = array_merge([
            'source', 'id', 'created', 'modified', 'created_by', 'modified_by',
        ], SmtpSettingsSetService::SMTP_SETTINGS_ALLOWED_FIELDS);

        return $this->sanitizeData($form->getData(), $allowedFields);
    }

    /**
     * @return array
     * @throws \Cake\Http\Exception\InternalErrorException if the data in the DB cannot be decrypted
     */
    protected function readConfigInDbOrFile(): array
    {
        return $this->readConfigInDb() ?? $this->readConfigInFile();
    }

    /**
     * Reads the SMTP settings in DB
     *
     * @return array|null
     * @throws \Cake\Http\Exception\InternalErrorException if the data in the DB cannot be decrypted
     */
    protected function readConfigInDb(): ?array
    {
        if (!$this->isFeaturePluginEnabled('SmtpSettings')) {
            return null;
        }
        $config = (new SmtpSettingsGetSettingsInDbService())->getSettings();
        if (!is_null($config)) {
            $config['source'] = self::SMTP_SETTINGS_SOURCE_DB;
        }

        return $config;
    }

    /**
     * @return array
     */
    protected function readConfigInFile(): array
    {
        $config = TransportFactory::get('default')->getConfig();
        if ($this->isSmtpDefinedInCipherguardConfigFile()) {
            $config['source'] = SmtpSettingsGetService::SMTP_SETTINGS_SOURCE_FILE;
        } else {
            $config['source'] = SmtpSettingsGetService::SMTP_SETTINGS_SOURCE_ENV;
        }
        $from = Mailer::getConfig('default')['from'] ?? [];
        foreach ($from as $email => $name) {
            $config['sender_email'] = $email;
            $config['sender_name'] = $name;
        }

        return $config;
    }

    /**
     * Checks that the cipherguard file is readable.
     *
     * Loads the config and checks if both SMTP config and sender email
     * are defined.
     *
     * This method is mocked in tests, its functionality is not unit tested: handle with care.
     *
     * @return bool true if SMTP and sender info are defined in config/cipherguard.php
     */
    protected function isSmtpDefinedInCipherguardConfigFile(): bool
    {
        if (!$this->isCipherguardConfigFileFoundAndReadable()) {
            return false;
        }

        $fileConfig = require $this->cipherguardFileName;

        $isEmailTransportDefinedInConfig = array_key_exists('EmailTransport', $fileConfig);
        $isEmailSenderDefinedInConfig = array_key_exists('Email', $fileConfig);

        return $isEmailSenderDefinedInConfig && $isEmailTransportDefinedInConfig;
    }

    /**
     * @return bool
     */
    protected function isCipherguardConfigFileFoundAndReadable(): bool
    {
        return \file_exists($this->cipherguardFileName) && \is_readable($this->cipherguardFileName);
    }
}
