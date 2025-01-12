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
 * @since         2.13.0
 */

namespace Cipherguard\Folders\Notification\Email;

use App\Notification\Email\Email;
use App\Notification\Email\EmailCollection;
use App\Notification\Email\SubscribedEmailRedactorInterface;
use App\Notification\Email\SubscribedEmailRedactorTrait;
use App\Utility\UserAccessControl;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cipherguard\Folders\Model\Entity\Folder;
use Cipherguard\Folders\Service\Folders\FoldersCreateService;
use Cipherguard\Locale\Service\LocaleService;
use InvalidArgumentException;

class CreateFolderEmailRedactor implements SubscribedEmailRedactorInterface
{
    use SubscribedEmailRedactorTrait;

    /**
     * @var string
     * @see templates/email/html/LU/folder_delete.php
     */
    public const TEMPLATE = 'Cipherguard/Folders.LU/folder_create';

    public const TEMPLATE_V5 = 'Cipherguard/Metadata.LU/folder_create_v5';

    /**
     * @var \App\Model\Table\UsersTable
     */
    private $usersTable;

    /**
     * Email redactor constructor.
     */
    public function __construct()
    {
        $this->usersTable = TableRegistry::getTableLocator()->get('Users');
    }

    /**
     * @return array
     */
    public function getSubscribedEvents(): array
    {
        return [
            FoldersCreateService::FOLDERS_CREATE_FOLDER_EVENT,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getNotificationSettingPath(): ?string
    {
        return 'send.folder.create';
    }

    /**
     * @param \Cake\Event\Event $event Event
     * @return \App\Notification\Email\EmailCollection
     */
    public function onSubscribedEvent(Event $event): EmailCollection
    {
        $emailCollection = new EmailCollection();

        $folder = $event->getData('folder');
        if (!$folder) {
            throw new InvalidArgumentException('`folder` is missing from event data.');
        }

        $uac = $event->getData('uac');
        if (!$uac) {
            throw new InvalidArgumentException('`uac` is missing from event data.');
        }

        $isV5 = $event->getData('isV5');
        if (is_null($isV5)) {
            $isV5 = false;
        }

        $email = $this->createEmail($folder, $uac, $isV5);

        return $emailCollection->addEmail($email);
    }

    /**
     * @param \Cipherguard\Folders\Model\Entity\Folder $folder Folder entity
     * @param \App\Utility\UserAccessControl $uac UserAccessControl
     * @param bool $isV5 If folder entity is V5 or not.
     * @return \App\Notification\Email\Email
     */
    private function createEmail(Folder $folder, UserAccessControl $uac, bool $isV5): Email
    {
        $recipient = $this->usersTable->findFirstForEmail($uac->getId());
        $subject = (new LocaleService())->translateString(
            $recipient->locale,
            function () use ($folder, $isV5) {
                $subject = __('You added the folder {0}', $folder->name);
                if ($isV5) {
                    $subject = __('You added a new folder');
                }

                return $subject;
            }
        );

        $template = self::TEMPLATE;
        if ($isV5) {
            $template = self::TEMPLATE_V5;
        }

        return new Email(
            $recipient,
            $subject,
            [
                'body' => [
                    'user' => $recipient,
                    'folder' => $folder,
                ],
                'title' => $subject,
            ],
            $template
        );
    }
}
