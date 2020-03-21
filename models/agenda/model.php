<?php
/**
 * Class Agenda
 * 
 * @author  Petr Blažek
 * @version 1.0
 * @date    04.07.2019
 */

class Agenda{
	private $registry;
    private $ID;
    private $TypeID;
    private $DocumentNo;
    private $Description;
    private $CreateDate;
    private $ExecuteDate;
    private $EntryID;
    private $TypeName;
    private $NoSeries;
    private $LastNo;
 
	public function __construct( Registry $registry, $id )
	{
		global $config;
        $pref = $config['dbPrefix'];

		$this->registry = $registry;
		if( $id != '' )
		{
			$sql = "SELECT a.ID, a.TypeID, a.DocumentNo, a.Description, a.CreateDate, a.ExecuteDate, a.EntryID, ".
							"t.Name as TypeName, t.NoSeries, t.LastNo ".
                		"FROM ".$pref."agenda c, ".$pref."agendatype t ".
                        "WHERE  ID='$id' ".
                            "AND a.TypeID = t.TypeID";

      		$this->registry->getObject('db')->executeQuery( $sql );
			if( $this->registry->getObject('db')->numRows() == 1 )
			{
				$data = $this->registry->getObject('db')->getRows();
				$this->ID = $data['ID'];
				$this->TypeID = $data['TypeID'];
				$this->DocumentNo = $data['DocumentNo'];
				$this->Description = $data['Description'];
				$this->CreateDate = $data['CreateDate'];
				$this->ExecuteDate = $data['ExecuteDate'];
				$this->EntryID = $data['EntryID'];
				$this->TypeName = $data['TypeName'];
				$this->NoSeries = $data['NoSeries'];
				$this->LastNo = $data['LastNo'];
			}
		}
		else
		{
			// New empty contact card
			$this->initEmpty();
		}
	}

    /**
     * Inicializace SQL záznamu z modelu
     * @return array() $data 
     */
    private function initSQLRecord()
    {
        $data = array();
        $data['ID'] = $this->ID;
        $data['TypeID'] = $this->TypeID;
        $data['DocumentNo'] = $this->DocumentNo;
        $data['Description'] = $this->Description;
        $data['CreateDate'] = $this->CreateDate;
        $data['ExecuteDate'] = $this->ExecuteDate;
        $data['NoSeries'] = $this->NoSeries;
        $data['EntryID'] = $this->EntryID;
        return $data;
    }

    /**
     * Inicializace prázdného záznamu
     * @return void
     */
    private function initEmpty()
    {
        $this->ID = '';
        $this->TypeID = 0;
        $this->DocumentNo = '';
        $this->Description = '';
        $this->CreateDate = null;
        $this->ExecuteDate = null;
        $this->EntryID = '';
        $this->TypeName = '';
        $this->NoSeries = '';
        $this->LastNo = '';
    }

    /**
     * Inicializace a založení nového záznamu dle číselné řady
     * @param $TypeID - kód typu agendy pro určení masky číselné řady
     * @return boolean $success - výsledek založení nového záznamu
     */
    function initNew( $TypeID )
    {
		global $config;
        $pref = $config['dbPrefix'];

        $this->initEmpty();
        $this->ID = $this->registry->getObject('fce')->GUID();
        $this->TypeID = $TypeID;
        $this->CreateDate = date("Y-m-d H:i:s");

        // Najít agendatype
        $this->registry->getObject('db')->initQuery('agendatype');
        $this->registry->getObject('db')->setFilter('TypeID',$TypeID);
        if ($this->registry->getObject('db')->findFirst())
        {
            $agendatype = $this->registry->getObject('db')->getResult();				
        }else{
            $this->initEmpty();
            return false;
        }
        $this->TypeName = $agendatype['Name'];
        $this->NoSeries = $agendatype['NoSeries'];
        $this->LastNo = $agendatype['LastNo'];       

        // Get new Document No
        $this->DocumentNo = $this->getNextDocumentNo($agendatype['NoSeries']);

        // Save record to database
        $data = $this->initSQLRecord();
        $this->registry->getObject('db')->insertRecords('agenda',$data);

        // Update LastNo in agendatype
        $changes = array();
        $changes['LastNo'] = $this->DocumentNo;
        $condition = "TypeID = '$TypeID'";
        $this->registry->getObject('db')->updateRecords('agendatype',$changes, $condition);

        return true;
    }


    /**
     * Generování dalšího čísla dle číselné řady
     * @return string $DocumentNo
     */
    private function getNextDocumentNo( $NoSeries )
    {
        $this->registry->getObject('db')->initQuery('agenda');
        $this->registry->getObject('db')->setFilter('NoSeries',$NoSeries);
        $this->registry->getObject('db')->setOrderBy('DocumentNo');
        if ($this->registry->getObject('db')->findLast())
        {
            $agenda = $this->registry->getObject('db')->getResult();
            $DocumentNo = $agenda['DocumentNo'];
        }else{
            if ($this->NoSeries == '')
            {
                $DocumentNo  = '0';
            }else{
                $DocumentNo = $this->NoSeries;
            }            
        }
        ++$DocumentNo;
        return $DocumentNo;
    }
}
