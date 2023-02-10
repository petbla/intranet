<?php
/**
 * PdfDocument is class for document printing 
 * This class is build on the Pdf.php from PEAR library 
 * 
 * Generování fontů - popis: http://www.fpdf.org/en/tutorial/tuto7.htm
 * Generování fontů: http://www.fpdf.org/makefont/make.php 
 *
 * @author  Petr Blažek
 * @version 1.0
 * @date    7.2.2023
 */
 
class pdfdocument extends FPDF  
{
  /**
   * Public variable
   *
   */        
  private $registry;
  private $fileName;        // filename

  /**
   * "Label's variablies
   *
   *  -------------------------------------------------------------------------------------------------------------------------
   *  documentType (option)       headerTitle (array)
   *  -------------------------------------------------------------------------------------------------------------------------
   *   zapis                      City, FromMeting, AtDate, MeetingNo, PresentMembers, ExcusedMemberNames, VerifiedMemberNames
   *  -------------------------------------------------------------------------------------------------------------------------
   * 
   */        
  private $labelLines = 4;    // počet řádků adresy        
  private $labelWidth;        // šířka štítku [mm]         
  private $labelHeight;       // výška štítku [mm]        
  private $labelInCols;       // počet štítků ve sloupcích A4         
  private $labelInRows;       // počet štítků v řádcích A4
  private $lSpace = 2;        // vnitřní levý okraj [mm]
  private $rSpace = 2;        // vnitřní pravý okraj [mm]          
  private $lineHeight = 10;   // výška řádku;
  private $documentType;      // Typ dokumentu PDF: zapis
  private $yy = 10;           // Lokální pozice Y
  private $isHeader = false;  // Tisk záhlaví Header() dokumentu na každý list
  private $isFooter = false;  // Tisk patičky Footer() dokumentu na každý list
  private $reportTitle;       // Název tiskové sestavy  
  private $pageNo;
  
  public function __construct( $registry ) 
  {
		$this->registry = $registry;

  }

  public function SetDocument($documentType, $fileName='', $reportTitle = '', $orientation='P', $unit='mm', $format='A4')
  {   
    parent:: __construct($orientation, $unit, $format);
    
    $this->reportTitle = $reportTitle;

    $this->AddFont('candara','','candara.php');
    $this->AddFont('candara','B','candarab.php');
    $this->AddFont('candara','I','candarai.php');
    $this->AddFont('candara','BI','candaraz.php');

    $this->AddFont('calibri','','calibri.php');
    $this->AddFont('calibri','B','calibrib.php');
    $this->AddFont('calibri','I','calibrii.php');
            
    $this->AddFont('times','','times.php');
    $this->AddFont('times','B','timesb.php');
    $this->AddFont('times','I','timesi.php');
            
    if (isset($fileName) && ($fileName <> ''))
      $this->fileName = $fileName;
    else
      $this->fileName = 'document.pdf';    
        
    /*  
     *  $this->documentType 
     *  - report (header, body,footer)
     */
        
    $this->setDocumentType( $documentType );
        
    $sTitle    = 'DMS Server';
    $sSubject  = '';
    $sAuthor   = 'Petr Blažek';
    $sKeywords = '';
    $sCreator  = 'Create By PHP7 (PEAR)';
    
    $this->SetTitle($sTitle,true);
    $this->SetSubject($sSubject,true);
    $this->SetAuthor($sAuthor,true);
    $this->SetKeywords($sKeywords,true);
    $this->SetCreator($sCreator,true);

  } // END constructor

  public function setDocumentType( $documentType )
  {
    $pos = strpos($documentType,'_');
    if ($pos > 0)
      $this->documentType = substr($documentType,0,$pos - 1);
    else
      $this->documentType = $documentType;
    
    switch ($documentType)
    {
      case 'report':
        $this->SetFont('calibri','',9);
        $this->isHeader = true;
        $this->isFooter = true;
        break;
      case 'document':
        $this->SetFont('calibri','',9);
        $this->isHeader = false;
        $this->isFooter = true;
        break;
      case 'label_2x7':
        // DIN 5008 - údaje pro štítek XxXmm , 2x7 ks, 4 řádky
        $this->labelInCols = 2;                    
        $this->labelInRows = 7;                           
        $this->labelLines  = 4;                   
        $this->labelWidth  = 104;                  
        $this->labelHeight = 42;
        $this->SetMargins(0,10); // $left, $top, $right=null
        $this->SetFont('calibri','',16);
        $this->lineHeight = 7;
        $this->lSpace = 20;
        break;
      case 'label_2x6':
        // DIN 5008 - údaje pro štítek XxXmm , 2x7 ks, 4 řádky
        $this->labelInCols = 2;                    
        $this->labelInRows = 6;                           
        $this->labelLines  = 4;                   
        $this->labelWidth  = 104;                  
        $this->labelHeight = 49.5;
        $this->SetMargins(0,4); // $left, $top, $right=null
        $this->SetFont('calibri','',16);
        $this->lineHeight = 6;
        $this->lSpace = 20;
        break;
      case 'label_4x13':
        // DIN 5008 - údaje pro štítek 51.5x21mm , 4x13 ks, 4 řádky        
        $this->labelInCols = 4;                    
        $this->labelInRows = 13;                           
        $this->labelLines  = 4;                   
        $this->labelWidth  = 52.4;                  
        $this->labelHeight = 21.65;
        $this->SetMargins(0,8); // $left, $top, $right=null
        $this->SetFont('calibri','',10);
        $this->lineHeight = 4;
        $this->lSpace = 5;
        break;
      case 'label_4x10':        
        $this->labelInCols = 4;                    
        $this->labelInRows = 10;                           
        $this->labelLines  = 4;                   
        $this->labelWidth  = 52.5;                  
        $this->labelHeight = 29.7;
        $this->SetMargins(0,2); // $left, $top, $right=null
        $this->SetFont('calibri','',10);
        $this->lineHeight = 4;
        $this->lSpace = 10;
        break;
      case 'label_7x19':        
        $this->labelInCols = 7;                    
        $this->labelInRows = 19;                           
        $this->labelLines  = 3;                   
        $this->labelWidth  = 30;                  
        $this->labelHeight = 15;
        $this->SetMargins(0,7); // $left, $top, $right=null
        $this->SetFont('calibri','',10);
        $this->lineHeight = 3;
        $this->lSpace = 4;
        break;
      case 'label_5x13':        
        // DIN 5008 - údaje pro štítek XxXmm , 2x7 ks, 4 řádky        
        $this->labelInCols = 5;                    
        $this->labelInRows = 13;                           
        $this->labelLines  = 4;                   
        $this->labelWidth  = 38;                  
        $this->labelHeight = 21.3;
        $this->SetMargins(10,10); // $left, $top, $right=null
        $this->SetFont('calibri','',9);
        $this->lineHeight = 4;
        $this->lSpace = 2;
        break;    
      case 'postReceipt':
        $this->SetFont('calibri','',10);
        break;
      case 'sek':
        $this->SetFont('calibri','',10);
        break;
    }  // END switch
  }
  
 
  function Show()
  {
    // D - download
    // F - file
    // I - standard output (PDF view)
    $this->Output($this->fileName,'I');
  }

  function NewDocument()
  {      
    $this->pageNo = 0;
    $this->HeaderDocument();
  }

  function HeaderDocument()
  {
    global $config, $caption;
    
    $this->AddFont('candara','','candara.php');
    $this->AddPage();
    $this->pageNo++;   
  } 
      
  /**
   *  Print header if $isHeader = true
   * 
   *  Cell($family, $style='', $size=0, $xpos=0, $nextln=0, $w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
   */
  function Header()
  {
    global $config;
    $skin = $config['skin'];
    $yy = $this->tMargin;
    
    if (!$this->isHeader)
      return;
      
    switch ($this->documentType)
    {
      case 'report':
        //Logo      
        $this->Image("views/$skin/images/logoPrint.jpg",15,$yy + 10,25);
        $this->SetY($yy + 10);
        break;
    }
    $this->yy = $this->GetY();
  }


  //Page footer
  function Footer()
  {
    global $config, $caption;

      if (!$this->isFooter)
        return;
    
    //Position at 1.5 cm from bottom
    $this->SetFontSize(6);        
    $this->SetXY(15,- 15);
    $yy = $this->GetY();
    
    switch ($this->documentType)
    {
      case 'report':
      case 'zapis':
        // Page number
        $this->SetY($yy);
        $this->Cell(0,10,$this->_utf2win($caption['PageNo']).' '.$this->PageNo(),0,0,'C');
        break;
    }
  }

  public function DocumentTitle($headerTitle){
    global $config;
    $skin = $config['skin'];
    $yy = $this->tMargin;

    //Logo      
    $this->Image("views/$skin/images/logoPrint.jpg",15,$yy + 10,25);
    $this->SetY($yy + 10);
            
    $this->WriteCell('times','B',40, 40, 12, 150,0,$headerTitle['City'],0,0,'C');
    $this->WriteCell('times','BI',18, 40, 8, 150,0,'ZÁPIS',0,0,'C');
    $this->WriteCell('times','I',18, 40, 8, 150,0,$headerTitle['FromMeting'].', '.$headerTitle['AtDate'],0,0,'C');
    $this->WriteCell('times','BI',18, 40, 8, 150,0,$headerTitle['MeetingNo'],0,0,'C');
    $this->WriteCell('times','',14, 40, 8, 150,0,$headerTitle['PresentMembers'],0,0,'C');
    if($headerTitle['ExcusedMemberNames'] != '')
      $this->WriteCell('times','',14, 40, 8, 150,0,$headerTitle['ExcusedMemberNames'],0,0,'C');
    if($headerTitle['VerifiedMemberNames'] != '')
      $this->WriteCell('times','',14, 40, 8, 150,0,$headerTitle['VerifiedMemberNames'],0,0,'C');
    $this->WriteCell('times','B',14, 40, 8, 150,0,'USNÁŠENÍ SCHOPNÉ',0,0,'C');

    $this->Ln(4);
    $this->WriteCell('times','',12, 20, 2, 150,0,'PROGRAM:',0,0,'L');
  }


  function LineProgramPoint($lineno,$text)
  {  
    $yy = $this->GetY();
    $this->SetXY(20,$yy);
    $this->SetFont('times','',12);
    $this->Cell(10,10,$lineno,0,0,'L');
    $this->Cell(150,10,$this->_utf2win($text),0,0,'L');
    $yy += 5;
    $this->SetY($yy);
  } 

  function MeetingLineZapis($meetingline)
  {  
    $lineno = $meetingline['LineNo'];
    if ($meetingline['LineNo2'] > 0)
        $lineno .= '.'.$meetingline['LineNo2'];
    $lineno .= '/';

    // Content
    $content = $meetingline['Content'];
    $yy = $this->GetY();
    $yy += 5;
    $this->SetXY(10,$yy);
    $this->SetFont('times','',12);
    $this->Cell(10,5,$lineno,0,0,'L');
    $yy = $this->GetY();

    $arr = explode("\n",$content);
    foreach($arr as $content){
      $content = trim($content);
      if($content != ''){
        $this->SetXY(20,$yy);
        $this->MultiCell(170,5,$this->_utf2win($content),0,'L');
        $yy = $this->GetY();
      }
    }

    // Discussion
    $content = $meetingline['Discussion'];
    if($content != ''){
      $yy = $this->GetY();
      $yy += 5;
      $this->SetXY(20,$yy);
      $this->SetFont('times','B',12);
      $this->Cell(170,5,$this->_utf2win('Diskuze:'),0,'L');
      $yy = $this->GetY();
      $yy += 5;
      $this->SetFont('times','',12);
      $arr = explode("\n",$content);
      foreach($arr as $content){
        $content = trim($content);
        if($content != ''){
          $this->SetXY(20,$yy);
          $this->MultiCell(170,5,$this->_utf2win($content),0,'L');
          $yy = $this->GetY();
        }
      }
      $this->SetXY(20,$yy + 2);
    }

    // Vote
    if($meetingline['Vote']){
      $yy = $this->GetY();
      $yy += 2;
      $this->SetXY(20,$yy);
      $this->SetFont('times','',12);
      $vote = $meetingline['VoteFor'] == 0 ? '0' : $meetingline['VoteFor'];
      $this->Cell(170,5,'pro: '.$vote,0,'L');
      $yy = $this->GetY();
      $this->SetXY(40,$yy);
      $vote = $meetingline['VoteAgainst'] == 0 ? '0' : $meetingline['VoteAgainst'];
      $this->Cell(170,5,'proti: '.$vote,0,'L');
      $yy = $this->GetY();
      $this->SetXY(60,$yy);
      $vote = $meetingline['VoteDelayed'] == 0 ? '0' : $meetingline['VoteDelayed'];
      $this->Cell(170,5,$this->_utf2win('zdržel se: ').$vote,0,'L');
      $yy = $this->GetY();
      $this->SetXY(20,$yy + 5);
    }

    // DraftResolution
    $content = $meetingline['DraftResolution'];
    if($content != ''){
      $yy = $this->GetY();
      $yy += 5;
      $this->SetXY(20,$yy);
      $this->SetFont('times','B',12);
      $this->Cell(170,5,$this->_utf2win('Usnesení:'),0,'L');
      $yy = $this->GetY();
      $yy += 5;
      $this->SetFont('times','',12);
      $arr = explode("\n",$content);
      foreach($arr as $content){
        $content = trim($content);
        if($content != ''){
          $this->SetXY(20,$yy);
          $this->MultiCell(170,5,$this->_utf2win($content),0,'L');
          $yy = $this->GetY();
        }
      }
      $this->SetXY(20,$yy + 2);
    }

  } 

  function ItemFooter($totalAmount)
  {
    global $caption,$config;

    $yy = $this->GetY() + 10;
    $this->SetXY(10,$yy);
    $this->SetFontSize(9);   
    
    if ($this->CheckEndPage()){
      $this->ItemHeader();
      $yy = $this->GetY() + 10;   
    }

    $this->Cell(160,10,$this->_utf2win($caption['Summary_cost']),0,0,'R');
    $this->Cell(25,10,$this->_utf2win($totalAmount.' '.$config['currency_symbol']),0,0,'R');
  } // END function ItemFooter



  function CheckEndPage()
  {
    if ($this->GetY() > 270)
    {
      $this->HeaderInvoice(0);
      return true; 
    }
    return false;
  } // END function CheckEndPage

  
  function CompressArray( $aArr )
  {
    foreach ($aArr as $sText){
      if (isset($sText) && ($sText <> ''))
        $aCompressArr[] = $sText; 
    }
    return $aCompressArr;
  } // END function CompressArray
   
  function CreateLabel($aDataLabel,$aFontSize,$fromCol,$fromRow,$countLabels) 
  {   
    global $deb;
    
    if ($countLabels == 0)       
      $countLabels = count($aDataLabel);      
        
    $this->AddPage();
    
    $loop = 0;       
    while (($loop < $countLabels)) {
      for ($row=1; $row<=$this->labelInRows; $row++) {            
        for ($col=1; $col<=$this->labelInCols; $col++) {              
          if ($row >= $fromRow) {          
            if ($row > $fromRow || $col >= $fromCol) {            
              if (($loop < $countLabels) ){              
                $x = $this->lMargin + (($col - 1) * $this->labelWidth);               
                $y = $this->tMargin + (($row - 1) * $this->labelHeight);              
                $aData = $this->CompressArray( $aDataLabel[$loop] );
                $lines = count($aData);
                if ($lines > $this->labelLines)
                  $lines = $this->labelLines;
                
                $tSpace = ($this->labelHeight - ($this->lineHeight * $lines)) / 2;     
                
                for ($i=0; $i<$lines; $i++) {
                  $text = $aData[$i];
                  if (isset($aFontSize[$i]) && is_int($aFontSize[$i]) )
                    $this->SetFontSize($aFontSize[$i]);
                  $this->Text($x + $this->lSpace,$y + ($i * $this->lineHeight) + $tSpace,$this->_utf2win($text));
                }
                           
              }else              
                break;              
              $loop++;
            };        
          };      
        };            
      };
      if ($loop < $countLabels)        
        $this->AddPage();    
    };    
  } // END function CreateLabel        
  
  function CreatePostReceipt($aInvoice, $fDobirka=null)
  {      
    global $caption, $config;
    global $deb;
    
    $this->AddPage();
    
    // Company Address
    $sZip = str_replace(' ', '', $config['compZip']);
    if (strlen($sZip) == 5) {
      $sZip = substr($sZip,0,3).' '.substr($sZip,3,2);
    }    
    $this->Text(17,43,$this->_utf2win($config['compName']));
    $this->Text(17,49,$this->_utf2win($config['compAddress']));
    $this->Text(29,55,$this->_utf2win($config['compCity']));
    $this->TextWider(4,55,4,$sZip);
    
    // Customer Address
    $sZip = str_replace(' ', '', $aInvoice['deliveryAddress']['zipcode']);
    if (strlen($sZip) == 5) {
      $sZip = substr($sZip,0,3).' '.substr($sZip,3,2);
    }    
    //$this->SetValue("charspacing",1);
    $this->TextWider(17,61,0,$this->_utf2win($aInvoice['deliveryAddress']['name']));
    $this->Text(17,67,$this->_utf2win($aInvoice['deliveryAddress']['address']));
    $this->Text(17,74,$this->_utf2win($aInvoice['deliveryAddress']['city']));
    $this->TextWider(4,79,4,$sZip);   
    
    //$this->SetValue("charspacing",1);
    if (isset($fDobirka)){
      $this->Text(56,67,'='.$this->tPrice($fDobirka).'=');  
    }
  } // END function CreatePostReceipt

  
  function CreatePostC118($aInvoice, $fDobirka=null)
  {      
    global $caption, $config;
    
    $this->AddPage();
    
    // Company Address
    $this->Text(97,39.5,$this->_utf2win($config['compName']));
    $this->Text(97,45.5,$this->_utf2win($config['compAddress']));
    $this->Text(97,52,$this->_utf2win($config['compCity']));
    $sZip = str_replace(' ', '', $config['compZip']);
    $this->TextWider(69.5,55.5,5,$sZip);
    
    // Customer Address
    $this->Text(97,68,$this->_utf2win($aInvoice['deliveryAddress']['name']));
    $this->Text(97,72,$this->_utf2win($aInvoice['deliveryAddress']['address']));
    $this->Text(97,77,$this->_utf2win($aInvoice['deliveryAddress']['city']));
    $sZip = str_replace(' ', '', $aInvoice['deliveryAddress']['zipcode']);
    $this->TextWider(69,80,4,$sZip);   
    
    if (isset($fDobirka)){
      $sPrice = str_replace('.', '', $this->tPrice($fDobirka));
      $sPrice = str_pad($sPrice , 10, ' ',STR_PAD_LEFT);
      $this->TextWider(123,13.5,5,$sPrice);
    }
  } // END function CreatePostC118

  function CreatePostA116($aInvoice, $fDobirka=null)
  {      
    global $caption, $config;
        
    // Base Init
    $this->AddPage();
                
    // Company Address - Right
    $this->Text(87,36,$this->_utf2win($config['compName']));
    $this->Text(64,43,$this->_utf2win($config['compAddress']));
    $sZip = str_replace(' ', '', $config['compZip']);
    $this->Text(64,50,$sZip . ' ' . $this->_utf2win($config['compCity']));

    // Company Address - Left
    $this->Text(25,53,$this->_utf2win($config['compName']));
    $this->Text(5,59,$this->_utf2win($config['compAddress']));
    $this->Text(5,64,$sZip . ' ' . $this->_utf2win($config['compCity']));
    
    // Customer Address - Right
    $this->TextWider(124,50,5,$this->_utf2win($aInvoice['deliveryAddress']['name']));
    $this->TextWider(124,64,5,$this->_utf2win($aInvoice['deliveryAddress']['address']));
    $this->TextWider(124,71,5,$this->_utf2win($aInvoice['deliveryAddress']['city']));    
    $sZip = str_replace(' ', '', $aInvoice['deliveryAddress']['zipcode']);
    $this->TextWider(124,78,5,$sZip,124,76);   

    // Customer Address - Left
    $this->Text(15,84,$this->_utf2win($aInvoice['deliveryAddress']['name']));
    $this->Text(15,89,$this->_utf2win($aInvoice['deliveryAddress']['address']));
    $this->Text(15,94,$sZip.' '.$this->_utf2win($aInvoice['deliveryAddress']['city']));    
    
    // Bank Account - Right
    $this->TextWider(125,19,5,'6701002207961203');           
    $this->TextWider(125,28,5,'6210');                     
    $sPrice = str_pad($aInvoice['orderNo'] , 10, '0',STR_PAD_LEFT);
    $this->TextWider(154,28,5,$aInvoice['orderNo'],153.5,27);
    $this->TextWider(125,36,5,'0008');                     
    
    // Bank Account - Left
    $this->Text(15,70.5,'6701002207961203/6210');
    $this->Text(16,75,$aInvoice['orderNo']);         //VS      
    
    // Amount
    if (isset($fDobirka)){
      $sPrice = str_replace('.', '', $this->tPrice($fDobirka));
      $sPrice = str_pad($sPrice , 10, ' ',STR_PAD_LEFT);
      $this->TextWider(125,9,5,$sPrice);
      
      $sPrice = str_replace('.', '  ', $this->tPrice($fDobirka));
      $sPrice = str_pad($sPrice , 10, ' ',STR_PAD_LEFT);
      $this->TextWider(20,36.5,4,$sPrice);
      
      $sCastkaSlovy = $this->_utf2win($this->registry->getObject('fce')->AmmountWords($fDobirka));
      if (strlen($sCastkaSlovy) > 25  ){
        $this->Text(10,42,'=='.substr($sCastkaSlovy,0,25));
        $this->Text(3,47.5,substr($sCastkaSlovy,25).'==');
      }else{
        $this->Text(10,42,'=='.$sCastkaSlovy.'==');
      }
    }
  } // END function CreatePostA116
  
  /*
   * ---------------------------------------------------------------------------
   *  TABLE
   * ---------------------------------------------------------------------------   
   */   
  function BasicTable($header,$data)
  {
      //Header
      foreach($header as $col)
          $this->Cell(40,7,$col,1);
      $this->Ln();
      //Data
      foreach($data as $row)
      {
          foreach($row as $col)
              $this->Cell(40,6,$this->_utf2win($col),1);
          $this->Ln();
      }
  }
  
  
  // ---------------------------------------------------------------------------

  /**
   *  Extended function Cell()
   * 
   *  Cell($family, $style='', $size=0, $xpos=0, $nextln=0, $w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
   */
  function writeCell($family, $style='', $size=0, $xpos=0, $nextln=0, $w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link=''){
    $this->SetFont($family, $style, $size);
    $this->SetX($xpos);  
    $this->Cell($w, $h, $this->_utf2win($txt), $border, $ln, $align, $fill, $link);
    if($nextln > 0)
      $this->Ln($nextln);
  }


  function _utf2win( $text )
  {
    return(iconv('utf-8','cp1250',$text));
  } // END function _utf2win

  function _border()
  {
    $yy = $this->yy;
    $this->Rect($this->lMargin,$yy, $this->w - (2 * $this->lMargin),
               $this->h - ($yy + $this->tMargin));
  } // END function _border

  function TextWider($x,$y,$spacing,$text)
  {
    global $deb;
    
    if (isset($text) && is_string($text) && is_numeric($spacing)){ 
      if ($spacing == 0){
        $this->Text($x,$y,$text);
        return;      
      }
      $count = strlen($text);
      $this->SetXY($x,$y);
      for ($i=0; $i<$count; $i++) {
        $this->Text($x + ($i * $spacing),$y , $text[$i]);
      }
    }
  } // END function TextWider

} // END class PdfDocument


?>