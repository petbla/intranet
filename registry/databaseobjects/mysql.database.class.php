<?php
/**
 * Správa / přístup k databázi: základní abstrakce
 * 
 * @author  Petr Blažek
 * @version 1.0
 * @date    5.7.2011 
 */
class mysqldatabase {
	
	/**
	 * Debug SQL query
	 * Logování SQL dotazů do logFile.txt
	 */
  private $writeToLog = true;
         	
  /**
	 * Umožňuje více spojení s databází 
	 * každé spojení se uloží ve formě prvku pole, aktivní spojení je uloženo v proměnné (viz dále)
	 */
	private $connections = array();
	
	/**
	 * Říka DB objektu, které spojení se má použít 
	 * pomocí setActiveConnection($id) je možné to změnit
	 */
	private $activeConnection = 0;
	
	/**
	 * Provedené dotazy jejichž výsledky se uložili do mezipaměti, primárně 
	 * pro potřeby správce šablon 
	 */
	private $queryCache = array();
	
        /** 
	 * Data uložená do mezipaměti pro pozdější použití, primárně pro potřeby 
   	 * správce šablon 
   	 */ 
	private $dataCache = array();
	
	/** 
	 * Počet provedených dotazů 
	 */ 
	private $queryCounter = 0;
	
	/** 
	 * Záznam o posledním dotazu 
	 */ 
	private $last;
	private $lastID;
	
	/** 
	 * Zásobník prováděnách dotazů
	 */ 
  private $queryTableName;
  private $queryFieldList;
  private $queryCondition;
  private $queryOrderBy;
  private $querySql;
  private $queryResult;
  private $isCacheQuery;
    
   /** 
    * Konstruktor databázového objektu 
    */ 
    public function __construct( $registry ) 
    { 
      $this->registry = $registry;
    }
    
   /** 
    * Vytvoří nové spojení s databází
    * @param String název hostitele
    * @param String uživatelské jméno
    * @param String heslo
    * @param String název databáze
    * @return int identifikátor nového spojení
    */
    public function newConnection( $host, $user, $password, $database )
    {
    	global $caption;
      $this->connections[] = new mysqli( $host, $user, $password, $database, null, null );
    	$connection_id = count( $this->connections )-1;
    	if( mysqli_connect_errno() )
    	{
    		trigger_error($caption['db_error_connect'] . ' '.$this->connections[$connection_id]->error, E_USER_ERROR);
      }else{
        $this->connections[$connection_id]->query( "set names utf-8" );
      } 
    	
    	return $connection_id;
    }
    
    /**
     * Ukončí aktivní spojení 
     * @return void
     */
    public function closeConnection()
    {
    	$this->connections[$this->activeConnection]->close();
    }
    
    /**
     * Nastaví aktuální spojení s databází pro následující operace 
     * @param int identifikátor nového spojení
     * @return void
     */
    public function setActiveConnection( int $new )
    {
    	$this->activeConnection = $new;
    }
    
    /**
     * Uloží výsledek dotazu do mezipaměti pro pozdější zpracování
     * @param String dotazový řetězec
     * @return index výsledku dotazu v mezipaměti
     */
    public function cacheQuery( $queryStr )
    {
    	global $caption;
   	
      if( !$result = $this->connections[$this->activeConnection]->query( $queryStr ) )
    	{
        trigger_error($caption['db_error_cachequery'] . ': '.$this->connections[$this->activeConnection]->error, E_USER_ERROR);
		    return -1;
  		}
  		else
  		{
		    if( $this->writeToLog )
        $this->queryCache[] = $result;
        $this->isCacheQuery = true;
  			return count($this->queryCache)-1;
  		}
    }
    
    /**
     * Získá počet řádků výsledku uloženého v mezipaměti 
     * @param int index výsledku dotazu v mezipaměti
     * @return int počet řádků 
     */
    public function numRowsFromCache( $cache_id )
    {
    	return $this->queryCache[$cache_id]->num_rows;	
    }
    
    /**
     * Získá výsledek dotazu uložení v mezipaměti 
     * @param int index výsledku dotazu 
     * @return array výsledek dotazu 
     */
    public function resultsFromCache( $cache_id )
    {
    	return $this->queryCache[$cache_id]->fetch_array(MYSQLI_ASSOC);
    }
    
    /**
     * Uloží data do mezipaměti pro pozdější použití 
     * @param array data
     * @return int index dat v mezipaměti 
     */
    public function cacheData( $data )
    {
      $this->dataCache[] = $data;
      $this->isCacheQuery = true;
    	return count( $this->dataCache )-1;
    }
    
    /**
     * Získá data z mezipaměti
     * @param int index dat v mezipaměti
     * @return array data
     */
    public function dataFromCache( $cache_id )
    {
    	return $this->dataCache[$cache_id];
    }
    
    /**
     * Odstraní záznamy z databáze 
     * @param String tabulka, ze které se záznamy odstraní 
     * @param String podmínka pro odstranění 
     * @param int počet řádků, které se mají odstranit 
     * @return void
     */
    public function deleteRecords( $table, $condition, $limit )
    {
      global $config;
      $table = $config['dbPrefix'].$table;

      $limit = ( $limit === '' ) ? '' : ' LIMIT ' . $limit;
    	$delete = "DELETE FROM {$table} WHERE {$condition} {$limit}";
    	$this->executeQuery( $delete );
    }
    
    /**
     * Aktualizuje záznamy v databázi 
     * @param String název tabulky
     * @param array pole změn sloupec => hodnota 
     * @param String podmínka
     * @return bool 
     */
    public function updateRecords( $table, $changes, $condition, $withPrefix = true)
    {
      global $config;
      if ($withPrefix)
        $table = $config['dbPrefix'].$table;

    	$update = "UPDATE " . $table . " SET ";
    	foreach( $changes as $field => $value )
    	{
        if($value == 'NULL')
          $update .= "`" . $field . "`= NULL ,";
        else
          $update .= "`" . $field . "`='{$value}',";
    	}
    	   	
    	// remove our trailing ,
    	$update = substr($update, 0, -1);
    	if( $condition != '' )
    	{
    		$update .= "WHERE " . $condition;
    	}
    	
    	$this->executeQuery( $update );
    	
    	return true;
    	
    }
    
    /**
     * Vloží záznam do databáze 
     * @param String název tabulky
     * @param array pole dat sloupec => hodnota 
     * @return bool 
     */
    public function insertRecords( $table, $data, $withPrefix = true )
    {
      global $config;
      if ($withPrefix)
        $table = $config['dbPrefix'].$table;

    	// setup some variables for fields and values
    	$fields = "";
  		$values = "";
  		
  		// vyplnění proměnných
  		foreach ($data as $f => $v)
  		{
  			$fields  .= "`$f`,";
  			$values .= ( is_numeric( $v ) && ( intval( $v ) == $v ) ) ? $v."," : "'$v',";
  		}
  		
  		// odstranění koncového znaku „,“ 
     	$fields = substr($fields, 0, -1);
     	// odstranění koncového znaku „,“ 
     	$values = substr($values, 0, -1);
      	
  		$insert = "INSERT INTO $table ({$fields}) VALUES({$values})";
  		$this->executeQuery( $insert );
      $this->lastID = $this->connections[ $this->activeConnection]->insert_id;		
  		return true;
    }

      /**
     * Nastavení polí pro SELECT
     * @param String tableName
     * @param String fieldList
     * @return void 
     */
    public function initQuery( $tableName, $fieldList = '*', $withPrefix = true )
    {
      global $config;
      global $config;

      $this->querySql = '';
      $this->queryResult = null;
      $this->queryTableName = '';
      $this->queryFieldList = '';
      $this->queryCondition = '';
      $this->queryOrderBy = '';
      $this->isCacheQuery = false;


      if( $fieldList === '')
      {
        $this->queryFieldList = '*';
      }
      if( $tableName === '' )
      {
        return;
      }
      if ($withPrefix)
        $tableName = $config['dbPrefix'].$tableName;

      $this->queryTableName = $tableName;
      $this->queryFieldList = $fieldList;
    }

      /**
     * Nastavení podmínky výběru
     * @param String condition
     * @return void 
     */
    public function setCondition( $condition )
    {
      if ($this->queryCondition != '')
      {
        $this->queryCondition .= " AND ";
      }
      $this->queryCondition .= $condition;
    }

     /**
     * Nastavení filtru pole 
     * @param String key
     * @param String value
     * @return void 
     */
    public function setFilter( $key, $value )
    {
      if( ($key === '')|($value === ''))
      {
        return;
      }
      if ($this->queryCondition != '')
      {
        $this->queryCondition .= " AND ";
      }
      switch (true) {
        case is_int($value):
          $this->queryCondition .= "`$key` = $value";
          break;
        case is_double($value):
          $this->queryCondition .= "`$key` = $value";
          break;
        default:
          $this->queryCondition .= "`$key` = '$value'";
          break;
      }     
    }    

     /**
     * Nastavení filtru rozsahu pole 
     * @param String key
     * @param String valueFrom
     * @param String valueTo
     * @return void 
     */
    public function setRange( $key, $valueFrom, $valueTo )
    {
      if( ($key === '')|($valueFrom === '')|($valueTo === ''))
      {
        return;
      }
      if ($this->queryCondition != '')
      {
        $this->queryCondition .= " AND ";
      }
      switch (true) {
        case (is_int($valueFrom) AND is_int($valueTo)):
          $this->queryCondition .= "$key BETWEEN $valueFrom AND $valueTo";
          break;
        case (is_double($valueFrom) AND is_double($valueTo)):
          $this->queryCondition .= "$key BETWEEN $valueFrom AND $valueTo";
          break;
        default:
          $this->queryCondition .= "$key BETWEEN '$valueFrom' AND '$valueTo'";
          break;
      }     
    }    

    
     /**
     * Nastavení řazení pro výběr
     * @param String orderBy
     * @return void 
     */
    public function setOrderBy( $orderBy )
    {
      if( $orderBy != '')
      {
        $this->queryOrderBy = $orderBy;
      }
    }

     /**
     * Nalezení sady záznamů
     * @param void
     * @return boolean
     */
    public function findSet( )
    {
      if ($this->isCacheQuery === true){
        return false;
      };
      $this->buildQuery();      
      if($this->querySql === '')
      {
        return false;
      }
      $queryStr = $this->querySql;
      $cache = $this->cacheQuery( $queryStr );
      $this->queryResult = null;
      while( $result = $this->resultsFromCache( $cache ) )
			{
				$this->queryResult[] = $result;
      }
      return is_array( $this->queryResult );
    }

     /**
     * Nalezení prvního záznamu
     * @param void
     * @return boolean
     */
    public function findFirst( )
    {
      if ($this->isCacheQuery === true){
        return false;
      };
      $this->buildQuery();      
      if($this->querySql === '')
      {
        return false;
      }
      $queryStr = $this->querySql;
      $this->executeQuery( $queryStr );
      if( $this->numRows() != 0 )
      {
        $this->queryResult = $this->getRows();
        return true;
      }
      return false;
    }

     /**
     * Nalezení posledního záznamu
     * @param void
     * @return boolean
     */
    public function findLast()
    {
      if ($this->isCacheQuery === true){
        return false;
      };
      if($this->queryOrderBy === '')
      {
        return false;
      }
      $this->queryOrderBy .= ' DESC';
      return $this->findFirst();
    }

     /**
     * Zjištění existence záznamu
     * @param void
     * @return boolean
     */
    public function isEmpty( $cache_id = null)
    {
      if ($this->isCacheQuery === true){
        if ($this->queryCache[$cache_id]->num_rows > 0)
        {
          return false;
        }
        return true;
      };
      $this->buildQuery();      
      if($this->querySql === '')
      {
        return true;
      }
      $queryStr = $this->querySql;
      $this->executeQuery( $queryStr );
      return ( $this->numRows() == 0 );
    }

    /**
     * Vrácení výsledku dotazu
     * @param void
     * @return array()
     */
    public function getResult()
    {
      return $this->queryResult;
    }

    /**
     * Provede dotaz 
     * @param String dotaz
     * @return void 
     */
    public function executeQuery( $queryStr )
    {
    	global $caption;
      
      $this->connections[$this->activeConnection]->query( "set names utf8" );	
      
      if( !$result = $this->connections[$this->activeConnection]->query( $queryStr ) )
    	{
        trigger_error($caption['db_error_executequery'].': '.$this->connections[$this->activeConnection]->error, E_USER_ERROR);
  		}
  		else
  		{
  			$this->last = $result;
  		}
    }
    
    public function lastInsertID()
    {
	    return $this->lastID;
    }
    
    /**
     * Získá řádky výsledku posledního provedeného dotazu
     * @return array 
     */
    public function getRows()
    {
    	return $this->last->fetch_array(MYSQLI_ASSOC);
    }
    
    public function numRows()
    {
	    return $this->last->num_rows;
    }
    
    /**
     * Získá počet řádků ovlivněných předchozím dotazem 
     * @return int počet ovlivněných řádků 
     */
    public function affectedRows()
    {
    	return $this->last->affected_rows;
    }
    
    /**
     * Vyčistí data
     * @param String data, která se mají vyčistit 
     * @return String vyčištěná data
     */
    public function sanitizeData( $data )
    {
    	return $this->connections[$this->activeConnection]->real_escape_string( $data );
    }

    /**
     * Poskládá dotaz
     * @return void 
     */
    private function buildQuery()
    {
      $table = $this->queryTableName;
      $fields = $this->queryFieldList;
      $condition = $this->queryCondition;
      $orderBy = $this->queryOrderBy;

      if ($table == "")
      {
        $this->querySql =  "";
        return;
      }
      if($fields == "")
      {
        $fields = '*';
      }

      $this->querySql = "SELECT {$fields} FROM {$table}";
      if ($condition != "")
      {
        $this->querySql .= " WHERE {$condition}";  
      } 
      if ($orderBy != "")
      {
        $this->querySql .= " ORDER BY {$orderBy}";  
      } 
      return;
    }   
    
    /**
     * Destruktur
     * ukončí všechna otevřená spojení s databázovým systémem 
     */
    public function __deconstruct()
    {
    	foreach( $this->connections as $connection )
    	{
    		$connection->close();
    	}
    }

    /**
     * Kontrola a aktualizace definice projektů (DMS nastavení)
     * @return void
     */
    public function CheckPortal()
    {
      global $config;

      $this->initQuery('source', '*', false);
      $this->setFilter('DbPrefix',$config['dbPrefix']);
      if($this->isEmpty())
      {
        $data = array();
        $data['Webroot'] = $config['webroot'];
        $data['Fileroot'] = $config['fileroot'];
        $data['DbPrefix'] = $config['dbPrefix'];
        $data['Name'] = $config['compName'];
        $data['Address'] = $config['compAddress'];
        $data['City'] = $config['compCity'];
        $data['Zip'] = $config['compZip'];
        $data['ICO'] = $config['compICO'];
        $this->insertRecords('source',$data, false);        
      }
      
    }

    /**
     * Nastavení výchozího DMS zdroje z nastavení
     * @param Integer $EntryNo = číslo položky portálu, kde 0=výchozí
     * @return void
     */
    public function SetPortal( $EntryNo = 0 )
    {
      global $config;

      $this->initQuery('source', '*', false);
      if ($EntryNo > 0)
      {
        $this->setFilter('EntryNo',$EntryNo);
      }
      else
      {
        $this->setFilter('Default',1);
      }
      if($this->findFirst())
      {
        $source = $this->getResult();
        $sql = "UPDATE `source` SET `Default` = '0'";
        $this->executeQuery( $sql );
      }
      else
      {
        $this->initQuery('source', '*', false);
        $this->setFilter('DbPrefix',$config['dbPrefix']);
        $this->findFirst();
        $source = $this->getResult();
      }  
      $EntryNo = $source['EntryNo'];
      $changes =  array();
      $changes['Default'] = 1;
			$condition = "EntryNo = $EntryNo";
			$this->updateRecords('source',$changes, $condition, false);
     
      $config['webroot'] = $source['Webroot'];
      $config['fileroot'] = $source['Fileroot'];
      $config['dbPrefix'] = $source['DbPrefix'];
      $config['compName'] = $source['Name'];
      $config['compAddress'] = $source['Address'];
      $config['compCity'] = $source['City'];
      $config['compZip'] = $source['Zip'];
      $config['compICO'] = $source['ICO'];
      $this->registry->getObject('template')->dataToTags( $config, 'cfg_' );
      
    }

    /**
     * Nastavení výchozího DMS zdroje z nastavení
     * @return Integer $Counter = počet záznamů DMS portálů
     */
    public function GetPortalCount()
    {
      global $config;

      $this->initQuery('source', '*', false);
      if($this->findFirst())
      {
        $Counter = $this->numRows();
      }
      return $Counter;
    }

}
?>
