<?php
/**
 * PdfDocument is class for document printing as invoice,labels,post address etc.
 * This class is build on the Pdf.php from PEAR library 
 *
 * @author  Petr Blažek
 * @version 1.0
 * @date    10.7.2011 
 */
 
class pdfdocument extends FPDF  
{
  /**
   * Public variable
   *
   */        
  var $fileName;        // filename
  /**
   * "Label's variablies
   *
   */        
  var $labelLines = 4;    // počet řádků adresy        
  var $labelWidth;        // šířka štítku [mm]         
  var $labelHeight;       // výška štítku [mm]        
  var $labelInCols;       // počet štítků ve sloupcích A4         
  var $labelInRows;       // počet štítků v řádcích A4
  var $lSpace = 2;        // vnitřní levý okraj [mm]
  var $rSpace = 2;        // vnitřní pravý okraj [mm]          
  var $lineHeight = 10;   // výška řádku;
  var $documentType;      // Typ dokumentu PDF
  var $yy = 10;           // Lokální pozice Y
  var $isHeader = false;  // Tisk záhlaví Header() dokumentu na každý list
  var $isFooter = false;  // Tisk patičky Footer() dokumentu na každý list
  var $reportTitle;       // Název tiskové sestavy  

  public function __construct( $registry ) 
  {
		$this->registry = $registry;

  }

  public function SetDocument($documentType, $fileName='', $orientation='P', $unit='mm', $format='A4')
  {   
    parent::FPDF($orientation, $unit, $format);
    
    global $config;
    global $deb;


    $this->AddFont('candara','','candara.php');
    $this->AddFont('candara','B','candarab.php');
    $this->AddFont('candara','I','candarai.php');
    $this->AddFont('candara','BI','candaraz.php');

    $this->AddFont('calibri','','calibri.php');
    $this->AddFont('calibri','B','calibrib.php');
    $this->AddFont('calibri','I','calibrii.php');
            
    if (isset($fileName) && ($fileName <> ''))
      $this->fileName = $fileName;
    else
      $this->fileName = 'document.pdf';    
        
    /*  
     *  $this->documentType 
     *  - invoice, label, postReceipt, sek, report
     */
        
    $this->setDocumentType( $documentType );
        
    $sTitle    = 'www.bijoux-maja.cz';
    $sSubject  = '';
    $sAuthor   = 'Petr Blažek';
    $sKeywords = '';
    $sCreator  = 'Create By PHP5.3.3 (PEAR)';
    
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
        $this->reportTitle = 'Katalog zboží';
                    break;
      case 'invoice':
        $this->SetFont('calibri','I',0);
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
      default:
        $this->_error('Unknown document type '.$documentType);
    }  // END switch
  }
  
  
  function Show()
  {
    global $deb;

    if ($this->fileName <> 'document.pdf')
      $this->Output($this->fileName);
    else
      $this->Output();
  } // END function Show
      
  function Header()
  {
      global $config;
      
      $yy = $this->tMargin;
      if (!$this->isHeader)
        return;
      
    switch ($this->documentType)
    {
                  case 'report':
        //Logo      
        $this->Image($config['dir_files'] . 'img/logoBijouxMaja.jpg',10,$yy,190);
        $this->SetY($yy + 20);
        //Title
        if (isset($this->reportTitle)){
          $this->SetFont('calibri','B',15);
          $this->Cell(30,10,$this->_utf2win($this->reportTitle),0,0,'L');
          //Line break
          $this->Ln(12);
        }
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
                  case 'invoice':
        //Legislativa
        $this->Text(15,$yy,$this->_utf2win($config['regDocument']));   
        $this->Text(15,$yy+3,$this->_utf2win($config['regDocumentNo']));
        break;   
                  case 'report':
        //Page number
        $this->SetY($yy);
        $this->Cell(0,10,$this->_utf2win($caption['pageNo']).' '.$this->PageNo(),0,0,'C');
        break;
    }
  }

  function NewInvoice($aInvoice)
  {      
    $this->pageNo = 0;
    $this->HeaderInvoice($aInvoice);
    $this->AddressInvoice($aInvoice); 
  } // END function NewInvoice

  function HeaderInvoice($aInvoice)
  {
    global $config, $caption;
    
    $this->AddFont('candara','','candara.php');
    $this->AddPage();
    $this->pageNo++;
    
    $this->_border();
        
    // Header
    $yy = $this->yy;
    $this->SetXY(12,$yy);
    $this->SetFontSize(16);
    $this->Write(10,$this->_utf2win($caption['textInvoice']));
    $this->SetXY(0,$yy);
    $this->Cell(198,10,$this->_utf2win($caption['docNo']).' '.$aInvoice['invoiceNo'],0,0,'R');
//    $this->Image('libraries/img/logoBijouxMaja.jpg', 10, 10,190,20,'jpeg');
    
    $this->SetXY(0,$yy + 7);
    $this->SetFontSize(8);
    $this->Cell(198,10,$this->_utf2win($caption['pageNo'].' '.$this->pageNo,85,10,100),0,0,'R');
    $this->Line(10,$yy + 15,200,$yy + 15);
    $this->SetY($yy + 15);
    
  } // END function HeaderInvoice

  function AddressInvoice($aInvoice)
  {
    global $config, $caption;
    
    // Addres - Customer
    $yy = $this->yy;
    $this->SetFontSize(8);
    $this->Text(120,$yy + 25,$this->_utf2win($caption['textCust']));
    $this->SetFontSize(10);
    $this->Text(120,$yy + 35,$this->_utf2win($aInvoice['deliveryAddress']['name']));
    $this->Text(120,$yy + 40,$this->_utf2win($aInvoice['deliveryAddress']['address']));
    $this->Text(120,$yy + 45,$this->_utf2win($aInvoice['deliveryAddress']['city']));
    $this->Text(120,$yy + 50,$aInvoice['deliveryAddress']['zipcode']);

    $this->SetFontSize(8);
    $this->Text(120,$yy + 65,$this->_utf2win($caption['email'] . ' : ' . $aInvoice['deliveryAddress']['email']));
    $this->Text(120,$yy + 69,$this->_utf2win($caption['Phone'] . ": " . $aInvoice['deliveryAddress']['phone']));
     
    // Addres - Company
    $this->SetFontSize(8);
    $this->Text(15,$yy + 25,$this->_utf2win($caption['textComp']));
    $this->SetFontSize(10);
    $this->Text(15,$yy + 35,$this->_utf2win($config['compName']));
    $this->Text(15,$yy + 40,$this->_utf2win($config['compAddress']));
    $this->Text(15,$yy + 45,$this->_utf2win($config['compCity']));
    $this->Text(15,$yy + 50,$config['compZip']);
    $this->SetFontSize(8);   
    $this->Text(15,$yy + 57,$this->_utf2win($caption['ico'].' : '.$config['compICO']));  
    $this->Text(15,$yy + 65,$this->_utf2win($caption['email']));
    $this->Text(15,$yy + 69,$this->_utf2win($caption['Phone']));
    $this->Text(15,$yy + 75,$this->_utf2win($caption['bankAcc']));
    $this->Text(15,$yy + 79,$this->_utf2win($caption['varSymb']));
    $this->Text(15,$yy + 83,$this->_utf2win($caption['conSymb']));
    
    $this->Text(45,$yy + 65,$config['eShopEmail']);   
    $this->Text(45,$yy + 69,$config['phoneUcto']);
    $this->Text(45,$yy + 75,$config['bankAccountNo']);
    $this->Text(45,$yy + 79,$aInvoice['orderNo']);
    $this->Text(45,$yy + 83,'0008');
    $this->Line(10,$yy + 85,200,$yy + 85);
    
    // Payment information
    $this->Text(120,$yy + 88,$this->_utf2win($caption['docDate']));   
    $this->Text(120,$yy + 92,$this->_utf2win($caption['dueDate']));
    $this->Text(120,$yy + 96,$this->_utf2win($caption['payMethod']));
    $this->Text(120,$yy + 100,$this->_utf2win($caption['OrderNo']));
    $this->Text(150,$yy + 88,$aInvoice['postingDate']);
    $this->Text(150,$yy + 92,$aInvoice['dueDate']);
    $this->Text(150,$yy + 96,$this->_utf2win($aInvoice['paymentMethod']));
    $this->Text(150,$yy + 100,$aInvoice['orderNo']);
    $this->Line(10,$yy + 102,200,$yy + 102);
    $this->SetY($yy + 102);
    
  } // END function AddressInvoice

  function AddressOrder($aOrder)
  {
    global $config, $caption;
    
    // Addres - Customer
    $yy = $this->yy;
    $this->SetFontSize(8);
    $this->Text(120,$yy + 25,$this->_utf2win($caption['textCust']));
    $this->SetFontSize(10);
    $this->Text(120,$yy + 35,$this->_utf2win($aOrder['deliveryAddress']['name']));
    $this->Text(120,$yy + 40,$this->_utf2win($aOrder['deliveryAddress']['address']));
    $this->Text(120,$yy + 45,$this->_utf2win($aOrder['deliveryAddress']['city']));
    $this->Text(120,$yy + 50,$aOrder['deliveryAddress']['zipcode']);

    $this->SetFontSize(8);
    $this->Text(120,$yy + 65,$this->_utf2win($caption['email'] . ' : ' . $aOrder['deliveryAddress']['email']));
    //$this->Text(120,$yy + 69,$this->_utf2win($caption['Phone'] . ": " . $aOrder['deliveryAddress']['phone']));
     
    // Addres - Company
    $this->SetFontSize(8);
    $this->Text(15,$yy + 25,$this->_utf2win($caption['textComp']));
    $this->SetFontSize(10);
    $this->Text(15,$yy + 35,$this->_utf2win($config['compName']));
    $this->Text(15,$yy + 40,$this->_utf2win($config['compAddress']));
    $this->Text(15,$yy + 45,$this->_utf2win($config['compCity']));
    $this->Text(15,$yy + 50,$config['compZip']);
    $this->SetFontSize(8);   
    $this->Text(15,$yy + 57,$this->_utf2win($caption['ico'].' : '.$config['compICO']));  
    $this->Text(15,$yy + 65,$this->_utf2win($caption['email']));
    $this->Text(15,$yy + 69,$this->_utf2win($caption['Phone']));
    $this->Text(15,$yy + 75,$this->_utf2win($caption['bankAcc']));
    $this->Text(15,$yy + 79,$this->_utf2win($caption['varSymb']));
    $this->Text(15,$yy + 83,$this->_utf2win($caption['conSymb']));
    
    $this->Text(45,$yy + 65,$config['eShopEmail']);   
    $this->Text(45,$yy + 69,$config['phoneUcto']);
    $this->Text(45,$yy + 75,$config['bankAccountNo']);
    $this->Text(45,$yy + 79,$aOrder['orderNo']);
    $this->Text(45,$yy + 83,'0008');
    $this->Line(10,$yy + 85,200,$yy + 85);
    
    // Payment information
    $this->Text(90,$yy + 90,$this->_utf2win($caption['docDate']));   
    $this->Text(90,$yy + 94,$this->_utf2win($caption['DeliveryMethod']));
    $this->Text(90,$yy + 98,$this->_utf2win($caption['payMethod']));
    
    $this->Text(120,$yy + 90,$aOrder['orderDate']);
    $this->Text(120,$yy + 94,$this->_utf2win($aOrder['shippingMethod']));
    $this->Text(120,$yy + 98,$this->_utf2win($aOrder['paymentMethod']));
    $this->Line(10,$yy + 102,200,$yy + 102);
    $this->SetY($yy + 102);
    
  } // END function AddressOrder

  function ItemHeader()
  {
    global $caption, $config;
    
    $yy = $this->GetY() - 2;
    $this->SetXY(15,$yy);
    $this->SetFontSize(7);   
    $this->Cell(20,10,$this->_utf2win($caption['code']),0,0,'L');
    $this->Cell(70,10,$this->_utf2win($caption['FirstLast_name']),0,0,'L');
    $this->Cell(30,10,$this->_utf2win($caption['Price'].' ['.$config['currency_symbol'].']'),0,0,'R');
    $this->Cell(30,10,$this->_utf2win($caption['Quantity']),0,0,'R');
    $this->Cell(30,10,$this->_utf2win($caption['Summary'].' ['.$config['currency_symbol'].']'),0,0,'R');   
    $yy += 8;
    $this->Line(10,$yy,200,$yy);
    $this->SetY($yy);
  } // END function ItemHeader


  function ItemLine($aLine)
  {  
    if ($this->CheckEndPage()){
      $this->ItemHeader();   
    }
    $yy = $this->GetY();
    $this->SetXY(15,$yy);
    $this->SetFontSize(8);   
    $this->Cell(20,10,$aLine['code'],0,0,'L');
    $this->Cell(70,10,$this->_utf2win($aLine['name']),0,0,'L');
    $this->Cell(30,10,$aLine['price'],0,0,'R');
    $this->Cell(30,10,$aLine['qty'],0,0,'R');
    $this->Cell(30,10,$aLine['cost'],0,0,'R');   
    $yy += 4;
    $this->SetY($yy);
  } // END function ItemLine

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


  function NewOrder($aOrder)
  {      
    $this->pageNo = 0;
    $this->HeaderOrder($aOrder);
    $this->AddressOrder($aOrder); 
  } // END function NewOrder

  function HeaderOrder($aOrder)
  {
    global $config, $caption;
    
    $this->AddFont('candara','','candara.php');
    $this->AddPage();
    $this->pageNo++;
    
    $this->_border();
        
    // Header
    $yy = $this->yy;
    $this->SetXY(12,$yy);
    $this->SetFontSize(16);
    $this->Write(10,$this->_utf2win($caption['textOrder']));
    $this->SetXY(0,$yy);
    $this->Cell(198,10,$this->_utf2win($caption['docNo']).' '.$aOrder['orderNo'],0,0,'R');
//    $this->Image('libraries/img/logoBijouxMaja.jpg', 10, 10,190,20,'jpeg');
    
    $this->SetXY(0,$yy + 7);
    $this->SetFontSize(8);
    $this->Cell(198,10,$this->_utf2win($caption['pageNo'].' '.$this->pageNo,85,10,100),0,0,'R');
    $this->Line(10,$yy + 15,200,$yy + 15);
    $this->SetY($yy + 15);
    
  } // END function HeaderInvoice

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
  
  // Product Catalog with (picture,name,description,price) 
  function ProductKatalog($header,$data)
  {
    global $oFoto;
    global $deb;
      
    //Column widths
    $w=array(30,15,125,20);
    //Header      
    $this->SetFillColor(255,255,153);
    $this->SetFontSize(9);
    $this->Cell($w[0],7,'',1,0,'L',true);
    $this->Cell($w[1],7,$this->_utf2win($header[0]),1,0,'L',true);
    $this->Cell($w[2],7,$this->_utf2win($header[1]),1,0,'L',true);
    $this->Cell($w[3],7,$this->_utf2win($header[2]),1,0,'R',true);
    $this->Ln();
    //Data
    foreach($data as $row)
    {
      if($this->y+27 > $this->PageBreakTrigger){
        // TransHeader
        $this->AddPage();      
        $this->SetFillColor(255,255,153);
        $this->SetFontSize(9);         
        $this->Cell($w[0],7,'',1,0,'L',true);
        $this->Cell($w[1],7,$this->_utf2win($header[0]),1,0,'L',true);
        $this->Cell($w[2],7,$this->_utf2win($header[1]),1,0,'L',true);
        $this->Cell($w[3],7,$this->_utf2win($header[2]),1,0,'R',true);
        $this->Ln();
      }
        
      $this->SetFontSize(9);         
      $this->Cell($w[0],4,'','LRT',0,'C');
      $this->Cell($w[1],4,$this->_utf2win($row[0]),'LRT');
      $this->Cell($w[2],4,$this->_utf2win($row[1]),'LRT');
      $this->Cell($w[3],4,$this->_utf2win($row[3]),'LRT',0,'R');
      $this->Ln();
      $this->SetFontSize(7);
      $this->Cell($w[0],4,'','LR');
      $this->Cell($w[1],4,'','LR');
      $this->Cell($w[2],4,$this->_utf2win($row[2]),'LR');
      $this->Cell($w[3],4,'','LR');
      $this->Ln();
      $this->Cell($w[0],12,'','LRB');
      $this->Cell($w[1],12,'','LRB');
      $this->Cell($w[2],12,'','LRB');
      $this->Cell($w[3],12,'','LRB');
      $this->Ln();
      
      $pomer = $oFoto->throwImgSize($row[4],'width') / $oFoto->throwImgSize($row[4],'height');
      $imgH = 18;
      $imgW = $imgH * $pomer;

      if ($imgW > 28)
        $imgW = 28;
      $this->Image($row[4],$this->GetX() + 1,$this->GetY()-19,$imgW,$imgH);
    }
    //Closure line
    $this->Cell(array_sum($w),0,'','T');
  }
  
  // ---------------------------------------------------------------------------

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


  function tPrice( $fPrice ){
    return sprintf( '%01.2f', $fPrice );
  } // end function tPrice

} // END class PdfDocument


?>