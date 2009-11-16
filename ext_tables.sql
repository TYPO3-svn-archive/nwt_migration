#
# Table structure for table 'tx_nwtmigration_migrationslog'
#
CREATE TABLE tx_nwtmigration_migrationslog (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    migration_id tinytext NOT NULL,
    was_successful tinyint(3) DEFAULT '0' NOT NULL,
	run_mode tinyint(3) DEFAULT '0' NOT NULL,
    note text NOT NULL,
	register blob NOT NULL,
    
    PRIMARY KEY (uid),
    KEY parent (pid)
);
