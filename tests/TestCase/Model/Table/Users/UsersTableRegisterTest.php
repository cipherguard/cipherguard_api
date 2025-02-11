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
 * @since         3.11.0
 */

namespace App\Test\TestCase\Model\Table\Users;

use App\Error\Exception\ValidationException;
use App\Model\Entity\AuthenticationToken;
use App\Model\Entity\User;
use App\Model\Table\UsersTable;
use App\Model\Validation\EmailValidationRule;
use App\Test\Factory\AuthenticationTokenFactory;
use App\Test\Factory\RoleFactory;
use App\Test\Factory\UserFactory;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;

/**
 * @group emailValidation
 */
class UsersTableRegisterTest extends TestCase
{
    use TruncateDirtyTables;

    /**
     * @var \App\Model\Table\UsersTable
     */
    public $Users;

    public function setUp(): void
    {
        parent::setUp();
        $this->Users = TableRegistry::getTableLocator()->get('Users');
    }

    public function testUsersTableRegister_Email_WithRegexp()
    {
        RoleFactory::make()->user()->persist();
        // Rule is an "a" followed by a b.
        $regex = '/a(b)/';
        Configure::write(EmailValidationRule::REGEX_CHECK_KEY, $regex);
        $profile = [
            'first_name' => '傅',
            'last_name' => '苹',
        ];

        $validUsernames = [
            'ab@test.test',
            'ab@test',
            'ab',
        ];
        foreach ($validUsernames as $username) {
            $this->Users->register(compact('username', 'profile'));
        }
        $this->assertSame(count($validUsernames), UserFactory::count());
    }

    public function testUsersTableRegister_ValidEmail_WithNotMatchingRegexpShouldFail()
    {
        RoleFactory::make()->user()->persist();
        // Rule is an "a" followed by a b.
        $regex = '/a(b)/';
        Configure::write(EmailValidationRule::REGEX_CHECK_KEY, $regex);
        $profile = [
            'first_name' => '傅',
            'last_name' => '苹',
        ];

        $username = 'b@test.test';
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Could not validate user data.');
        $this->Users->register(compact('username', 'profile'));
    }

    /**
     * @see \App\Test\TestCase\Model\Table\Users\UsernameCaseSensitiveTest
     */
    public function testUsersTableRegister_By_Default_Cannot_Register_Existing_Active_Username()
    {
        RoleFactory::make()->user()->persist();
        /** @var \App\Model\Entity\User $existingUser */
        $existingUser = UserFactory::make()->user()->active()->persist();
        $username = strtoupper($existingUser->username);
        $profile = [
            'first_name' => '傅',
            'last_name' => '苹',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Could not validate user data.');
        $this->Users->register(compact('username', 'profile'));
    }

    /**
     * Given a user with the same upper-cased username already exists
     * And the search is case-sensitive
     * Then the user with the same username (but lower-cased) can register
     */
    public function testUsersTableRegister_Register_Active_Username_Case_Sensitive_Success()
    {
        Configure::write(UsersTable::CIPHERGUARD_SECURITY_USERNAME_CASE_SENSITIVE, true);

        RoleFactory::make()->user()->persist();
        $username = 'john@cipherguard.com';
        /** @var \App\Model\Entity\User $existingUser */
        UserFactory::make(['username' => strtoupper($username)])->user()->active()->persist();
        $profile = [
            'first_name' => '傅',
            'last_name' => '苹',
        ];

        $registeredUser = $this->Users->register(compact('username', 'profile'));
        $this->assertInstanceOf(User::class, $registeredUser);
        $this->assertSame(2, UserFactory::count());
        $token = AuthenticationTokenFactory::firstOrFail([
            'user_id' => $registeredUser->id,
            'type' => AuthenticationToken::TYPE_REGISTER,
        ]);
        $this->assertInstanceOf(AuthenticationToken::class, $token);
    }
}
