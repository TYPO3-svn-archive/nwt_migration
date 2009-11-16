<?php

abstract class tx_nwtmigration_migration_basic  {
	
	
	/**
	 * Status is unknown (perhaps an error occured)
	 * @var int
	 */
	const STATUS_UNKNOWN = 0;
	
	/**
	 * Migration is nor yet applied
	 * @var int
	 */
	const STATUS_NOTAPPLIED = 1;
	
	/**
	 * Migration is successfully applied
	 * @var int
	 */
	const STATUS_SUCCESSFUL = 2;
	
	/**
	 * Migration failed during "up"
	 * @var int
	 */
	const STATUS_FAILED = 3;
	
	
	/**
	 * Run Up
	 * @var int
	 */
	const RUNMODE_UP = 1;
	/**
	 * Run Down
	 * @var int
	 */
	const RUNMODE_DOWN = 2;
	
	
	/**
	 * @var tx_nwtmigration_migrationHelper
	 */
	var $helper;
	
	/**
	 * Values in this array are stored after "up" and restored before running "down"
	 * @var array
	 */
	var $register = array();
	
	
	/**
	 * get the title of the current migration
	 * 
	 * @return string
	 */
	abstract public function getTitle();
	
	/**
	 * apply the migration
	 *
	 * @return bool true if successful
	 */
	abstract public function up();
	
	/**
	 * revert the migration
	 *
	 * @return bool true if successful
	 */
	abstract public function down();
	
	
	// TODO check function to check if migration can be applied
	
	
	
	/**
	 * Return the ID of the migration, detected from the classname
	 * 
	 * migration ids are timestamps
	 *
	 * @return int the id of the mirgration
	 */
	public function getId() {
			// schould be "migration_1234567890"
		$classname = get_class($this);
		if ($classname != __CLASS__) {
			list($literal, $id) = explode('_', $classname);
			if ($literal == 'migration') {
				return intval($id);
			}
		}
		return false;
	}
	
	/**
	 * detect the current status of the migration
	 *
	 * @return int Status-Code (use STATUS_ constants defined in this class)
	 */
	public function getStatus() {

		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'tx_nwtmigration_migrationslog',
			' migration_id = ' . intval($this->getId()),
			'',
			' crdate DESC ',
			' 1 '
		);
		
		// not log found or last run was "down" and successful
		if (count($rows) != 1
		 || ($rows[0]['was_successful'] == 1
		 	&& $rows[0]['run_mode'] == self::RUNMODE_DOWN)) {
			return self::STATUS_NOTAPPLIED;
		}
		
		// last run was Up and successful
		if ($rows[0]['was_successful'] == 1
		  && $rows[0]['run_mode'] == self::RUNMODE_UP) {
			return self::STATUS_SUCCESSFUL;
		}
		
		// last run wasn't successful (error occured)
		if ($rows[0]['was_successful'] == 0) {
			return self::STATUS_FAILED;
		}
		
		// any other case
		return self::STATUS_UNKNOWN;
	}
	
	
	public function injectHelper(tx_nwtmigration_migrationHelper $helper) {
		$this->helper = $helper;
	}
	
}
?>