<?php
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
 * @since         3.10.0
 */
declare(strict_types=1);

namespace Cipherguard\SelfRegistration\Notification\Email\Redactor;

use Cake\Form\Schema;
use Cake\Validation\Validator;
use Cipherguard\EmailNotificationSettings\Utility\EmailNotificationSettingsDefinitionInterface;
use Cipherguard\EmailNotificationSettings\Utility\EmailNotificationSettingsDefinitionTrait;

class SelfRegistrationNotificationSettingsDefinition implements EmailNotificationSettingsDefinitionInterface
{
    use EmailNotificationSettingsDefinitionTrait;

    public const SEND_ADMIN_USER_REGISTER_COMPLETE = 'send_admin_user_register_complete';

    public const FIELDS = [
        self::SEND_ADMIN_USER_REGISTER_COMPLETE,
    ];

    /**
     * @param \Cake\Form\Schema $schema An instance of schema
     * @return \Cake\Form\Schema
     */
    public function buildSchema(Schema $schema)
    {
        foreach (static::FIELDS as $fieldName) {
            $schema->addField($fieldName, ['type' => 'boolean', 'default' => true]);
        }

        return $schema;
    }

    /**
     * @param \Cake\Validation\Validator $validator An instance of validator
     * @return \Cake\Validation\Validator
     */
    public function buildValidator(Validator $validator)
    {
        foreach (static::FIELDS as $fieldName) {
            $validator->boolean($fieldName, __('An email notification setting should be a boolean.'));
        }

        return $validator;
    }
}
