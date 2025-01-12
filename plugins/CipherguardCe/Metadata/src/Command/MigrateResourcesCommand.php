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
namespace Cipherguard\Metadata\Command;

use App\Command\CipherguardCommand;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Http\Exception\BadRequestException;
use Cipherguard\Metadata\Service\Migration\MigrateAllV4ResourcesToV5Service;

class MigrateResourcesCommand extends CipherguardCommand
{
    /**
     * @inheritDoc
     */
    public static function getCommandDescription(): string
    {
        return __('Migrate V4 resources to V5.');
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        parent::execute($args, $io);

        try {
            $result = (new MigrateAllV4ResourcesToV5Service())->migrate();
        } catch (BadRequestException $e) {
            $msg = $e->getMessage();
            $msg .= "\n";
            $msg .= __('To enable, set "allow_creation_of_v5_resources" metadata settings to true via `update_metadata_types_settings` command.'); // phpcs:ignore

            $io->abort($msg);
        }

        if (isset($result['migrated']) && count($result['migrated'])) {
            $this->success(__('{0} resources were migrated.', count($result['migrated'])), $io);
        }
        if ($result['success']) {
            $io->success(__('All resources successfully migrated.'));
        } else {
            $this->error(__('All resources could not migrated.'), $io);
            $this->error(__('See errors:'), $io);
            $errors = $result['errors'];
            foreach ($errors as $error) {
                $this->error($error['error_message'], $io);
            }

            return $this->errorCode();
        }

        return $this->successCode();
    }
}
