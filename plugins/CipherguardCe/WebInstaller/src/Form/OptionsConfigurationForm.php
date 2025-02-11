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
 * @since         2.0.0
 */
namespace Cipherguard\WebInstaller\Form;

use Cake\Form\Form;
use Cake\Form\Schema;
use Cake\Validation\Validator;

class OptionsConfigurationForm extends Form
{
    /**
     * Options configuration schema.
     *
     * @param \Cake\Form\Schema $schema schema
     * @return \Cake\Form\Schema
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        return $schema
            ->addField('full_base_url', 'string')
            ->addField('force_ssl', ['type' => 'string']);
    }

    /**
     * Validation rules.
     *
     * @param \Cake\Validation\Validator $validator validator
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('full_base_url', 'create', __('A full base url is required.'))
            ->notEmptyString('full_base_url', __('The full base url should not be empty'))
            ->utf8('full_base_url', __('The full base url should be a valid BMP-UTF8 string.'));

        $validator
            ->requirePresence('force_ssl', 'create', __('A force ssl status is required.'))
            ->boolean('force_ssl', __('The force ssl setting should be a valid boolean.'));

        return $validator;
    }
}
