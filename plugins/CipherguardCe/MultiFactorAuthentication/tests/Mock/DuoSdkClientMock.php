<?php
declare(strict_types=1);

/**
 * Cipherguard ~ Open source password manager for teams
 * Copyright (c) Khulnasoft Ltd' (https://www.cipherguard.khulnasoft.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Khulnasoft Ltd' (https://www.cipherguard.khulnasoft.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.cipherguard.khulnasoft.com Cipherguard(tm)
 * @since         3.11.0
 */

namespace Cipherguard\MultiFactorAuthentication\Test\Mock;

use App\Model\Entity\User;
use Duo\DuoUniversal\Client;
use Duo\DuoUniversal\DuoException;

class DuoSdkClientMock
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject The CakePHP mock object for Client
     */
    public $stub;
    /**
     * @var string The Duo API hostname setting
     */
    public $apiHostname;
    /**
     * @var string The Duo state
     */
    public $state;
    /**
     * @var string The Duo domain
     */
    public $iss;
    /**
     * @var string The Duo authentication URL/endpoint
     */
    public $duoAuthUrl;

    /**
     * Constructor.
     *
     * @param \PHPUnit\Framework\TestCase $testCase Test case
     */
    public function __construct(\PHPUnit\Framework\TestCase $testCase)
    {
        $this->apiHostname = 'api-45e9f2ca.duosecurity.com';
        $this->state = 'duo-not-so-random-state';
        $this->iss = "https://$this->apiHostname/oauth/v1/token";
        $this->duoAuthUrl = "https://$this->apiHostname/oauth/v1/authorize?sate=$this->state";
        $this->stub = $testCase->getMockBuilder(Client::class)
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * Get an instance of the Duo Client object
     *
     * @return Client
     */
    public function getClient(): Client
    {
        /** @var Client $mockedClient */
        $mockedClient = $this->stub;

        return $mockedClient;
    }

    /**
     * @param \PHPUnit\Framework\TestCase $testCase Test case
     * @param User $user Test user
     * @return static
     */
    public static function createDefault(\PHPUnit\Framework\TestCase $testCase, User $user): self
    {
        $mock = new self($testCase);
        $mock->mockSuccessGenerateState($mock->state);
        $mock->mockSuccessCreateAuthUrl($mock->apiHostname, $mock->state);
        $mock->mockSuccessHealthCheck();
        $mock->mockSuccessExchangeAuthorizationCodeFor2FAResult($mock->iss, $user->username);

        return $mock;
    }

    /**
     * @param \PHPUnit\Framework\TestCase $testCase Test case
     * @return static
     */
    public static function createWithSuccessHealthcheck(\PHPUnit\Framework\TestCase $testCase): self
    {
        $mock = new self($testCase);
        $mock->mockSuccessHealthCheck();

        return $mock;
    }

    /**
     * @param \PHPUnit\Framework\TestCase $testCase Test case
     * @return static
     */
    public static function createWithExchangeAuthorizationCodeFor2FAResultThrowingException(\PHPUnit\Framework\TestCase $testCase): self
    {
        $mock = new self($testCase);
        $mock->mockErrorExchangeAuthorizationCodeFor2FAResult();

        return $mock;
    }

    /**
     * @param \PHPUnit\Framework\TestCase $testCase Test case
     * @param User $user Test user
     * @return static
     */
    public static function createWithWrongExchangeAuthorizationCodeFor2FAResultIss(\PHPUnit\Framework\TestCase $testCase, User $user): self
    {
        $iss = 'https://api-00f0f2ff.duosecurity.com/oauth/v1/token';
        $mock = new self($testCase);
        $mock->mockSuccessExchangeAuthorizationCodeFor2FAResult($iss, $user->username);

        return $mock;
    }

    /**
     * @param \PHPUnit\Framework\TestCase $testCase Test case
     * @return static
     */
    public static function createWithWrongExchangeAuthorizationCodeFor2FAResultSub(\PHPUnit\Framework\TestCase $testCase): self
    {
        $username = 'wrong@cipherguard.khulnasoft.com';
        $mock = new self($testCase);
        $mock->mockSuccessExchangeAuthorizationCodeFor2FAResult($mock->iss, $username);

        return $mock;
    }

    /**
     * @param \PHPUnit\Framework\TestCase $testCase Test case
     * @param string $sub the sub to use
     * @return static
     */
    public static function createWithExchangeAuthorizationCodeFor2FAResultSub(\PHPUnit\Framework\TestCase $testCase, string $sub): self
    {
        $mock = new self($testCase);
        $mock->mockSuccessExchangeAuthorizationCodeFor2FAResult($mock->iss, $sub);

        return $mock;
    }

    /**
     * @param string $state Duo state to be generated by the SDK
     * @return $this
     */
    public function mockSuccessGenerateState(string $state)
    {
        $this->stub->method('generateState')
            ->willReturn($state);

        return $this;
    }

    /**
     * @param string $apiHostname Duo API hostname used to generate the auth URL
     * @param string $state Duo state used to generate the auth URL
     * @return $this
     */
    public function mockSuccessCreateAuthUrl(string $apiHostname, string $state)
    {
        $this->duoAuthUrl = "https://$apiHostname/oauth/v1/authorize?sate=$state";
        $this->stub->method('createAuthUrl')
            ->willReturn($this->duoAuthUrl);

        return $this;
    }

    /**
     * @return $this
     */
    public function mockSuccessHealthCheck()
    {
        $this->stub->method('healthCheck')
            ->willReturn([
                'stat' => 'OK',
                'response' => [
                    'timestamp' => 1234567890,
                ],
            ]);

        return $this;
    }

    /**
     * @param string $iss Duo iss (endpoint) used to generate the 2FA result
     * @param string $sub Duo sub (user) used to generate the 2FA result
     * @return $this
     */
    public function mockSuccessExchangeAuthorizationCodeFor2FAResult(string $iss, string $sub)
    {
        $callbackPayload = [
            'iss' => $iss,
            'sub' => $sub,
        ];
        $this->stub->method('exchangeAuthorizationCodeFor2FAResult')
            ->willReturn($callbackPayload);

        return $this;
    }

    /**
     * @return $this
     */
    public function mockErrorHealthCheck()
    {
        $this->stub->method('healthCheck')
            ->willThrowException(new DuoException());

        return $this;
    }

    /**
     * @return $this
     */
    public function mockErrorCreateAuthUrl()
    {
        $this->stub->method('createAuthUrl')
            ->willThrowException(new DuoException());

        return $this;
    }

    /**
     * @return $this
     */
    public function mockErrorExchangeAuthorizationCodeFor2FAResult()
    {
        $this->stub->method('exchangeAuthorizationCodeFor2FAResult')
            ->willThrowException(new DuoException());

        return $this;
    }

    /**
     * @return $this
     */
    public function mockInvalidExchangeAuthorizationCodeFor2FAResult()
    {
        $this->stub->method('exchangeAuthorizationCodeFor2FAResult')
            ->willReturn('OK');

        return $this;
    }
}
