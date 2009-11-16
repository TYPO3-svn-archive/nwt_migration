<?php
class migration_1234567891 extends tx_nwtmigration_migration_basic {
	function getTitle() {
		return 'another migration';
	}
	
	function up() {
	}
	
	function down(){		
	}
	
	function getStatus() {
		return self::STATUS_SUCCESSFUL;
	}
	
}
?>