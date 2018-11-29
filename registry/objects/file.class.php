<?php
/**
 * Správce souborů
 *
 * @author  Petr Blažek
 * @version 1.0
 * @date    27.11.2018 
 */

class file {

  public function __construct( $registry ) 
  {
    $this->registry = $registry;
  }

  public function addItem( $item = array('level'=>0,'type'=>0,'name'=>'','title'=>'','path'=>'','fileExtension'=>'')){

    if ($item['name']==''|$item['path'] ==''){
      return;
    }
    $level = $item['level'];
    $type = $item['type'];
    $name = $this->registry->getObject('db')->sanitizeData($item['name']);
    $title = $this->registry->getObject('db')->sanitizeData($item['title']);
    $path = $this->registry->getObject('db')->sanitizeData($item['path']);
    $fileExtension = $item['fileExtension'];

    // Check IF exists
    $sql = "SELECT * FROM DmsEntry WHERE name='$name' AND path='$path'";
    $this->registry->getObject('db')->executeQuery( $sql );			
    if( $this->registry->getObject('db')->numRows() != 0 )
    {
      return;
    }

    // Check and create Parents
    $this->addPath( $item['path'] , $level);

    // Find Parent
    if ($level != 0 )
    {
      $p = explode(DIRECTORY_SEPARATOR,$item['path']);
    
      $plevel = $level - 1;
      $pname = $p[$plevel];
      $sql = "SELECT * FROM DmsEntry WHERE level=$plevel AND name='$pname'";

      //TODO - najít Parent
    }
    else
    {
      $parent = 0;
    }

    // Insert NEW
    $id = $this->registry->getObject('fce')->GUID();
    $lineNo = 0;

    $sql = "INSERT INTO DmsEntry (ID,Level,Parent,Type,LineNo,Title,Name,Path,FileExtension) 
              VALUES ('$id',$level,$parent,$type,$lineNo,'$title','$name','$path','$fileExtension')";
    
    //TODO - zalozit položku

  }

  public function addPath( $path ){
    $level = substr_count($path,DIRECTORY_SEPARATOR);
    $p = explode(DIRECTORY_SEPARATOR,$path);
    $parent = 0;
    $path = '';
    for ($i=0; $i < $level; $i++) { 
      $name = $p[$i];
      $title = $name;
      $sql = "SELECT * FROM DmsEntry WHERE level=$i AND name='$name'";
      $this->registry->getObject('db')->executeQuery( $sql );			
      if( $this->registry->getObject('db')->numRows() == 0 ){
        $lineNo = 0;
        $sql = "SELECT Level,LineNo FROM DmsEntry WHERE level=$i ORDER BY LineNo DESC";
        $this->registry->getObject('db')->executeQuery( $sql );			
        if( $this->registry->getObject('db')->numRows() != 0 ){
          $entry = $this->registry->getObject('db')->getRows();
          $lineNo = $entry['LineNo'];
        }
        $lineNo += 100;
        
        $data = array();
        $data['ID'] = $this->registry->getObject('fce')->GUID();
        $data['Level'] = $i;
        $data['Parent'] = $parent;
        $data['Type'] = 20;
        $data['LineNo'] = $lineNo;
        $data['Title'] = $this->registry->getObject('db')->sanitizeData($title); 
        $data['Name'] = $this->registry->getObject('db')->sanitizeData($name);
        $data['Path'] = $this->registry->getObject('db')->sanitizeData($path);
        $data['FileExtension'] = '';

        $this->registry->getObject('db')->insertRecords( 'DmsEntry', $data );
        $sql = "SELECT * FROM DmsEntry WHERE level=$i AND name='$name'";
        $this->registry->getObject('db')->executeQuery( $sql );			
      }
      $parentEntry = $this->registry->getObject('db')->getRows();
      $parent = $parentEntry['EntryNo'];
      $path .= $name.$path.DIRECTORY_SEPARATOR;
    }
  }


  public function getFiles($root = '.'){ 
      
    //  $files = $this->registry->getObject('file')->getFiles($config['fileserver']);

    // Type: 20-directory, 30-file

    $item  = array('level'=>0,'type'=>0,'name'=>'','title'=>'','path'=>'','fileExtension'=>''); 
    $files = array();
    $last_letter  = $root[strlen($root)-1]; 
    $root  = ($last_letter == '\\' || $last_letter == '/') ? $root : $root.DIRECTORY_SEPARATOR; 
    
    $directories[]  = $root; 
    
    while (sizeof($directories)) { 
      $dir  = array_pop($directories); 
      if ($handle = opendir($dir)) { 
        while (false !== ($file = readdir($handle))) { 
          if ($file == '.' || $file == '..') { 
            continue; 
          };
          $file = iconv("windows-1250","utf-8",$file);
          $item['name'] = $file;
          $file = $dir.$file; 
          if (is_dir($file)) { 
            $item['type'] = 20;
            $item['title'] = $item['name'];
            $directory_path = $file.DIRECTORY_SEPARATOR; 
            array_push($directories, $directory_path); 
          } elseif (is_file($file)) { 
            $item['type'] = 30;
            $item['fileExtension'] = pathinfo($item['name'],PATHINFO_EXTENSION);
            $f = explode('.',$item['name']);
            $item['title'] = $f[0];
          } 
          $item['path'] = str_replace($root,'',$file);
          $item['path'] = str_replace($item['name'],'',$item['path']);
          $item['level'] = substr_count($item['path'],DIRECTORY_SEPARATOR);
          $files[]  = $item; 
        } 
        closedir($handle); 
      } 
    } 
    return $files; 
  } 
}