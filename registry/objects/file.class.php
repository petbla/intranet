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
		$this->registry->getObject('db')->initQuery('DmsEntry','');
    $this->registry->getObject('db')->setFilter('name',$name);
    $this->registry->getObject('db')->setFilter('path',$path);
    if (!($this->registry->getObject('db')->isEmpty()))
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

  public function addPath( $path )
  {
    $level = substr_count($path,DIRECTORY_SEPARATOR);
    $p = explode(DIRECTORY_SEPARATOR,$path);
    $parent = 0;
    $path = '';
    for ($i=0; $i < $level; $i++) 
    { 
      $name = $p[$i];
      $title = $name;
      
      $this->registry->getObject('db')->initQuery('DmsEntry');
      $this->registry->getObject('db')->setCondition( "level=$i AND name='$name'" );
      if( $this->registry->getObject('db')->findFirst() == false )
      {
        // Find last line No. of 
        $lineNo = 0;
        $this->registry->getObject('db')->initQuery('DmsEntry','Level,LineNo');
        $this->registry->getObject('db')->setCondition( "level=$i" );
        $this->registry->getObject('db')->setOrderBy('LineNo');
        if( $this->registry->getObject('db')->findLast())
        {
          $entry = $this->registry->getObject('db')->getResult();
          $lineNo = $entry['LineNo'];
        }
        $lineNo += 100;
        
        // Insert NEW

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
        $this->registry->getObject('db')->findFirst();

      }
      $parentEntry = $this->registry->getObject('db')->getResult();
      $parent = $parentEntry['EntryNo'];
      $path .= $name.$path.DIRECTORY_SEPARATOR;
    }
  }


  public function getFiles(){ 
      
    global $config; 
    
    //  $files = $this->registry->getObject('file')->getFiles($config['fileserver']);

    // Type: 20-directory, 30-file

    $item  = array('level'=>0,'type'=>0,'name'=>'','title'=>'','path'=>'','fileExtension'=>''); 
    $files = array();
    
    $root = $config['fileserver'];

    $last_letter  = $root[strlen($root)-1]; 
    $root  = ($last_letter == '\\' || $last_letter == '/') ? $root : $root.DIRECTORY_SEPARATOR; 
    
    $directories[]  = iconv("windows-1250","utf-8",$root);
    
    $paret = 0;
    $level = 0;

    while (sizeof($directories)) { 
      $dir = array_pop($directories); 
      $dir = iconv("windows-1250","utf-8",$dir);
      if ($handle = opendir($dir)) { 
        while (false !== ($file = readdir($handle))) 
        { 
          $file = iconv("windows-1250","utf-8",$file);
          if ($file == '.' || $file == '..') 
          { 
            continue; 
          };
          $item = array();
          $item['path'] = str_replace($root,'',$dir);
          $item['name'] = $item['path'].$file;
          $item['title'] = $file;
          $file = $dir.$file;
          if (is_file($file)) 
          { 
            $item['type'] = 30;
            $item['fileExtension'] = pathinfo($file,PATHINFO_EXTENSION);
            $ext = $item['fileExtension'];
            if ($ext != '')
            {
              $item['title'] = substr($item['title'],0,strlen($item['title']) - strlen($ext) - 1);            
            }
            $tt = $item['title'];
            if($tt[strlen($tt)-1] == '.')
            {
              $item['title'] = substr($tt,0,strlen($tt) );            
            }
          } 
          else
          { 
            $item['type'] = 20;
            $directory_path = $file.DIRECTORY_SEPARATOR; 
            array_push($directories, $directory_path); 
          } 
          $item['level'] = substr_count($item['path'],DIRECTORY_SEPARATOR);          
          $files[]  = $item; 
        } 
        closedir($handle); 
      } 
    } 
    return $files; 
  } 
}