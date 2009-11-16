<?php

require_once(t3lib_extMgm::extPath('nwt_migration', 'classes/class.tx_nwtmigration_migration_basic.php'));
require_once(t3lib_extMgm::extPath('nwt_migration', 'tests/fixtures/migrations/class.migration_1234567890.php'));
require_once(t3lib_extMgm::extPath('nwt_migration', 'tests/fixtures/migrations/class.migration_1234567891.php'));


class tx_nwtmigration_migration_basic_testcase extends tx_phpunit_testcase {

	public function testMigrationGetIdReturnsCorrectId() {
		$migration = $this->getMock('tx_nwtmigration_migration_basic', array('getTitle','up','down'), array(), 'migration_1234567881');
		
		$this->assertEquals('1234567881', $migration->getId());
	}
		
	public function testMigrationGetStatusImplementedInMigration() {
		$migration = new migration_1234567891();
		$this->assertEquals(tx_nwtmigration_migration_basic::STATUS_SUCCESSFUL, $migration->getStatus());
	}
	
	public function testMigrationGetStatusReadFromDatabase() {
		$memorizeDB = $GLOBALS['TYPO3_DB'];
		
		$mockDB = $this->getMock('t3lib_db', array('exec_SELECTgetRows'));

		$mockDB->expects($this->once())
			->method('exec_SELECTgetRows')
			->with(
				$this->equalTo('*'),
				$this->equalTo('tx_nwtmigration_migrationslog'),
				$this->equalTo(' migration_id = 1234567880'),
				$this->equalTo(''),
				$this->equalTo(' crdate DESC '),
				$this->equalTo(' 1 ')
				)
			->will($this->returnValue(array(
					array(
						// 'uid' => 1,
						// 'pid' => 0,
						// 'tstamp' => 1228929017,
						// 'crdate' => 1228929017,
						// 'cruser_id' => 1,
						// 'migration_id' => 1234567880,
						'was_successful' => 1,
						'run_mode' => 1, // up
						// 'note' => 'foobarnote'
					))));
		
		$GLOBALS['TYPO3_DB'] = $mockDB;
		
		$migration = $this->getMock('tx_nwtmigration_migration_basic', array('getTitle','up','down'), array(), 'migration_1234567880');

		$this->assertEquals(tx_nwtmigration_migration_basic::STATUS_SUCCESSFUL, $migration->getStatus());

		$GLOBALS['TYPO3_DB'] = $memorizeDB;
	}
	
	
	
	public function testMigrationGetStatusFailedIfLastRunWasNotSuccessful() {
		$memorizeDB = $GLOBALS['TYPO3_DB'];
		
		$mockDB = $this->getMock('t3lib_db', array('exec_SELECTgetRows'));

		$mockDB->expects($this->once())
			->method('exec_SELECTgetRows')
			->will($this->returnValue(array(
					array(
						'was_successful' => 0,
					))));
		
		$GLOBALS['TYPO3_DB'] = $mockDB;
		
		$migration = $this->getMock('tx_nwtmigration_migration_basic', array('getTitle','up','down'), array(), 'migration_1234567882');

		$this->assertEquals(tx_nwtmigration_migration_basic::STATUS_FAILED, $migration->getStatus());

		$GLOBALS['TYPO3_DB'] = $memorizeDB;
	}
	
	
	
	public function testMigrationGetStatusSuccessfulIfLastRunWasUpAndSuccessful() {
		$memorizeDB = $GLOBALS['TYPO3_DB'];
		
		$mockDB = $this->getMock('t3lib_db', array('exec_SELECTgetRows'));

		$mockDB->expects($this->once())
			->method('exec_SELECTgetRows')
			->will($this->returnValue(array(
					array(
						'was_successful' => 1,
						'run_mode' => 1, // up
					))));
		
		$GLOBALS['TYPO3_DB'] = $mockDB;
		
		$migration = $this->getMock('tx_nwtmigration_migration_basic', array('getTitle','up','down'), array(), 'migration_1234567883');

		$this->assertEquals(tx_nwtmigration_migration_basic::STATUS_SUCCESSFUL, $migration->getStatus());

		$GLOBALS['TYPO3_DB'] = $memorizeDB;
	}
	
	
	
	
	
	public function testMigrationGetStatusSuccessfulIfLastRunWasDownAndSuccessful() {
		$memorizeDB = $GLOBALS['TYPO3_DB'];
		
		$mockDB = $this->getMock('t3lib_db', array('exec_SELECTgetRows'));

		$mockDB->expects($this->once())
			->method('exec_SELECTgetRows')
			->will($this->returnValue(array(
					array(
						'was_successful' => 1,
						'run_mode' => 2, // down
					))));
		
		$GLOBALS['TYPO3_DB'] = $mockDB;
		
		$migration = $this->getMock('tx_nwtmigration_migration_basic', array('getTitle','up','down'), array(), 'migration_1234567885');

		$this->assertEquals(tx_nwtmigration_migration_basic::STATUS_NOTAPPLIED, $migration->getStatus());

		$GLOBALS['TYPO3_DB'] = $memorizeDB;
	}
	
	
	
	
	public function testMigrationGetStatusNotappliedIfNoRunLogFound() {
		$memorizeDB = $GLOBALS['TYPO3_DB'];
		
		$mockDB = $this->getMock('t3lib_db', array('exec_SELECTgetRows'));

		$mockDB->expects($this->once())
			->method('exec_SELECTgetRows')
			->will($this->returnValue(array()));
		
		$GLOBALS['TYPO3_DB'] = $mockDB;
		
		$migration = $this->getMock('tx_nwtmigration_migration_basic', array('getTitle','up','down'), array(), 'migration_1234567884');

		$this->assertEquals(tx_nwtmigration_migration_basic::STATUS_NOTAPPLIED, $migration->getStatus());

		$GLOBALS['TYPO3_DB'] = $memorizeDB;
	}

	/*
	public function testMigrationCanRunUp() {
		$fixture = new migration_1234567890();
		$this->assertTrue($fixture->canRunUp());
	}
	
	public function testMigrationCanRunDown() {
		$fixture = new migration_1234567890();
		$this->assertTrue($fixture->canRunDown());
	}
	*/
}
?>