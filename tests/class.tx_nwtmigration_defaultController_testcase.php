<?php

require_once(t3lib_extMgm::extPath('nwt_migration', 'classes/class.tx_nwtmigration_viewHelper.php'));


class tx_nwtmigration_defaultController_testcase extends tx_phpunit_testcase {

	var $fakeExtConf = array();
	
	public function setUp() {
		$this->fakeExtConf = array(
			'path_migrations' => 'EXT:nwt_migration/tests/fixtures/migrations/'
		);
	}
	
	public function testDispatcherRunsDefaultAction() {

		$controller = $this->getMock('tx_nwtmigration_defaultController',array('actionGetMigrationList'));
		
		$controller->expects($this->once())
			->method('actionGetMigrationList')
			->will($this->returnValue('foobar'));

		$return = $controller->dispatch('');
		$this->assertEquals('foobar',$return);
		
	}
	

		
	public function testGetMigrationReturnsMigration() {
		$controller = new tx_nwtmigration_defaultController();
		
		$controller->init($this->fakeExtConf);
		$migration = $controller->getMigration('1234567890');
		
		$this->assertType('tx_nwtmigration_migration_basic', $migration);
	}
	
	
	public function testActionGetMigrationListUsesViewHelper() {
		$viewHelper = $this->getMock('tx_nwtmigration_viewHelper',array('arrayToTable'));
		
		$expectedHeader = array(
			'number',
			'migration_id',
			'migration_title',
			'migration_status',
			'migration_action',
		);

		$expectedData = array(
			array(
				'1.',
				'1234567890',
				'just a migration for testing', 
				'<img src="../../../../typo3conf/ext/nwt_migration/mod1/gfx/icon_add.gif" title="Migration not yet applied" alt="Migration not yet applied" />', 
				'<a href="?action=runUp&mid=1234567890">'. $GLOBALS['LANG']->getLL('migration_runup') .'</a>'
			),
			array(
				'2.',
				'1234567891',
				'another migration', 
				'<img src="../../../../typo3conf/ext/nwt_migration/mod1/gfx/icon_ok2.gif" title="Migration successful applied" alt="Migration successful applied" />',
				'<a href="?action=runDown&mid=1234567891">'. $GLOBALS['LANG']->getLL('migration_rundown') .'</a>'
			),
		);
		
		$viewHelper->expects($this->once())
			->method('arrayToTable')
			->with($this->equalTo($expectedHeader), $this->equalTo($expectedData))
			->will($this->returnValue('foobar'));

		$controller = new tx_nwtmigration_defaultController();
		$controller->init($this->fakeExtConf, $viewHelper);

		$return = $controller->actionGetMigrationList();

		$this->assertEquals('foobar',$return);
	}
	
	
	
	
	
	
	
	
	
	public function testActionGetMigrationListFindsFixtureMigration() {
		$controller = new tx_nwtmigration_defaultController();
		
		$controller->init($this->fakeExtConf);
		
		$return = $controller->findAndGetMigrations();
		$this->assertEquals(2, count($return));
		$this->assertType('array', $return);
		$this->assertEquals(1234567891, $return['1234567891']->getId());
		$this->assertEquals(1234567890, $return['1234567890']->getId());
		$this->assertType('tx_nwtmigration_migration_basic', $return['1234567891']);
		$this->assertType('tx_nwtmigration_migration_basic', $return['1234567890']);
	}
	
	
	
	
	public function testActionRunMigrationRunsUp() {
		$migration = $this->getMock('tx_nwtmigration_migration_basic', array('getTitle','up','down','getStatus'), array(), 'migration_1234567870');
		
		$migration->expects($this->once())
			->method('up')
			->will($this->returnValue(true));
			
		$migration->expects($this->once())
			->method('getStatus')
			->will($this->returnValue(tx_nwtmigration_migration_basic::STATUS_NOTAPPLIED));
		
		$controller = $this->getMock('tx_nwtmigration_defaultController',array('getMigration'));
		
		$controller->expects($this->once())
			->method('getMigration')
			->will($this->returnValue($migration));
			
		$return = $controller->actionRunMigration(tx_nwtmigration_migration_basic::RUNMODE_UP, '1234567870');
		
	}
	
	public function testActionRunMigrationDonotRunUpIfStatusIsFailed()	{
		$migration = $this->getMock('tx_nwtmigration_migration_basic', array('getTitle','up','down','getStatus'), array(), 'migration_1229091541');
		
		$migration->expects($this->never())
			->method('up')
			->will($this->returnValue(true));
			
		$migration->expects($this->once())
			->method('getStatus')
			->will($this->returnValue(tx_nwtmigration_migration_basic::STATUS_FAILED));
		
		$controller = $this->getMock('tx_nwtmigration_defaultController',array('getMigration'));
		
		$controller->expects($this->once())
			->method('getMigration')
			->will($this->returnValue($migration));
			
		$return = $controller->actionRunMigration(tx_nwtmigration_migration_basic::RUNMODE_UP, '1229091541');
	}
}
?>