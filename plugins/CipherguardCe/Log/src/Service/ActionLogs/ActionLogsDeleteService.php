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
 * @since         3.6.0
 */
namespace Cipherguard\Log\Service\ActionLogs;

use App\Utility\UserAction;
use Cake\Chronos\ChronosDate;
use Cake\Http\Exception\InternalErrorException;
use Cake\ORM\TableRegistry;

class ActionLogsDeleteService
{
    public const AUTH_LOGIN_LOGIN_GET = 'AuthLogin.loginGet';
    public const AUTH_CHECK_SESSION_CHECK_SESSION_GET = 'AuthCheckSession.checkSessionGet';
    public const AUTH_IS_AUTHENTICATED_IS_AUTHENTICATED = 'AuthIsAuthenticated.isAuthenticated';

    public const ALL_ACTION_NAME = [
        self::AUTH_LOGIN_LOGIN_GET,
        self::AUTH_CHECK_SESSION_CHECK_SESSION_GET,
        self::AUTH_IS_AUTHENTICATED_IS_AUTHENTICATED,
    ];

    /**
     * @param string $actionName Action name (no UUID) which logs will be deleted.
     * @param \Cake\Chronos\ChronosDate|null $cutOffDate Delete entries strictly older than this date. Delete all if null
     * @return void
     */
    public function delete(string $actionName, ?ChronosDate $cutOffDate = null): void
    {
        $this->validateActionName($actionName);
        $conditions = [
            'action_id' => UserAction::actionId($actionName),
        ];
        if ($cutOffDate) {
            $conditions['created <'] = $cutOffDate;
        }
        /** @var \Cipherguard\Log\Model\Table\ActionLogsTable $ActionLogsTable */
        $ActionLogsTable = TableRegistry::getTableLocator()->get('Cipherguard/Log.ActionLogs');
        $ActionLogsTable->deleteAll($conditions);
    }

    /**
     * @param string $actionName Action name
     * @return void
     * @throws \Cake\Http\Exception\InternalErrorException if the action name passed is not among the constants defined in this class
     */
    protected function validateActionName(string $actionName): void
    {
        if (!in_array($actionName, self::ALL_ACTION_NAME)) {
            throw new InternalErrorException('The action name is not defined.');
        }
    }
}
