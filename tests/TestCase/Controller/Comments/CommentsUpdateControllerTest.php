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

namespace App\Test\TestCase\Controller\Comments;

use App\Test\Factory\CommentFactory;
use App\Test\Factory\RoleFactory;
use App\Test\Factory\UserFactory;
use App\Test\Lib\AppIntegrationTestCase;
use App\Utility\UuidFactory;

class CommentsUpdateControllerTest extends AppIntegrationTestCase
{
    public function testCommentsUpdateController_Success(): void
    {
        RoleFactory::make()->user()->persist();
        $user = UserFactory::make()->user()->persist();
        $comment = CommentFactory::make()->withUser($user)->persist();
        $commentId = $comment->get('id');
        $this->logInAs($user);

        $commentContent = 'updated comment content';
        $putData = ['content' => $commentContent];
        $this->putJson("/comments/$commentId.json", $putData);
        $this->assertSuccess();

        $comment = CommentFactory::find()
            ->where(['id' => $this->_responseJsonBody->id])
            ->first();
        $this->assertEquals($commentContent, $comment->content);
        $this->assertEquals($user->id, $comment->modified_by);
        $this->assertEquals($user->id, $comment->modified_by);

        // Assert that modified time is within one second from the test time.
        $this->assertTrue($comment->modified->wasWithinLast('1 second'));
    }

    public function testCommentsUpdateController_Error_CsrfToken(): void
    {
        $this->disableCsrfToken();

        $user = UserFactory::make()->user()->persist();
        $comment = CommentFactory::make()->withUser($user)->persist();
        $commentId = $comment->get('id');
        $this->logInAs($user);

        $this->put("/comments/$commentId.json");
        $this->assertResponseCode(403);
    }

    public function testCommentsUpdateController_Error_NotAuthenticated(): void
    {
        $commentId = UuidFactory::uuid();
        $postData = [];
        $this->putJson("/comments/$commentId.json", $postData);
        $this->assertAuthenticationError();
    }

    public function testCommentsUpdateController_NotAccessibleFields(): void
    {
        $commentatorId = UserFactory::make()->user()->persist()->get('id');
        $user = UserFactory::make()->user()->persist();
        $comment = CommentFactory::make()->withUser($user)->persist();
        $commentId = $comment->get('id');
        $this->logInAs($user);

        $commentData = [
            'id' => UuidFactory::uuid(),
            'content' => 'updated comment content',
            'parent_id' => UuidFactory::uuid(),
            'created' => '2015-06-06 10:00:00',
            'modified' => '2015-06-06 10:00:00',
            'created_by' => $commentatorId,
            'modified_by' => $commentatorId,
        ];

        $this->putJson("/comments/$commentId.json", $commentData);
        $this->assertSuccess();

        // Check that the groups and its sub-models are saved as expected.
        $commentUpdated = CommentFactory::find()
            ->where(['id' => $this->_responseJsonBody->id])
            ->first();
        $this->assertNotEquals($commentData['id'], $commentUpdated->id);
        $this->assertEquals($commentData['content'], $commentUpdated->content);
        $this->assertNotEquals($commentData['parent_id'], $commentUpdated->parent_id);
        $this->assertNotEquals($commentData['created'], $commentUpdated->created);
        $this->assertNotEquals($commentData['modified'], $commentUpdated->modified);
        $this->assertNotEquals($commentData['created_by'], $commentUpdated->created_by);
        $this->assertNotEquals($commentData['modified_by'], $commentUpdated->modified_by);
    }

    /**
     * Check that calling url without JSON extension throws a 404
     */
    public function testCommentsUpdateController_Error_NotJson(): void
    {
        RoleFactory::make()->user()->persist();
        $user = UserFactory::make()->user()->persist();
        $comment = CommentFactory::make()->withUser($user)->persist();
        $commentId = $comment->get('id');
        $this->logInAs($user);

        $commentContent = 'updated comment content';
        $putData = ['content' => $commentContent];
        $this->put("/comments/$commentId", $putData);
        $this->assertResponseCode(404);
    }
}
