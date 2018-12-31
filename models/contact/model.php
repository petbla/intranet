<?php
/**
 * Class Contact
 * 
 * @author  Petr Blažek
 * @version 1.0
 * @date    29.11.2018
 */

class Contact{
	private $registry;
	private $ID;
	private $FullName;
	private $FirstName;
	private $LastName;
	private $Title;
	private $Function;
	private $Company;
	private $Email;
	private $Phone;
	private $Web;
	private $Note;
	private $Address;
	private $Close;
	private $active = false;
	private $groups = array();
		   
	public function __construct( Registry $registry, $id )
	{
		global $config;
		$this->registry = $registry;
		if( $id != '' )
		{
			$id = $this->registry->getObject('db')->sanitizeData( $id );
			$sql = "SELECT c.ID, c.FullName, c.FirstName, c.LastName, c.Title, c.Function, c.Company, ".
							"c.Email, c.Phone, c.Web, c.Note, c.Address, c.Close, ".
							"(SELECT GROUP_CONCAT( cg.GroupCode SEPARATOR ',' ) FROM contactgroups cg ".
							" WHERE cg.ContactID = c.ID) AS Groups ".
                		"FROM Contact c ".
                		"WHERE  ID='$id'";

      		$this->registry->getObject('db')->executeQuery( $sql );
			if( $this->registry->getObject('db')->numRows() == 1 )
			{
				$data = $this->registry->getObject('db')->getRows();
				$this->ID = $data['ID'];
				$this->FullName = $data['FullName'];
				$this->FirstName = $data['FirstName'];
				$this->LastName = $data['LastName'];
				$this->Title = $data['Title'];
				$this->Function = $data['Function'];
				$this->Company = $data['Company'];
				$this->Email = $data['Email'];
				$this->Phone = $data['Phone'];
				$this->Web = $data['Web'];
				$this->Note = $data['Note'];
				$this->Address = $data['Address'];
				$this->Close = $data['Close'];
				$this->active = ($data['Close'] !== 0);
				$this->Groups = $data['Groups'];
			}
		}
		else
		{
			// zde můžeme chtít provést něco jiného...
		}
	}

	public function isActive()
	{
		return $this->active;
	}
	
	public function getData()
	{
		$data = array();
		foreach( $this as $field => $fdata )
		{
			if( !is_object( $fdata ) )
			{
				$data[ $field ] = $fdata;
			}
		}
		return $data;
	}
}
?>