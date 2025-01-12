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
 * @since         3.0.0
 */
namespace Cipherguard\EmailDigest\Controller\EmailDigest;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use Cipherguard\EmailDigest\Service\PreviewEmailBatchService;

class PreviewNextEmailsBatchController extends AppController
{
    /**
     * @inheritDoc
     */
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        if (Configure::read('debug') && Configure::read('cipherguard.selenium.active')) {
            $this->Authentication->allowUnauthenticated(['preview']);
        } else {
            throw new NotFoundException();
        }

        return parent::beforeFilter($event);
    }

    /**
     * @return \Cake\Http\Response
     * @throws \Exception
     */
    public function preview()
    {
        $batchLimit = $this->getRequest()->getQueryParams()['limit'] ?? 10;

        $previewService = new PreviewEmailBatchService();
        $previews = $previewService->previewNextEmailsBatch($batchLimit);

        $previewContent = '';
        foreach ($previews as $preview) {
            $previewContent .= '<pre>' . $preview->getHeaders() . '</pre>';
            $previewContent .= $preview->getContent();
            $previewContent .= '<hr>';
        }

        return $this->response
            ->withStringBody($previewContent)
            ->withType('html');
    }
}
