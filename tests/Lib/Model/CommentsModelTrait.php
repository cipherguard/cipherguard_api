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

namespace App\Test\Lib\Model;

use App\Utility\UuidFactory;

trait CommentsModelTrait
{
    /**
     * Get a dummy comment with test data.
     * The comment returned passes a default validation.
     *
     * @param array|null $data Custom data that will be merged with the default dummy comment.
     * @return array Comment data
     */
    public static function getDummyComment(?array $data = []): array
    {
        $entityContent = [
            'user_id' => UuidFactory::uuid('user.id.ada'),
            'foreign_key' => UuidFactory::uuid('resource.id.bower'),
            'foreign_model' => 'Resource',
            'content' => 'this is a test comment',
            'parent_id' => null,
            'created_by' => UuidFactory::uuid('user.id.ada'),
            'modified_by' => UuidFactory::uuid('user.id.ada'),
        ];
        $entityContent = array_merge($entityContent, $data);

        return $entityContent;
    }

    /**
     * Asserts that an object has all the attributes a comment should have.
     *
     * @param object $comment
     */
    protected function assertCommentAttributes($comment)
    {
        $attributes = ['id', 'parent_id', 'foreign_key', 'foreign_model', 'content', 'created', 'modified', 'created_by', 'modified_by'];
        $this->assertObjectHasAttributes($attributes, $comment);
    }
}
