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


  public function findItem( $fullName )
  {
    global $config;

    $root = $config['fileserver'];
    $name = str_replace($root,'',$fullName);
    $type = $this->getFileType($fullName);
    if ($name == '')
    {
      return 0;
    }
       
    $this->registry->getObject('db')->initQuery('DmsEntry','EntryNo,Name');
    $sanitize_name = iconv("windows-1250","utf-8",$name);
    $sanitize_name = $this->registry->getObject('db')->sanitizeData($sanitize_name);
    $this->registry->getObject('db')->setCondition( "Name='$sanitize_name'" );
    if( $this->registry->getObject('db')->findFirst())
    {
       $parentEntry = $this->registry->getObject('db')->getResult();
       return $parentEntry['EntryNo'];
    }

    $level = substr_count($name,DIRECTORY_SEPARATOR);
    
    $pathArr = explode(DIRECTORY_SEPARATOR, $name);
    if ($name[strlen($name)-1] == DIRECTORY_SEPARATOR)
    {
      $name = substr($name,0,strlen($name) - 1);
      array_pop($pathArr);
    }

    if(count($pathArr) > 1)
    {
      $title = $pathArr[count($pathArr) - 1];
      array_pop($pathArr);        
      $path = implode(DIRECTORY_SEPARATOR, $pathArr);
    }
    else
    {
      $title = $name;
      $path = '';
    }
    $title = iconv("windows-1250","utf-8",$title);
    $name = iconv("windows-1250","utf-8",$name);
    
    // Insert NEW folder

    $data = array();
    $data['ID'] = $this->registry->getObject('fce')->GUID();
    $data['Level'] = $level;
    $data['Parent'] = $this->findItem($root.$path);
    $data['Type'] = $type;
//    $data['LineNo'] = $lineNo;
    $data['Title'] = $this->registry->getObject('db')->sanitizeData($title); 
    $data['Name'] = $this->registry->getObject('db')->sanitizeData($name);
//    $data['Path'] = $this->registry->getObject('db')->sanitizeData($path);
    $data['FileExtension'] = '';
//    $data['ModifyDateTime'] = date(" d.m.Y H:i:s.", fileatime($fullName)); // datum a čas změny

    $this->registry->getObject('db')->insertRecords( 'DmsEntry', $data );
    $this->registry->getObject('db')->findFirst();

    $parentEntry = $this->registry->getObject('db')->getResult();
    return $parentEntry['EntryNo'];
  }


  public function getFiles($root = '.'){ 

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
          $fullName = $dir.$file;
          $entryNo = $this->findItem($fullName);
          $type = $this->getFileType($fullName);

          if ($type == 20) 
          { 
            $directory_path = $fullName.DIRECTORY_SEPARATOR; 
            array_push($directories, $directory_path); 
          } 
/*          
          elseif ($type == 30) 
          { 
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
*/         
        } 
        closedir($handle); 
      } 
    } 
  } 
  
  private function getFileType ($file)
  {
    if (is_dir($file)) 
    { 
      return 20;
    } 
    elseif (is_file($file)) 
    { 
      return 30;
    }
    else
    {
      return (substr_count($file,'.') > 0 ? 30 : 20);  
    }
  }
}