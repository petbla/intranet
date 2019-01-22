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

        if ($this->version == '1.0') 
        {
            // upgrade to 1.1
            $this->upgrade_001();
        }
        if ($this->version == '1.1') 
        {
            // upgrade to 1.2
            $this->upgrade_002();
        }
        if ($this->version == '1.2') 
        {
            // upgrade to 1.3
            $this->upgrade_003();
        }
        if ($this->version == '1.3') 
        {
            // upgrade to 1.4
            $this->upgrade_004();
        }
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
            $this->version = $setup['Version'];
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
        $changes['Version'] = $ver;
        $condition = 'PrimaryKey = ' . $this->PK;
        $this->registry->getObject('log')->addMessage("Aktualizace nastavení",'setup',$ID);
        $this->registry->getObject('db')->updateRecords( 'setup', $changes, $condition); 
        $this->Version = $ver;
    }
}

