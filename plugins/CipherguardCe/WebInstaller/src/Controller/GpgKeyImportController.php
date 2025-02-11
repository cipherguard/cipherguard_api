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
namespace Cipherguard\WebInstaller\Controller;

use Cake\Core\Exception\CakeException;
use Cake\Utility\Hash;
use Cipherguard\WebInstaller\Form\GpgKeyForm;

class GpgKeyImportController extends AbstractGpgKeyController
{
    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->stepInfo['template'] = 'Pages/gpg_key_import';
        $this->stepInfo['generate_key_cta'] = '/install/gpg_key';
    }

    /**
     * @inheritDoc
     */
    protected function validateData(array $data): void
    {
        $form = new GpgKeyForm();
        $confIsValid = $form->execute($data);
        $this->set('formExecuteResult', $form);
        if (!$confIsValid) {
            $errors = Hash::flatten($form->getErrors());
            $errorMessage = implode('; ', $errors);
            throw new CakeException(__('The data entered are not correct: {0}', $errorMessage));
        }
    }
}
