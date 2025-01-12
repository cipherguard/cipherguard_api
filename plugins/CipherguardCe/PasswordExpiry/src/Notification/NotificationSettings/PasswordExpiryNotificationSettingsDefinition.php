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
 * @since         4.5.0
 */

namespace Cipherguard\PasswordExpiry\Notification\NotificationSettings;

use Cake\Form\Schema;
use Cake\Validation\Validator;
use Cipherguard\EmailNotificationSettings\Utility\EmailNotificationSettingsDefinitionInterface;
use Cipherguard\EmailNotificationSettings\Utility\EmailNotificationSettingsDefinitionTrait;

class PasswordExpiryNotificationSettingsDefinition implements EmailNotificationSettingsDefinitionInterface
{
    use EmailNotificationSettingsDefinitionTrait;

    /**
     * @inheritDoc
     */
    public function buildSchema(Schema $schema): Schema
    {
        return $schema
            ->addField('send_password_expire', ['type' => 'boolean', 'default' => true]);
    }

    /**
     * @inheritDoc
     */
    public function buildValidator(Validator $validator): Validator
    {
        return $validator
            ->boolean('send_password_expire', __('The send password expire setting should be a boolean.'));
    }
}
