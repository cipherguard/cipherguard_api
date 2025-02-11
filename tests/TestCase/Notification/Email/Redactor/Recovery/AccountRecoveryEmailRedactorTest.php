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
 * @since         3.4.0
 */

namespace App\Test\TestCase\Notification\Email\Redactor\Recovery;

use App\Controller\Users\UsersRecoverController;
use App\Test\Factory\AuthenticationTokenFactory;
use App\Test\Factory\UserFactory;
use App\Test\Lib\AppIntegrationTestCase;
use App\Test\Lib\Model\EmailQueueTrait;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use Cipherguard\EmailDigest\Test\Factory\EmailQueueFactory;

class AccountRecoveryEmailRedactorTest extends AppIntegrationTestCase
{
    use EmailQueueTrait;

    public function setUp(): void
    {
        parent::setUp();
        Configure::write('cipherguard.webInstaller.configured', true);
    }

    public function tearDown(): void
    {
        Configure::delete('cipherguard.webInstaller.configured');
        parent::tearDown();
    }

    public function testAccountRecoveryEmailRedactor()
    {
        $this->getJson('/auth/is-authenticated.json');

        $user = UserFactory::make()->withAvatar()->user()->persist();

        /** @var \App\Model\Table\UsersTable $Users */
        $Users = TableRegistry::getTableLocator()->get('Users');
        /** @var \App\Model\Entity\User $user */
        $user = $Users->findByUsername($user->username)->first();
        $token = AuthenticationTokenFactory::make()->persist();
        $case = 'default';
        $event = new Event(UsersRecoverController::RECOVER_SUCCESS_EVENT_NAME, null, compact('user', 'token', 'case'));
        EventManager::instance()->dispatch($event);

        $this->assertSame(1, EmailQueueFactory::count());
        $this->assertEmailIsInQueue([
            'email' => $user->username,
            'subject' => "Your account recovery, {$user->profile->first_name}!",
            'template' => 'AN/user_recover',
        ]);
        $emailVars = EmailQueueFactory::find()->firstOrFail()->get('template_vars');
        $this->assertSame($case, $emailVars['body']['case']);
        $this->assertSame($user->username, $emailVars['body']['user']['username']);
        $this->assertSame($user->profile->first_name, $emailVars['body']['user']['profile']['first_name']);
        $this->assertSame($token->token, $emailVars['body']['token']['token']);
    }
}
