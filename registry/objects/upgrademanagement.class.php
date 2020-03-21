<?php
/**
 * Upgrade management
 *
 * @author  Petr Blažek
 * @version 1.0
 * @date    06.01.2019
 */

class upgrademanagement {

    private $version;
    private $PK;
    private $newInit = false;

    public function __construct( $registry ) 
    {
        $this->registry = $registry;
    }
    
    public function checkUpgrade( )
    {
        global $config;
        $pref = $config['dbPrefix'];
        
        $this->openSetup();

        if($this->newInit)
        {
            // Create NEW Tables
            $this->PK = 0;
            $this->version = '1.1';
            $this->newInit = true;

            // setup
            $sql = "CREATE TABLE IF NOT EXISTS `".$pref."setup` (
                `PrimaryKey` int(11) NOT NULL AUTO_INCREMENT,
                `Version` varchar(10) COLLATE utf8_czech_ci NOT NULL DEFAULT '0.0',
                PRIMARY KEY (`PrimaryKey`)
              ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci";
            $this->registry->getObject('db')->executeQuery( $sql );

            $setup['version'] = $this->version;
            $this->registry->getObject('db')->insertRecords('setup',$setup);
            $this->registry->getObject('db')->initQuery('setup');
            $this->registry->getObject('db')->findFirst();
            $setup = $this->registry->getObject('db')->getResult();
            $this->PK = $setup['PrimaryKey'];

            // dmsentry
            $sql = "CREATE TABLE IF NOT EXISTS `".$pref."dmsentry` (
                `EntryNo` int(11) NOT NULL AUTO_INCREMENT,
                `ID` varchar(36) COLLATE utf8_czech_ci NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
                `Level` int(11) NOT NULL DEFAULT '0',
                `Parent` int(11) NOT NULL DEFAULT '0',
                `Type` int(11) NOT NULL DEFAULT '0',
                `LineNo` int(11) NOT NULL DEFAULT '0',
                `Title` varchar(250) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
                `Name` varchar(250) COLLATE utf8_czech_ci DEFAULT '',
                `Path` varchar(250) COLLATE utf8_czech_ci DEFAULT '',
                `FileExtension` varchar(10) COLLATE utf8_czech_ci DEFAULT '',
                `Url` varchar(150) COLLATE utf8_czech_ci NOT NULL,
                `ModifyDateTime` datetime DEFAULT NULL,
                `CreateDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `Archived` tinyint(1) NOT NULL DEFAULT '0',
                `NewEntry` tinyint(1) NOT NULL DEFAULT '1',
                `PermissionSet` int(11) NOT NULL DEFAULT '0',
                `LastChange` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`EntryNo`),
                KEY `ID` (`ID`),
                KEY `Level` (`Level`,`Parent`,`Type`,`LineNo`)
              ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci";
            $this->registry->getObject('db')->executeQuery( $sql );

            $sql = "ALTER TABLE `".$pref."dmsentry` ADD FULLTEXT KEY `Title` (`Title`)";
            $this->registry->getObject('db')->executeQuery( $sql );

            // permissionset
            $sql = "CREATE TABLE IF NOT EXISTS `".$pref."permissionset` (
                `Level` int(11) NOT NULL,
                `Name` varchar(30) COLLATE utf8_czech_ci NOT NULL,
                PRIMARY KEY (`Level`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci";
            $this->registry->getObject('db')->executeQuery( $sql );

            $sql = "INSERT INTO `".$pref."permissionset` (`Level`, `Name`) VALUES
            (0, 'veřejnost'),
            (1, 'zaměstnanec'),
            (2, 'člen výboru'),
            (3, 'zastupitel'),
            (4, 'radní'),
            (5, 'starosta'),
            (9, 'administrátor')";
            $this->registry->getObject('db')->executeQuery( $sql );

            // user
            $sql = "CREATE TABLE IF NOT EXISTS `".$pref."user` (
                `ID` varchar(36) COLLATE utf8_czech_ci NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
                `Name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
                `Password` varchar(50) COLLATE utf8_czech_ci NOT NULL,
                `PermissionSet` int(11) NOT NULL DEFAULT '0',
                PRIMARY KEY (`ID`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci";
            $this->registry->getObject('db')->executeQuery( $sql );

            // contact
            $sql = "CREATE TABLE IF NOT EXISTS `".$pref."contact` (
                `ID` varchar(36) COLLATE utf8_czech_ci NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
                `FullName` varchar(85) COLLATE utf8_czech_ci NOT NULL,
                `FirstName` varchar(30) COLLATE utf8_czech_ci DEFAULT NULL,
                `LastName` varchar(30) COLLATE utf8_czech_ci DEFAULT NULL,
                `Title` varchar(20) COLLATE utf8_czech_ci DEFAULT NULL,
                `Function` varchar(50) COLLATE utf8_czech_ci DEFAULT NULL,
                `Company` varchar(50) COLLATE utf8_czech_ci DEFAULT NULL,
                `Note` varchar(250) COLLATE utf8_czech_ci DEFAULT NULL,
                `Phone` varchar(80) COLLATE utf8_czech_ci DEFAULT NULL,
                `Email` varchar(80) COLLATE utf8_czech_ci DEFAULT NULL,
                `Web` varchar(80) COLLATE utf8_czech_ci DEFAULT NULL,
                `Address` text COLLATE utf8_czech_ci,
                `Close` int(11) NOT NULL DEFAULT '0',
                `ContactGroups` varchar(250) COLLATE utf8_czech_ci DEFAULT NULL,
                PRIMARY KEY (`ID`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci";
            $this->registry->getObject('db')->executeQuery( $sql );

            $sql = "ALTER TABLE `".$pref."contact` ADD FULLTEXT KEY `Contact` (`FullName`,`Function`,`Company`,`Address`,`Note`,`Phone`,`Email`,`ContactGroups`)";
            $this->registry->getObject('db')->executeQuery( $sql );

            // contactgroup
            $sql = "CREATE TABLE IF NOT EXISTS `".$pref."contactgroup` (
                `Code` varchar(20) COLLATE utf8_czech_ci NOT NULL,
                `Name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
                PRIMARY KEY (`Code`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci";
            $this->registry->getObject('db')->executeQuery( $sql );
        }
        
        // Check upgrade

        if ($this->version === '1.0') 
        {
            // upgrade to 1.1
            $this->upgrade_001();
        }
        if ($this->version === '1.1') 
        {
            // upgrade to 1.2
            $this->upgrade_002();
        }
        if ($this->version === '1.2') 
        {
            // upgrade to 1.3
            $this->upgrade_003();
        }
        if ($this->version === '1.3') 
        {
            // upgrade to 1.4
            $this->upgrade_004();
        }
        if ($this->version === '1.4') 
        {
            // upgrade to 1.5
            $this->upgrade_005();
        }
        if ($this->version === '1.5') 
        {
            // upgrade to 1.6
            $this->upgrade_006();
        }
        if ($this->version === '1.6') 
        {
            // upgrade to 1.7
            $this->upgrade_007();
        }
        if ($this->version === '1.7') 
        {
            // upgrade to 1.8
            $this->upgrade_008();
        }
        if ($this->version === '1.8') 
        {
            // upgrade to 1.9
            $this->upgrade_009();
        }
        if ($this->version === '1.9') 
        {
            // upgrade to 1.10
            $this->upgrade_010();
        }
        if ($this->version === '2.0') 
        {
            // upgrade to 2.01
            $this->upgrade_011('2.01');
        }
        if ($this->version === '2.01') 
        {
            // upgrade to 2.02
            $this->upgrade_012('2.02');
        }
        if ($this->version === '2.02') 
        {
            // upgrade to 2.03
            $this->upgrade_013('2.03');
        }
        if ($this->version === '2.03') 
        {
            // upgrade to 2.04
            $this->upgrade_014('2.04');
        }
        if ($this->version === '2.04') 
        {
            // upgrade to 2.05
            $this->upgrade_015('2.05');
        }
        if ($this->version === '2.05') 
        {
            // upgrade to 2.06
            $this->upgrade_016('2.06');
        }
        if ($this->version === '2.06') 
        {
            // upgrade to 2.07
            $this->upgrade_017('2.07');
        }
        if ($this->version === '2.07') 
        {
            // upgrade to 2.08
            $this->upgrade_018('2.08');
        }
        if ($this->version === '2.08') 
        {
            // upgrade to 2.09
            $this->upgrade_019('2.09');
        }
        if ($this->version === '2.09') 
        {
            // upgrade to 2.10
            $this->setNewVersion('2.10');
        }
        if ($this->version === '2.10') 
        {
            // upgrade to 2.11
            $this->upgrade_020('2.11');
        }
        if ($this->version === '2.11') 
        {
            // upgrade to 2.12
            $this->upgrade_021('2.12');
        }
        if ($this->version === '2.12') 
        {
            // upgrade to 2.13
            $this->upgrade_022('2.13');
        }
    }

    private function upgrade_022($upVer)
    {
		global $config;
        $pref = $config['dbPrefix'];

        // upgrade table 'agenda'
        $sql = "ALTER TABLE ".$pref."agenda".
                " ADD `NoSeries` varchar(20) DEFAULT ''";
        $this->registry->getObject('db')->executeQuery( $sql );

        // Update
        $this->registry->getObject('db')->initQuery('agenda');
		if ($this->registry->getObject('db')->findSet())
		{
		    $agenda = $this->registry->getObject('db')->getResult();
			foreach ($agenda as $rec) {
                $ID = $rec['ID'];
                $DocumentNo = $rec['DocumentNo'];        // SML-2019-0005
                $delka = strlen($DocumentNo);            // 13
                $changes['NoSeries'] = substr($DocumentNo,0,$delka - 3).'000';
                $condition = "ID = '$ID'";
                $this->registry->getObject('db')->updateRecords('agenda',$changes, $condition);
            }
        }
        $this->setNewVersion($upVer);
    }
    
    private function upgrade_021($upVer)
    {
		global $config;
        $pref = $config['dbPrefix'];
        
        // new table agenda
        $sql = "CREATE TABLE IF NOT EXISTS `".$pref."agenda` (
            `ID` varchar(36) DEFAULT '00000000-0000-0000-0000-000000000000',
            `TypeID` int(11) NOT NULL DEFAULT 0,
            `DocumentNo` varchar(20) DEFAULT '',
            `Description` varchar(250) COLLATE utf8_czech_ci DEFAULT '',
            `CreateDate` datetime NULL,
            `ExecuteDate` datetime NULL,
            `EntryID` varchar(36) DEFAULT '00000000-0000-0000-0000-000000000000',
            PRIMARY KEY (`TypeID`, `DocumentNo`),
            KEY `ID` (`ID`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci";
        $this->registry->getObject('db')->executeQuery( $sql );

        // new table agendatype
        $sql = "CREATE TABLE IF NOT EXISTS `".$pref."agendatype` (
            `TypeID` INT(11) NOT NULL AUTO_INCREMENT,
            `Name` varchar(250) COLLATE utf8_czech_ci DEFAULT '',
            `NoSeries` varchar(20) DEFAULT '',
            `LastNo` varchar(20) DEFAULT '',
            PRIMARY KEY (`TypeID`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci";
        $this->registry->getObject('db')->executeQuery( $sql );

        $this->setNewVersion($upVer);
    }

    private function upgrade_020($upVer)
    {
		global $config;
        $pref = $config['dbPrefix'];

        $sql = "SELECT * FROM information_schema.columns WHERE table_schema = 'intranet' AND TABLE_NAME = 'Source' AND COLUMN_NAME = 'Version'";
        $cache = $this->registry->getObject('db')->cacheQuery( $sql );
        if ($this->registry->getObject('db')->IsEmpty( $cache ))
        {
            // upgrade table 'Source'
            $sql = "ALTER TABLE source".
                    " ADD `Version` varchar(20) DEFAULT ''";
            $this->registry->getObject('db')->executeQuery( $sql );
        }

        // upgrade table 'contact'
        $sql = "ALTER TABLE ".$pref."contact".
                " ADD `BirthDate` date NULL DEFAULT NULL";
        $this->registry->getObject('db')->executeQuery( $sql );        
        $this->setNewVersion($upVer);
    }

    private function upgrade_019($upVer)
    {
        $sql = "SELECT * FROM information_schema.columns WHERE table_schema = 'intranet' AND TABLE_NAME = 'Source' AND COLUMN_NAME = 'Default'";
        $cache = $this->registry->getObject('db')->cacheQuery( $sql );
        if ($this->registry->getObject('db')->IsEmpty( $cache ))
        {
            // upgrade table 'Source'
            $sql = "ALTER TABLE source".
                    " ADD `Default` tinyint(1) NULL DEFAULT 0";
            $this->registry->getObject('db')->executeQuery( $sql );
        }
        $this->setNewVersion($upVer);
    }

    private function upgrade_018($upVer)
    {
        $sql = "SELECT * FROM information_schema.columns WHERE table_schema = 'intranet' AND TABLE_NAME = 'Source' AND COLUMN_NAME = 'City'";
        $cache = $this->registry->getObject('db')->cacheQuery( $sql );
        if ($this->registry->getObject('db')->IsEmpty( $cache ))
        {
            // upgrade table 'Source'
            $sql = "ALTER TABLE source".
                    " ADD `City` varchar(100) COLLATE utf8_czech_ci DEFAULT ''".
                    ", ADD `Zip` varchar(10) COLLATE utf8_czech_ci DEFAULT ''".
                    ", ADD `ICO` varchar(30) COLLATE utf8_czech_ci DEFAULT ''";
            $this->registry->getObject('db')->executeQuery( $sql );
        }
        $this->setNewVersion($upVer);
    }

    private function upgrade_017($upVer)
    {
        $this->setNewVersion($upVer);
    }

    private function upgrade_016($upVer)
    {
        $sql = "SELECT * FROM information_schema.columns WHERE table_schema = 'intranet' AND TABLE_NAME = 'Source' AND COLUMN_NAME = 'Address'";
        $cache = $this->registry->getObject('db')->cacheQuery( $sql );
        if ($this->registry->getObject('db')->IsEmpty( $cache ))
        {
            // upgrade table 'Source'
            $sql = "ALTER TABLE source".
                    " ADD `Address` varchar(100) COLLATE utf8_czech_ci DEFAULT ''";
            $this->registry->getObject('db')->executeQuery( $sql );
        }
        $this->setNewVersion($upVer);
    }

    private function upgrade_015($upVer)
    {
        $sql = "SELECT * FROM information_schema.tables WHERE table_schema = 'intranet' AND TABLE_NAME = 'Source'";
        $cache = $this->registry->getObject('db')->cacheQuery( $sql );
        if ($this->registry->getObject('db')->IsEmpty( $cache ))
        {
            // new table 'source'
            $sql = "CREATE TABLE IF NOT EXISTS `source` (
                `EntryNo` int(11) NOT NULL AUTO_INCREMENT,
                `Webroot` varchar(200) COLLATE utf8_czech_ci DEFAULT '',
                `Fileroot` varchar(200) COLLATE utf8_czech_ci DEFAULT '',
                `DbPrefix` varchar(20) COLLATE utf8_czech_ci DEFAULT '',
                `Name` varchar(100) COLLATE utf8_czech_ci DEFAULT '',
                PRIMARY KEY (`EntryNo`)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci";
            $this->registry->getObject('db')->executeQuery( $sql );
        }
        $this->setNewVersion($upVer);
    }

    private function upgrade_014($upVer)
    {
		global $config;
        $pref = $config['dbPrefix'];

        // upgrade table 'dmsentry'
        $sql = "ALTER TABLE ".$pref."dmsentry".
                " CHANGE `Url` `Url` VARCHAR(250) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL DEFAULT ''";        
        $this->registry->getObject('db')->executeQuery( $sql );        
        $this->setNewVersion($upVer);
    }

    private function upgrade_013($upVer)
    {
		global $config;
        $pref = $config['dbPrefix'];

        // upgrade table 'dmsentry'
        $sql = "ALTER TABLE ".$pref."dmsentry".
                " CHANGE `Url` `Url` VARCHAR(250) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL";
        $this->registry->getObject('db')->executeQuery( $sql );
        $this->setNewVersion($upVer);
    }

    private function upgrade_012($upVer)
    {
		global $config;
        $pref = $config['dbPrefix'];

        // upgrade table 'dmsentry'
        $sql = "ALTER TABLE ".$pref."dmsentry".
                " ADD `Private` tinyint(1) NULL DEFAULT 0";
        $this->registry->getObject('db')->executeQuery( $sql );

        $this->setNewVersion($upVer);
    }

    private function upgrade_011($upVer)
    {
        $this->setNewVersion($upVer);
    }

    private function upgrade_010()
    {
		global $config;
        $pref = $config['dbPrefix'];

        // upgrade table 'dmsentry'
        $sql = "ALTER TABLE ".$pref."dmsentry".
                " ADD `RemindState` varchar(30) NULL DEFAULT ''";
        $this->registry->getObject('db')->executeQuery( $sql );

        $this->setNewVersion('2.0');
    }

    private function upgrade_009()
    {
		global $config;
        $pref = $config['dbPrefix'];

        // upgrade table 'dmsentry'
        $sql = "UPDATE ".$pref."dmsentry".
                "`mis_dmsentry` SET `RemindClose` = '0' WHERE `Remind` = '1'";
        $this->registry->getObject('db')->executeQuery( $sql );

        $this->setNewVersion('1.9');
    }

    private function upgrade_008()
    {
		global $config;
        $pref = $config['dbPrefix'];

        // upgrade table 'dmsentry'
        $sql = "ALTER TABLE ".$pref."dmsentry".
                " ADD `RemindResponsiblePerson` varchar(50) NULL DEFAULT ''".
                ", ADD `RemindUserID` varchar(36) NULL DEFAULT '00000000-0000-0000-0000-000000000000'".
                ", ADD `RemindContactID` varchar(36) NULL DEFAULT '00000000-0000-0000-0000-000000000000'";
        $this->registry->getObject('db')->executeQuery( $sql );

        $this->setNewVersion('1.8');
    }

    private function upgrade_007()
    {
		global $config;
        $pref = $config['dbPrefix'];

        // upgrade table 'dmsentry'
        $sql = "ALTER TABLE ".$pref."dmsentry ADD `Remind` tinyint(1) NULL DEFAULT 0".
               ", ADD `RemindClose` tinyint(1) NULL DEFAULT 0".
               ", ADD `RemindFromDate` DATE NULL".
               ", ADD `RemindLastDate` DATE NULL".
               ", ADD `RemindUserGroup` int(11) NOT NULL DEFAULT 0".
               ", ADD `Content` TEXT NULL";
        $this->registry->getObject('db')->executeQuery( $sql );

        $this->setNewVersion('1.7');
    }

    private function upgrade_006()
    {
		global $config;
        $pref = $config['dbPrefix'];

        $sql = "SELECT ID,FileExtension FROM ".$pref."dmsentry WHERE Archived = 0 AND Type = 30";
        $entries = array();
        $this->registry->getObject('db')->executeQuery( $sql );
        while( $entry = $this->registry->getObject('db')->getRows() )
        {
            switch (strtolower($entry['FileExtension'])) {
                case 'bmp':
                case 'jpg':
                case 'png':
                    $entry['Multimedia'] = 'image';
                    $entries[] = $entry;
                    break;
                case 'mp3':
                case 'wav':
                case 'mid':
                    $entry['Multimedia'] = 'audio';
                    $entries[] = $entry;
                    break;
                case 'mp4':
                    $entry['Multimedia'] = 'video';
                    $entries[] = $entry;
                    break;
            }
        }
        foreach ($entries as $entry ) {
            $changes['Multimedia'] = $entry['Multimedia'];
            $condition = "ID = '".$entry['ID']."'";
            $this->registry->getObject('db')->updateRecords( 'dmsentry', $changes, $condition); 
        }
        $this->setNewVersion('1.6');
    }

    private function upgrade_005()
    {
		global $config;
        $pref = $config['dbPrefix'];
        
        // upgrade table 'user'
        $sql = "ALTER TABLE ".$pref."user ADD `Close` int(1) NULL DEFAULT 0";
        $this->registry->getObject('db')->executeQuery( $sql );

        $this->setNewVersion('1.5');
    }

    private function upgrade_004()
    {
		global $config;
        $pref = $config['dbPrefix'];
        
        // upgrade table 'log'
        $sql = "ALTER TABLE ".$pref."log ADD `IP` VARCHAR(30) NULL DEFAULT '' AFTER `UserName`";
        $this->registry->getObject('db')->executeQuery( $sql );

        $this->setNewVersion('1.4');
    }

    private function upgrade_003()
    {
		global $config;
        $pref = $config['dbPrefix'];

        // new table 'log'
        $sql = "CREATE TABLE IF NOT EXISTS `".$pref."log` (
            `EntryNo` int(11) NOT NULL AUTO_INCREMENT,
            `Table` varchar(20) COLLATE utf8_czech_ci DEFAULT '',
            `ID` varchar(36) COLLATE utf8_czech_ci DEFAULT '00000000-0000-0000-0000-000000000000',
            `UserID` varchar(36) COLLATE utf8_czech_ci DEFAULT '00000000-0000-0000-0000-000000000000',
            `UserName` varchar(50) COLLATE utf8_czech_ci DEFAULT '',
            `LogDateDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `Description` varchar(250) COLLATE utf8_czech_ci DEFAULT '',
            PRIMARY KEY (`EntryNo`),
            KEY `ID` (`ID`,`LogDateDate`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci";
        $this->registry->getObject('db')->executeQuery( $sql );

        // upgrade table 'user'
        $sql = "ALTER TABLE ".$pref."user ADD `FullName` VARCHAR(200) NULL DEFAULT '' AFTER `Name`";
        $this->registry->getObject('db')->executeQuery( $sql );

        $this->setNewVersion('1.3');
    }

    private function upgrade_002()
    {
		global $config;
        $pref = $config['dbPrefix'];

        $sql = "ALTER TABLE ".$pref."dmsentry ADD `Multimedia` VARCHAR(30) NULL DEFAULT '' AFTER `Type`";
        $this->registry->getObject('db')->executeQuery( $sql );

        $sql = "SELECT ID,FileExtension FROM ".$pref."dmsentry WHERE Archived = 0 AND Type = 30";
        $entries = array();
        $this->registry->getObject('db')->executeQuery( $sql );
        while( $entry = $this->registry->getObject('db')->getRows() )
        {
            switch (strtolower($entry['FileExtension'])) {
                case 'bmp':
                case 'jpg':
                case 'png':
                    $entry['Multimedia'] = 'image';
                    $entries[] = $entry;
                    break;
                case 'mp3':
                case 'wav':
                case 'mid':
                    $entry['Multimedia'] = 'audio';
                    $entries[] = $entry;
                    break;
                case 'mp4':
                    $entry['Multimedia'] = 'video';
                    $entries[] = $entry;
                    break;
            }
        }
        foreach ($entries as $entry ) {
            $changes['Multimedia'] = $entry['Multimedia'];
            $condition = "ID = '".$entry['ID']."'";
            $this->registry->getObject('db')->updateRecords( 'dmsentry', $changes, $condition); 
        }
        $this->setNewVersion('1.2');
    }

    private function upgrade_001()
    {
		global $config;
        $pref = $config['dbPrefix'];

        /*
         *    Odstranění tabulky contactgroups a migrace dat do contact.Groups
         */
        $sql = "SELECT c.ID, c.ContactGroups, ".
                "(SELECT GROUP_CONCAT( cg.GroupCode SEPARATOR ',' ) FROM ".$pref."contactgroups cg WHERE cg.ContactID = c.ID) AS Groups ".
                "FROM ".$pref."Contact c ";
        $contacts = array();
        $this->registry->getObject('db')->executeQuery( $sql );
        while( $contact = $this->registry->getObject('db')->getRows() )
        {
            if($contact['Groups'])
            {
                $contact['ContactGroups'] = $contact['Groups'];
                $contacts[] = $contact;
            }
        }
        foreach ($contacts as $contact ) {
            $changes['ContactGroups'] = $contact['ContactGroups'];
            $condition = "ID = '".$contact['ID']."'";
            $ID = $contact['ID'];
            $this->registry->getObject('log')->addMessage("Zobrazení a aktualizace kontaktu",'contact',$ID);
            $this->registry->getObject('db')->updateRecords( 'contact', $changes, $condition); 
        }
        $sql = "DROP TABLE contactgroups";
        $this->registry->getObject('db')->executeQuery( $sql );
        
        $this->setNewVersion('1.1');
    }

    private function openSetup()
    {
        global $config;
        $prefix = $config['dbPrefix'];

        $sql = "SHOW TABLES LIKE '".$prefix."setup'";
        $cache = $this->registry->getObject('db')->cacheQuery( $sql );
        if($this->registry->getObject('db')->numRowsFromCache( $cache ) == 1)
        {
            $this->registry->getObject('db')->initQuery('setup');
            if($this->registry->getObject('db')->findFirst())
            {
                $setup = $this->registry->getObject('db')->getResult();
            }
            else
            {
                $setup['version'] = '1.0';
                $this->registry->getObject('db')->insertRecords('setup',$setup);
                $this->registry->getObject('db')->initQuery('setup');
                $this->registry->getObject('db')->findFirst();
                $setup = $this->registry->getObject('db')->getResult();
            }
            $this->PK = $setup['PrimaryKey'];
            $this->version = $config['sourceVersion'];
            $this->newInit = false;
        }
        else
        {
            $this->version = '1.0';
            $this->newInit = true;
        }
    }

    private function setNewVersion( $ver )
    {
        global $config;

        $EntryNo = $config['sourceEntryNo'];
        $changes =  array();
        $changes['Version'] = $ver;
        $condition = "EntryNo = $EntryNo";
        $this->registry->getObject('db')->updateRecords('source',$changes, $condition, false);
        $this->version = $ver;
    }

    function getVersion()
    {
        return $this->version;
    }
}

