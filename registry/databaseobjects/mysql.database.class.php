<?php

/**
 * MySQL Management
 * 
 * @author  Petr Blažek
 * @version 1.0
 * @date    7.4.2023
 */
class mysqldatabase
{

  /**
   * Debug SQL query
   * Logování SQL dotazů do logFile.txt
   */
  private $writeToLog = true;
  private $registry;
  private $lastCache;

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
  private $handler;

  /** 
   * Konstruktor databázového objektu 
   */
  public function __construct($registry)
  {
    $this->registry = $registry;
    require_once( FRAMEWORK_PATH . 'registry/databaseobjects/mysql.database.handler.php');
  }

  /** 
   * Vytvoří nové spojení s databází
   * @param string název hostitele
   * @param string uživatelské jméno
   * @param string heslo
   * @param string název databáze
   * @return int identifikátor nového spojení
   */
  public function newConnection($host, $user, $password, $database, $socket = NULL)
  {
    global $caption;
    $this->connections[] = new mysqli($host, $user, $password, $database, null, $socket);
    $connection_id = count($this->connections) - 1;
    if (mysqli_connect_errno()) {
      trigger_error($caption['db_error_connect'] . ' ' . $this->connections[$connection_id]->error, E_USER_ERROR);
    } else {
      $this->connections[$connection_id]->query("set names UTF8");
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
  public function setActiveConnection($new)
  {
    $this->activeConnection = $new;
  }

  /**
   * Vrací aktuální spojení s databází pro následující operace 
   * @return int identifikátor aktivního spojení
   */
  public function getActiveConnection()
  {
    return ($this->activeConnection);
  }

  /**
   * Uloží výsledek dotazu do mezipaměti pro pozdější zpracování
   * @param string dotazový řetězec
   * @return int index výsledku dotazu v mezipaměti
   */
  public function cacheQuery($queryStr)
  {
    global $caption;

    if (!$result = $this->connections[$this->activeConnection]->query($queryStr)) {
      trigger_error($caption['db_error_cachequery'] . ': ' . $this->connections[$this->activeConnection]->error, E_USER_ERROR);
      return -1;
    } else {
      if ($this->writeToLog)
        $this->queryCache[] = $result;
      $this->isCacheQuery = true;
      return count($this->queryCache) - 1;
    }
  }

  /**
   * Získá počet řádků výsledku uloženého v mezipaměti 
   * @param int index výsledku dotazu v mezipaměti
   * @return int počet řádků 
   */
  public function numRowsFromCache($cache_id)
  {
    return $this->queryCache[$cache_id]->num_rows;
  }

  /**
   * Získá výsledek dotazu uložení v mezipaměti 
   * @param int index výsledku dotazu 
   * @return array<mixed> výsledek dotazu 
   */
  public function resultsFromCache($cache_id)
  {
    return $this->queryCache[$cache_id]->fetch_array(MYSQLI_ASSOC);
  }

  /**
   * Uloží data do mezipaměti pro pozdější použití 
   * @param array<mixed> data
   * @return int index dat v mezipaměti 
   */
  public function cacheData($data)
  {
    $this->dataCache[] = $data;
    $this->isCacheQuery = true;
    return count($this->dataCache) - 1;
  }

  /**
   * Získá data z mezipaměti
   * @param int index dat v mezipaměti
   * @return array<mixed> data
   */
  public function dataFromCache($cache_id)
  {
    return $this->dataCache[$cache_id];
  }

  /**
   * Odstraní záznamy z databáze 
   * @param string tabulka, ze které se záznamy odstraní 
   * @param string podmínka pro odstranění 
   * @param int počet řádků, které se mají odstranit 
   * @return void
   */
  public function deleteRecords($table, $condition, $limit = '')
  {
    global $config;
    $table = $config['dbPrefix'] . $table;

    $limit = ($limit === '') ? '' : ' LIMIT ' . $limit;
    $delete = "DELETE FROM {$table} WHERE {$condition} {$limit}";
    $this->executeQuery($delete);
  }

  /**
   * Aktualizuje záznamy v databázi 
   * @param $table název tabulky
   * @param $changes pole změn sloupec => hodnota 
   * @param $condition podmínka
   * @return bool 
   */
  public function updateRecords($table, $changes, $condition, $withPrefix = true)
  {
    global $config;
    if ($table != 'source')
      if ($withPrefix)
        $table = $config['dbPrefix'] . $table;

    $update = "UPDATE " . $table . " SET ";
    foreach ($changes as $field => $value) {
      if (($value == 'NULL') or ($value == null))
        $update .= "`" . $field . "`= NULL ,";
      else
        $update .= "`" . $field . "`='{$value}',";
    };
    onAfterSetUpdateRecords($update, $table, $changes, $condition);

    // remove our trailing ,
    $update = substr($update, 0, -1);
    if ($condition != '') {
      $update .= " WHERE " . $condition;
    }

    $this->executeQuery($update);

    return true;
  }

  /**
   * Vloží záznam do databáze 
   * @param string název tabulky
   * @param array<mixed> pole dat sloupec => hodnota 
   * @return bool 
   */
  public function insertRecords($table, $data, $withPrefix = true)
  {
    global $config;
    if ($withPrefix)
      $table = $config['dbPrefix'] . $table;

    // setup some variables for fields and values
    $fields = "";
    $values = "";

    // vyplnění proměnných
    foreach ($data as $f => $v) {
      $fields  .= "`$f`,";
      if ($v === null) {
        $values .= 'NULL,';
      } else {
        $values .= (is_numeric($v) && (intval($v) == $v)) ? $v . "," : "'$v',";
      }
    };
    onAfterSetInsertRecords($fields, $values, $table, $data);

    // odstranění koncového znaku „,“ 
    $fields = substr($fields, 0, -1);
    // odstranění koncového znaku „,“ 
    $values = substr($values, 0, -1);

    $insert = "INSERT INTO $table ({$fields}) VALUES({$values})";
    $this->executeQuery($insert);
    $this->lastID = $this->connections[$this->activeConnection]->insert_id;
    return true;
  }

  /**
   * Nastavení polí pro SELECT
   * @param string tableName
   * @param string fieldList
   * @return void 
   */
  public function initQuery($tableName, $fieldList = '*', $withPrefix = true)
  {
    global $config;

    $this->querySql = '';
    $this->queryResult = null;
    $this->queryTableName = '';
    $this->queryFieldList = '';
    $this->queryCondition = '';
    $this->queryOrderBy = '';
    $this->isCacheQuery = false;


    if ($fieldList === '') {
      $this->queryFieldList = '*';
    }
    if ($tableName === '') {
      return;
    }
    if ($withPrefix)
      $tableName = $config['dbPrefix'] . $tableName;

    $this->queryTableName = $tableName;
    $this->queryFieldList = $fieldList;
  }

  /**
   * Nastavení podmínky výběru
   * @param string condition
   * @return void 
   */
  public function setCondition($condition)
  {
    if ($this->queryCondition != '') {
      $this->queryCondition .= " AND ";
    }
    $this->queryCondition .= $condition;
  }

  /**
   * Nastavení filtru pole 
   * @param string key
   * @param string value
   * @return void 
   */
  public function setFilter($key, $value)
  {
    if (($key === '') | ($value === '')) {
      return;
    }
    if ($this->queryCondition != '') {
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
   * @param string key
   * @param string valueFrom
   * @param string valueTo
   * @return void 
   */
  public function setRange($key, $valueFrom, $valueTo)
  {
    if (($key === '') | ($valueFrom === '') | ($valueTo === '')) {
      return;
    }
    if ($this->queryCondition != '') {
      $this->queryCondition .= " AND ";
    }
    switch (true) {
      case (is_int($valueFrom) and is_int($valueTo)):
        $this->queryCondition .= "$key BETWEEN $valueFrom AND $valueTo";
        break;
      case (is_double($valueFrom) and is_double($valueTo)):
        $this->queryCondition .= "$key BETWEEN $valueFrom AND $valueTo";
        break;
      default:
        $this->queryCondition .= "$key BETWEEN '$valueFrom' AND '$valueTo'";
        break;
    }
  }


  /**
   * Nastavení řazení pro výběr
   * @param string orderBy
   * @return void 
   */
  public function setOrderBy($orderBy)
  {
    if ($orderBy != '') {
      $this->queryOrderBy = $orderBy;
    }
  }

  /**
   * Nalezení sady záznamů
   * @param int
   * @return boolean
   */
  public function findSet($cache = null)
  {
    if ($cache === null) {
      if ($this->isCacheQuery === true) {
        return false;
      };
      $this->buildQuery();
      if ($this->querySql === '') {
        return false;
      }
      $queryStr = $this->querySql;
      $cache = $this->cacheQuery($queryStr);
    }
    $this->queryResult = null;
    while ($result = $this->resultsFromCache($cache)) {
      $this->queryResult[] = $result;
    }
    return is_array($this->queryResult);
  }

  /**
   * Nalezení prvního záznamu
   * @return boolean
   */
  public function findFirst()
  {
    if ($this->isCacheQuery === true) {
      return false;
    };
    $this->buildQuery();
    if ($this->querySql === '') {
      return false;
    }
    $queryStr = $this->querySql;
    $this->executeQuery($queryStr);
    if ($this->numRows() != 0) {
      $this->queryResult = $this->getRows();
      return true;
    }
    return false;
  }

  /**
   * Nalezení posledního záznamu
   * @return boolean
   */
  public function findLast()
  {
    if ($this->isCacheQuery === true) {
      return false;
    };
    if ($this->queryOrderBy === '') {
      return false;
    }
    $this->queryOrderBy .= ' DESC';
    return $this->findFirst();
  }

  /**
   * Zjištění existence záznamu
   * @return boolean
   */
  public function isEmpty($cache_id = null)
  {
    if ($this->isCacheQuery === true) {
      if ($this->queryCache[$cache_id]->num_rows > 0) {
        return false;
      }
      return true;
    };
    $this->buildQuery();
    if ($this->querySql === '') {
      return true;
    }
    $queryStr = $this->querySql;
    $this->executeQuery($queryStr);
    return ($this->numRows() == 0);
  }

  /**
   * Vrácení výsledku dotazu
   * @return array<mixed>()
   */
  public function getResult()
  {
    return $this->queryResult;
  }

  /**
   * Provede dotaz 
   * @param string $queryStr
   */
  public function executeQuery($queryStr)
  {
    global $caption;

    $this->connections[$this->activeConnection]->query("set names utf8");

    if (!$result = $this->connections[$this->activeConnection]->query($queryStr)) {
      trigger_error($caption['db_error_executequery'] . ': ' . $this->connections[$this->activeConnection]->error, E_USER_ERROR);
    } else {
      $this->last = $result;
    }
  }

  public function lastInsertID()
  {
    return $this->lastID;
  }

  public function lastCahe()
  {
    return $this->lastCache;
  }

  /**
   * Získá řádky výsledku posledního provedeného dotazu
   * @return array<mixed> 
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
   * @param string data, která se mají vyčistit 
   * @return string vyčištěná data
   */
  public function sanitizeData($data)
  {
    return $this->connections[$this->activeConnection]->real_escape_string($data);
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

    if ($table == "") {
      $this->querySql =  "";
      return;
    }
    if ($fields == "") {
      $fields = '*';
    }

    $this->querySql = "SELECT {$fields} FROM {$table}";
    if ($condition != "") {
      $this->querySql .= " WHERE {$condition}";
    }
    if ($orderBy != "") {
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
    foreach ($this->connections as $connection) {
      $connection->close();
    }
  }

  /**
   * Vrací SQL dotaz s limitem na aktuální Page
   * @param $sql - full SQL 
   * @return $sql - limited SQL
   */
  public function getSqlByPage($sql)
  {
    global $config;
    $maxVisibleItem = isset($config['maxVisibleItem']) ? $config['maxVisibleItem'] : 25;

    // Stránkování
    $cacheFull = $this->registry->getObject('db')->cacheQuery($sql);
    $records = $this->registry->getObject('db')->numRowsFromCache($cacheFull);
    $pageCount = (int) ($records / $maxVisibleItem);
    $pageCount = ($records > $pageCount * $maxVisibleItem) ? $pageCount + 1 : $pageCount;
    $pageNo = (isset($_GET['p'])) ? $_GET['p'] : 1;
    $pageNo = ($pageNo > $pageCount) ? $pageCount : $pageNo;
    $pageNo = ($pageNo < 1) ? 1 : $pageNo;
    $fromItem = (($pageNo - 1) * $maxVisibleItem);
    $sql .= " LIMIT $fromItem," . $maxVisibleItem;

    // Apply to template
    $navigate = $this->registry->getObject('template')->NavigateElement($pageNo, $pageCount);
    $this->registry->getObject('template')->getPage()->addTag('navigate_menu', $navigate);

    return $sql;
  }

  /**
   * Sestavení stránky
   * @return void
   */
  private function build($template = 'page.tpl.php')
  {
    // Category Menu
    //$this->registry->getObject('document')->createCategoryMenu();

    // Build page
    $this->registry->getObject('template')->addTemplateBit('search', 'search.tpl.php');
    $this->registry->getObject('template')->addTemplateBit('categories', 'categorymenu-empty.tpl.php');
    $this->registry->getObject('template')->buildFromTemplates('header.tpl.php', $template, 'footer.tpl.php');
  }
}
