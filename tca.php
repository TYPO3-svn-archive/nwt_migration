<?php
if (!defined ('TYPO3_MODE'))     die ('Access denied.');

$TCA["tx_nwtmigration_migrationslog"] = array (
    "ctrl" => $TCA["tx_nwtmigration_migrationslog"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "migration_id,was_successful,note"
    ),
    "feInterface" => $TCA["tx_nwtmigration_migrationslog"]["feInterface"],
    "columns" => array (
        "migration_id" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:nwt_migration/locallang_db.xml:tx_nwtmigration_migrationslog.migration_id",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "was_successful" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:nwt_migration/locallang_db.xml:tx_nwtmigration_migrationslog.was_successful",        
            "config" => Array (
                "type" => "check",
            )
        ),
        "note" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:nwt_migration/locallang_db.xml:tx_nwtmigration_migrationslog.note",        
            "config" => Array (
                "type" => "text",
                "cols" => "30",    
                "rows" => "5",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "migration_id;;;;1-1-1, was_successful, note")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);
?>