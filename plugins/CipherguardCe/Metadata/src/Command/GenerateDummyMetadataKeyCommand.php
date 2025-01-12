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
use Cake\Core\Configure;
use Cipherguard\Metadata\Service\Migration\GenerateDummyMetadataKeyService;

class GenerateDummyMetadataKeyCommand extends CipherguardCommand
{
    /**
     * @inheritDoc
     */
    public static function getCommandDescription(): string
    {
        return 'Generate a metadata private/public key pair. '
            . 'Share it with server and users keys. '
            . 'For testing purpose ONLY. '
            . 'Requires both DEBUG and CIPHERGUARD_SELENIUM_ACTIVE flags.';
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        parent::execute($args, $io);

        if (!Configure::read('debug') || !Configure::read('cipherguard.selenium.active')) {
            $io->out('Please enable DEBUG and CIPHERGUARD_SELENIUM_ACTIVE flags.');

            return $this->errorCode();
        }

        $verbose = false;
        if ($args->getOption('verbose')) {
            $verbose = true;
        }
        try {
            $key = (new GenerateDummyMetadataKeyService())->generate($verbose);
            $io->out('New key generated and encrypted for users: ' . $key->fingerprint);
        } catch (\Exception $e) {
            $io->err($e->getMessage());

            return $this->errorCode();
        }

        return $this->successCode();
    }
}
