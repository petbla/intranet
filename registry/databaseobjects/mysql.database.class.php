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
    * Konstruktor databázového objektu 
    */ 
    public function __construct() { }
    
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
      $this->connections[] = new mysqli( $host, $user, $password, $database );
    	$connection_id = count( $this->connections )-1;
    	if( mysqli_connect_errno() )
    	{
    		trigger_error($caption['db_error_connect'] . ' '.$this->connections[$connection_id]->error, E_USER_ERROR);
      }else{
        $this->connections[$connection_id]->query( "set names cp1250" );
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
    	global $deb;
   	
      if( !$result = $this->connections[$this->activeConnection]->query( $queryStr ) )
    	{
		    $deb->Error($queryStr);
        trigger_error($caption['db_error_cachequery'] . ': '.$this->connections[$this->activeConnection]->error, E_USER_ERROR);
		    return -1;
  		}
  		else
  		{
		    if( $this->writeToLog )
          $deb->Trace($queryStr);
  			$this->queryCache[] = $result;
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
    	$limit = ( $limit == '' ) ? '' : ' LIMIT ' . $limit;
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
    public function updateRecords( $table, $changes, $condition )
    {
    	$update = "UPDATE " . $table . " SET ";
    	foreach( $changes as $field => $value )
    	{
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
    public function insertRecords( $table, $data )
    {
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
     * Provede dotaz 
     * @param String dotaz
     * @return void 
     */
    public function executeQuery( $queryStr )
    {
    	global $caption;
    	global $deb;
      
      $this->connections[$this->activeConnection]->query( "set names utf8" );	
      
      if( !$result = $this->connections[$this->activeConnection]->query( $queryStr ) )
    	{
        $deb->Error($queryStr);
        trigger_error($caption['db_error_executequery'].': '.$this->connections[$this->activeConnection]->error, E_USER_ERROR);
  		}
  		else
  		{
		    if( $this->writeToLog )
          $deb->Trace($queryStr);
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
}
?>
