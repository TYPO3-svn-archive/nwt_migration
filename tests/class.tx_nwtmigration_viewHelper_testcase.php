<?php

require_once(t3lib_extMgm::extPath('nwt_migration', 'classes/class.tx_nwtmigration_defaultController.php'));


class tx_nwtmigration_viewHelper_testcase extends tx_phpunit_testcase {

	public function testArrayToTableReturnsHTMLTable() {
		$return = tx_nwtmigration_viewHelper::arrayToTable(array(), array());
		
			// starts with table
		$this->assertRegExp('/^<table(.*)/', $return);
		
			// ends with table
		$this->assertRegExp('/(.*)<\/table>/', $return);
	}
	
	
	public function testgetMigrationStatusIconReturnsImgTag() {
		$return = tx_nwtmigration_viewHelper::getMigrationStatusIcon(tx_nwtmigration_migration_basic::STATUS_UNKNOWN);
		$this->assertRegExp('/^<img(.*)\/>$/', $return);
	}
	
	
	
	public function testgetMigrationActionLinkReturnsEmptyStringIfStatusIsUnknown() {
		$migrationMock = $this->getMock('tx_nwtmigration_migration_basic', array('getStatus','getTitle','up','down'), array(), 'migration_0987654321');

		$migrationMock->expects($this->once())
			->method('getStatus')
			->will($this->returnValue(tx_nwtmigration_migration_basic::STATUS_UNKNOWN));
		
		$return = tx_nwtmigration_viewHelper::getMigrationActionLink($migrationMock);
		$this->assertEquals('', $return);
	}	
	
	public function testgetMigrationActionLinkReturnsEmptyStringIfStatusIsFailed() {
		$migrationMock = $this->getMock('tx_nwtmigration_migration_basic', array('getStatus','getTitle','up','down'));

		$migrationMock->expects($this->once())
			->method('getStatus')
			->will($this->returnValue(tx_nwtmigration_migration_basic::STATUS_FAILED));
		
		$return = tx_nwtmigration_viewHelper::getMigrationActionLink($migrationMock);
		$this->assertEquals('', $return);
	}
	
	
	public function testgetMigrationActionLinkReturnsATagIfStatusIsNotApplied() {
		$migrationMock = $this->getMock('tx_nwtmigration_migration_basic', array('getStatus','getTitle','up','down'));

		$migrationMock->expects($this->once())
			->method('getStatus')
			->will($this->returnValue(tx_nwtmigration_migration_basic::STATUS_NOTAPPLIED));
		
		$return = tx_nwtmigration_viewHelper::getMigrationActionLink($migrationMock);
		
		$this->assertRegExp('/^<a(.*)>(.*)<\/a>$/', $return);
	}
	
	public function testgetMigrationActionLinkReturnsATagIfStatusIsSuccessful() {
		$migrationMock = $this->getMock('tx_nwtmigration_migration_basic', array('getStatus','getTitle','up','down'));

		$migrationMock->expects($this->once())
			->method('getStatus')
			->will($this->returnValue(tx_nwtmigration_migration_basic::STATUS_SUCCESSFUL));
					
		$return = tx_nwtmigration_viewHelper::getMigrationActionLink($migrationMock);
		
		echo $return;
		
		$this->assertRegExp('/^<a(.*)>(.*)<\/a>$/', $return);
	}
	

	public function testGetActionLink()	{
		$return = tx_nwtmigration_viewHelper::getActionLink('this is a text', 'testAction', array('foo' => 'bar'));
		
		$this->assertRegExp('/^<a +href="(.*)?(.*)action=testAction(.*)foo=bar(.*)"(.*)>this is a text<\/a>$/', $return);
	}
	
		 
}
?>