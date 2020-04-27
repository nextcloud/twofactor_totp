<?php
/**
 * @author Semih Serhat Karakaya <karakayasemi@itu.edu.tr>
 *
 * Two-factor TOTP
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\TwoFactor_Totp\Tests\Command;

use OCP\IDBConnection;
use Test\Traits\UserTrait;
use OCA\TwoFactor_Totp\Command\SetSecretVerificationStatusCommand;
use OCA\TwoFactor_Totp\Db\TotpSecret;
use OCA\TwoFactor_Totp\Db\TotpSecretMapper;
use Symfony\Component\Console\Tester\CommandTester;
use Test\TestCase;

/**
 * Class SetSecretVerificationStatusCommandTest
 *
 * @group DB
 */
class SetSecretVerificationStatusCommandTest extends TestCase {
	use UserTrait;

	/** @var IDBConnection */
	private $db;

	/** @var CommandTester */
	private $commandTester;

	/** @var string  */
	private $dbTable = 'twofactor_totp_secrets';

	protected function setUp(): void {
		parent::setUp();

		$this->db = \OC::$server->getDatabaseConnection();
		$mapper = new TotpSecretMapper($this->db);
		$command = new SetSecretVerificationStatusCommand($mapper, \OC::$server->getUserManager());
		$this->commandTester = new CommandTester($command);

		$this->createUser('user1');
		$this->createUser('user2');
		$mapper->insert(TotpSecret::fromParams([
			'userId' => 'user1',
			'secret' => 'test',
			'verified' => false
		]));
	}

	protected function tearDown(): void {
		parent::tearDown();
		$query = $this->db->getQueryBuilder()->delete($this->dbTable);
		$query->execute();
	}

	/**
	 * @dataProvider inputProvider
	 * @param array $input
	 * @param string $expectedOutput
	 */
	public function testCommandInput($input, $expectedOutput) {
		$this->commandTester->execute($input);
		$output = $this->commandTester->getDisplay();
		$this->assertStringContainsString($expectedOutput, $output);
	}

	public function inputProvider() {
		return [
			[
				['set-verified' => 'true', '--uid' => ['user1']], 'The secret of user1 is verified'
			],
			[
				['set-verified' => 'false', '--uid' => ['user1']], 'The secret of user1 is unverified'
			],
			[
				['set-verified' => 'true',  '--uid' => ['user3']], 'User user3 does not exist'
			],
			[
				['set-verified' => 'true', '-u' => ['user2']], 'User has no secret'
			],
			[
				['set-verified' => 'true',  '--all' => ''], 'The status of all TOTP secrets has been set to verified'
			],
			[
				['set-verified' => 'false', '--all' => ''], 'The status of all TOTP secrets has been set to unverified'
			],
			[
				['set-verified' => 'false'], 'No user input specified.'
			],
			[
				['set-verified' => 'error'], 'Argument value is not valid.'
			],
		];
	}
}
