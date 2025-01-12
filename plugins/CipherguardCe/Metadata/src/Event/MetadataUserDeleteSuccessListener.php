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
 * @since         4.10.0
 */
namespace Cipherguard\Metadata\Event;

use App\Controller\Users\UsersDeleteController;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cipherguard\Metadata\Service\UserMetadataKeysDeleteService;

/**
 * Listens to UsersDeleteController::DELETE_SUCCESS_EVENT_NAME event.
 */
class MetadataUserDeleteSuccessListener implements EventListenerInterface
{
    /**
     * @inheritDoc
     */
    public function implementedEvents(): array
    {
        return [UsersDeleteController::DELETE_SUCCESS_EVENT_NAME => 'deleteUserMetadataKeys'];
    }

    /**
     * Delete user metadata private & session keys after user is deleted.
     *
     * @param \Cake\Event\Event $event The event.
     * @return void
     * @throws \Exception
     */
    public function deleteUserMetadataKeys(Event $event)
    {
        /** @var \App\Model\Entity\User $deletedUser */
        $deletedUser = $event->getData('user');

        (new UserMetadataKeysDeleteService())->delete($deletedUser->get('id'));
    }
}
