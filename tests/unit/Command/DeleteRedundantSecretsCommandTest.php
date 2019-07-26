<?php
/**
 * @author Sıla Boyraz <boyrazs15@itu.edu.tr>
 *
 * @copyright Copyright (c) 2019, ownCloud GmbH
 * @license AGPL-3.0
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
use OCA\TwoFactor_Totp\Command\DeleteRedundantSecretsCommand;
use OCA\TwoFactor_Totp\Db\TotpSecret;
use OCA\TwoFactor_Totp\Db\TotpSecretMapper;
use Symfony\Component\Console\Tester\CommandTester;
use Test\TestCase;

/**
 * Class DeleteRedundantSecretsCommandTest
 *
 * @group DB
 */
class DeleteRedundantSecretsCommandTest extends TestCase {
	use UserTrait;

	/** @var IDBConnection */
	private $db;

	/** @var CommandTester */
	private $commandTester;

	/** @var string  */
	private $dbTable = 'twofactor_totp_secrets';

	protected function setUp() {
		parent::setUp();

		$this->db = \OC::$server->getDatabaseConnection();
		$mapper = new TotpSecretMapper($this->db);
		$command = new DeleteRedundantSecretsCommand($mapper, \OC::$server->getUserManager());
		$this->commandTester = new CommandTester($command);

		$this->createUser('user1');
		$mapper->insert(TotpSecret::fromParams([
			'userId' => 'user1',
			'secret' => 'test',
			'verified' => false
		]));
		$this->createUser('user2');
		$mapper->insert(TotpSecret::fromParams([
			'userId' => 'user2',
			'secret' => 'test',
			'verified' => false
		]));
		$mapper->insert(TotpSecret::fromParams([
			'userId' => 'nonexisting_user',
			'secret' => 'test',
			'verified' => false
		]));
	}

	protected function tearDown() {
		parent::tearDown();
		$query = $this->db->getQueryBuilder()->delete($this->dbTable);
		$query->execute();
	}

	public function testCommandInput() {
		$this->commandTester->execute([]);
		$output = $this->commandTester->getDisplay();
		$this->assertContains("The redundant secret of nonexisting_user is deleted.", $output);
		$this->assertContains("1 redundant secrets are deleted", $output);
	}
}
