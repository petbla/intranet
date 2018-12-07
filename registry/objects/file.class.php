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

  public function findPath( $path )
  {
    if ($path == '')
    {
      return 0;
    }
    $last_letter = $path[strlen($path)-1];
    $name = $path;

    $type = ($last_letter == DIRECTORY_SEPARATOR) ? 20 : 30;
    $this->registry->getObject('db')->initQuery('DmsEntry','EntryNo,Name');
    $sanitize_name = $this->registry->getObject('db')->sanitizeData($path);
    $this->registry->getObject('db')->setCondition( "Name='$sanitize_name'" );
    $this->registry->getObject('db')->setCondition( "Type=$type" );
    if( $this->registry->getObject('db')->findFirst())
    {
       $parentEntry = $this->registry->getObject('db')->getResult();
       return $parentEntry['EntryNo'];
    }

    $level = substr_count($path,DIRECTORY_SEPARATOR);
    if ($level > 2)
    {
      $level = $level;
    }

    $tree = explode(DIRECTORY_SEPARATOR, $path);
    if($type == 20)
    {
      array_pop($tree);
    }
    if(count($tree) >= 1)
    {
      $title = $tree[count($tree) - 1];
      array_pop($tree);        
    }
    else
    {
      $title = $tree[0];
    }
    $path = implode(DIRECTORY_SEPARATOR, $tree);
    if ($path != '')
    {
      $path .= DIRECTORY_SEPARATOR;
    }
    
    // Insert NEW folder

    $data = array();
    $data['ID'] = $this->registry->getObject('fce')->GUID();
    $data['Level'] = $level;
    $data['Parent'] = $this->findPath($path);
    $data['Type'] = $type;
//    $data['LineNo'] = $lineNo;
    $data['Title'] = $this->registry->getObject('db')->sanitizeData($title); 
    $data['Name'] = $this->registry->getObject('db')->sanitizeData($name);
    $data['Path'] = $this->registry->getObject('db')->sanitizeData($path);
    $data['FileExtension'] = '';

    $this->registry->getObject('db')->insertRecords( 'DmsEntry', $data );
    $this->registry->getObject('db')->findFirst();

    $parentEntry = $this->registry->getObject('db')->getResult();
    return $parentEntry['EntryNo'];
  }


  public function getFiles($root = '.'){ 
      
    //  $files = $this->registry->getObject('file')->getFiles($config['fileserver']);

    // Type: 20-directory, 30-file

    $item  = array('level'=>0,'parent'=>0,'type'=>0,'name'=>'','title'=>'','path'=>'','fileExtension'=>'','modifyDateTime'=>''); 
    $files = array();
    $last_letter  = $root[strlen($root)-1]; 
    $root = ($last_letter == '\\' || $last_letter == '/') ? $root : $root.DIRECTORY_SEPARATOR; 
    $directories[]  = $root; 
    $paret = 0;
    $level = 0;

    while (sizeof($directories)) { 
      $dir  = array_pop($directories); 
      if ($handle = opendir($dir)) { 
        while (false !== ($file = readdir($handle))) 
        { 
          if ($file == '.' || $file == '..') 
          { 
            continue; 
          };
          $file = iconv("windows-1250","utf-8",$file);

          // Tady je problém, protože v $file není u položky Directory lomítko
          // což znamená, že z $file neumím poznam, jestli to je soubor nebo složka
          // pokud je v $file nějaká diakritika => is_dir ani is_file v tomto případě nezafunguje


          $item = array();
          $item['path'] = str_replace($root,'',$dir);
          $item['name'] = $item['path'].$file;
          $item['entryNo'] = $this->findPath($item['name']);
          
//          $item['parent'] = $this->findPath($item['path']);
          $item['title'] = $file;
          
          $item['modifyDateTime'] = date(" d.m.Y H:i:s.", fileatime('c:\temp\pokus.txt')); // datum a čas změny
                  

          if (is_dir($file)) 
          { 
            $item['type'] = 20;
            $directory_path = $file.DIRECTORY_SEPARATOR; 
            array_push($directories, $directory_path); 
          } 
          elseif (is_file($file)) 
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
            $item['type'] = 99;
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