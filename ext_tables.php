<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE == 'BE')	{		
	t3lib_extMgm::addModule('tools','txnwtmigrationM1','',t3lib_extMgm::extPath($_EXTKEY).'mod1/');
}

$TCA["tx_nwtmigration_migrationslog"] = array (
    "ctrl" => array (
        'title'     => 'LLL:EXT:nwt_migration/locallang_db.xml:tx_nwtmigration_migrationslog',        
        'label'     => 'uid',    
        'tstamp'    => 'tstamp',
        'crdate'    => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => "ORDER BY crdate",    
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
        'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_nwtmigration_migrationslog.gif',
    ),
    "feInterface" => array (
        "fe_admin_fieldList" => "migration_id, was_successful, note",
    )
);

?>