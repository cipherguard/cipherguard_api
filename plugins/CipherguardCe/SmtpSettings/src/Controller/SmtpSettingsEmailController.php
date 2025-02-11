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
 * @since         3.8.0
 */
namespace Cipherguard\SmtpSettings\Controller;

use App\Controller\AppController;
use App\Error\Exception\FormValidationException;
use Cipherguard\SmtpSettings\Service\SmtpSettingsTestEmailService;

class SmtpSettingsEmailController extends AppController
{
    /**
     * SmtpSettings Send Test Email
     *
     * @param \Cipherguard\SmtpSettings\Service\SmtpSettingsTestEmailService $sendTestEmailService Service injected for unit test purposes
     * @return void
     */
    public function sendTestEmail(SmtpSettingsTestEmailService $sendTestEmailService)
    {
        $this->User->assertIsAdmin();

        try {
            $sendTestEmailService->sendTestEmail($this->getRequest()->getData());
            $debug = $sendTestEmailService->getTrace();
            $this->success(__('The operation was successful.'), compact('debug'));
        } catch (FormValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $debug = $sendTestEmailService->getTrace();
            $this->error($e->getMessage(), compact('debug'), 400);
        }
    }
}
