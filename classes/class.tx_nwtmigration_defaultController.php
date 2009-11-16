<?php

require_once(t3lib_extMgm::extPath('nwt_migration', 'classes/class.tx_nwtmigration_viewHelper.php'));
require_once(t3lib_extMgm::extPath('nwt_migration', 'classes/class.tx_nwtmigration_migrationHelper.php'));
require_once(t3lib_extMgm::extPath('nwt_migration', 'classes/class.tx_nwtmigration_migration_basic.php'));

/**
 * 
 *
 */
class  tx_nwtmigration_defaultController {

	var $_extConfig = array();
	
	// TODO extconf um start-page-id erweitern? 
	// TODO TSFE initialisieren und an Mirgration übergeben
	// TODO konfiguration, wo constants geschrieben werden sollen: fileadmin/.../.txt oder sys_template:1234
	
	// TODO source der migration anzeigen können (im BE) 
	
	
	
	/**
	 * @var tx_nwtmigration_viewHelper
	 */
	var $viewHelper;
	
	
	
	/**
	 * run given action
	 *
	 * @param string $action
	 */
	function dispatch($action = '') {
		switch ($action) {
			case 'runUp':
				return $this->actionRunMigration(tx_nwtmigration_migration_basic::RUNMODE_UP, t3lib_div::_GP('mid'));
				break;
			case 'runDown':
				return $this->actionRunMigration(tx_nwtmigration_migration_basic::RUNMODE_DOWN, t3lib_div::_GP('mid'));
				break;
			
			default:
			case 'getMigrationList':
				return $this->actionGetMigrationList();
			break;
		}
	}
	
	
	/**
	 * initilize the controller
	 *
	 * @param array $extConf
	 */
	function init($extConfig, $viewHelper = null) {
		$this->_extConfig = $extConfig;
		
		if (!$viewHelper) { 
			$this->viewHelper = t3lib_div::makeInstance('tx_nwtmigration_viewHelper');
		} else {
			$this->viewHelper = $viewHelper;
		}
	}
	
	
	/**
	 * get a table of all available migrations
	 *
	 * @return string HTML
	 */	
	function actionGetMigrationList() {
		$header = array(
			'number',
			'migration_id',
			'migration_title',
			'migration_status',
			'migration_action',
		);
		
		$data = array();

		$migrations = $this->findAndGetMigrations();
		$i = 1;
		foreach ($migrations as $migration) {
			$data[] = array(
				($i++).'.',
				$migration->getId(),
				$migration->getTitle(),
				tx_nwtmigration_viewHelper::getMigrationStatusIcon($migration->getStatus()),
				tx_nwtmigration_viewHelper::getMigrationActionLink($migration)
			);
		}
		$content = $this->viewHelper->arrayToTable($header, $data);
		
		return $content;
	}
	
	
	/**
	 * find and include all migrations from configured directory 
	 *
	 * @return unknown
	 */
	function findAndGetMigrations() {
		$path = t3lib_div::getFileAbsFileName($this->_extConfig['path_migrations']);

		$migrations = array();
		
		$d = dir($path);
		while (false !== ($entry = $d->read())) {

		   if (is_file($path . '/' . $entry)
		     && preg_match('/^class\.migration_([0-9]{10})\.php$/', $entry, $match) ) {
				$migrationID = intval($match[1]);
				
				$migrations[$migrationID] = $this->getMigration($migrationID);
		   }
		}
		
		$d->close();
		ksort($migrations);
		
		return $migrations;
	}
	
	/**
	 * apply or revert given migration
	 *
	 * @param int $mode (RUNMODE_UP, RUNMODE_DOWN)
	 * @param int $migrationID
	 * @return string HTML
	 */
	function actionRunMigration($mode, $migrationID) {

		$migration = $this->getMigration($migrationID);
		
		$status = $migration->getStatus();
		$success = false; 
				
		if ($mode == tx_nwtmigration_migration_basic::RUNMODE_UP 
		  && $status == tx_nwtmigration_migration_basic::STATUS_NOTAPPLIED) {
			$success = $migration->up();
		} 
		if ($mode == tx_nwtmigration_migration_basic::RUNMODE_DOWN 
		  && $status == tx_nwtmigration_migration_basic::STATUS_SUCCESSFUL) {
			$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'*',
				'tx_nwtmigration_migrationslog',
				' migration_id = ' . intval($migration->getId()),
				'',
				' crdate DESC ',
				' 1 '
			);
			
			// last run was Up and successful
			if ($rows[0]['was_successful'] == 1
			  && $rows[0]['run_mode'] == tx_nwtmigration_migration_basic::RUNMODE_UP) {
				$migration->register = unserialize($rows[0]['register']);
			}
		  	
		  	$success = $migration->down();
		}
		
		$register = $migration->register;

		$GLOBALS['TYPO3_DB']->exec_INSERTquery(
			'tx_nwtmigration_migrationslog',
			array(
				'pid' => 0,
				'tstamp' => mktime(),
				'crdate' => mktime(),
				'cruser_id' =>  $GLOBALS['BE_USER']->user['uid'],
				'migration_id' => $migrationID,
				'was_successful' => $success,
				'run_mode' => $mode,
				'note' => '',
				'register' => serialize($register),
			)
		);
		
		$content = '';
		$llkey = 'migration_run_';
		$llkey .= ($mode == tx_nwtmigration_migration_basic::RUNMODE_UP)? 'up' : '';
		$llkey .= ($mode == tx_nwtmigration_migration_basic::RUNMODE_DOWN)? 'down' : '';
		$llkey .= '_';
		$llkey .= ($success)? 'successful' : 'notsuccessful'; 
		$content .= $GLOBALS['LANG']->getLL($llkey); 	

		return $content;
	}
	
	
	/**
	 * include and make instance of given migration 
	 *
	 * @param int $migrationID
	 * @return tx_nwtmigration_migration_basic instance of migration
	 */
	function getMigration($migrationID) {
		$path = t3lib_div::getFileAbsFileName($this->_extConfig['path_migrations']);
		$classname = 'migration_'.$migrationID;		
		$file = 'class.' . $classname . '.php';
		
		if (!class_exists($classname)
			&& is_file($path . '/' . $file ) ) {
				require_once $path . '/' . $file;
		}
		
		/**
		 * @var $migration tx_nwtmigration_migration_basic 
		 */
		$migration = t3lib_div::makeInstance($classname);
		$migration->injectHelper(tx_nwtmigration_migrationHelper::getInstance());

		return $migration;
	}

}

?>