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

namespace Cipherguard\Mobile\Controller\Transfers;

use App\Controller\AppController;
use App\Model\Table\AvatarsTable;
use Cake\Http\Exception\BadRequestException;
use Cake\Validation\Validation;

/**
 * Class TransfersViewController
 *
 * @package Cipherguard\Mobile\Controller\Transfers
 */
class TransfersViewController extends AppController
{
    /**
     * @var \Cipherguard\Mobile\Model\Table\TransfersTable
     */
    protected $Transfers;

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Transfers = $this->fetchTable('Cipherguard/Mobile.Transfers');
    }

    /**
     * View a transfer status
     *
     * @param string $id transfer uuid
     * @throws \Cake\Datasource\Exception\RecordNotFoundException if transfer does not exist
     * @return void
     */
    public function view(string $id): void
    {
        // Check request sanity
        if (!Validation::uuid($id)) {
            throw new BadRequestException(__('The transfer id is not valid.'));
        }

        // Contain options
        $whitelist = ['contain' => ['user', 'user.profile']];
        $options = $this->QueryString->get($whitelist);
        $contain = empty($options['contain']['user']) ? [] : ['Users'];
        $contain = empty($options['contain']['user.profile']) ? $contain : [
            'Users.Profiles' => AvatarsTable::addContainAvatar(),
        ];

        $transfer = $this->Transfers->find()
            ->contain($contain)
            ->where([
                $this->Transfers->aliasField('id') => $id,
                $this->Transfers->aliasField('user_id') => $this->User->id(),
            ])
            ->firstOrFail();

        $this->success(__('The operation was successful.'), $transfer);
    }
}
