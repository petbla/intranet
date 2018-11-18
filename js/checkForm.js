/**
 * Kontrolní funkce správných polí formuláře
 *
 * @version 1.0
 * @author Petr Blažek
 */

var cfBorderColor   = '#ABADB3';
var cfWarningColor  = '#ff0000';
var bAllGood 		= true;
var sAllWarnings = '';
var oFirstWrong;
var bIsWarnings = false;

var	regStr = /\s/gi;
var regEmail = /^[a-z0-9_.-]+([_\\.-][a-z0-9]+)*@([a-z0-9_\.-]+([\.][a-z]{2,4}))+$/i;


function fieldOperations( obj, bCheck, sInfo ){
	if( bCheck === true ) {
    if( obj.type != 'hidden' )
      obj.style.borderColor = cfBorderColor;
	}
	else {
    if( sInfo )
  		sAllWarnings += sInfo +'\n';
		if( obj.type != 'hidden' ){
  		obj.style.borderColor = cfWarningColor;
      if( bIsWarnings == false )
        oFirstWrong = obj;
		}
		bIsWarnings = true;
		return false;
	}
  return true;
} // end function fieldOperations


function checkText( obj, sInfo ) {
	checkT = obj.value.replace( regStr, "" );
  var bCheck = true;
	if( checkT == '' )
    bCheck = false;
  
  return fieldOperations( obj, bCheck, sInfo );
} // end function checkText


function checkEmail( obj ) {
	var sEmail = obj.value;
  var bCheck = true;
	if ( sEmail.search( regEmail ) == -1 )
    bCheck = false;
  return fieldOperations( obj, bCheck, cfLangMail );
} // end function checkEmail

function checkArray( obj, sInfo ) {
	var str = obj.value;
  var bCheck = true;
  if ( str.indexOf(",") == -1 )
    bCheck = false;
  return fieldOperations( obj, bCheck, sInfo );
} // end function checkArray


function checkForm( form, aElements ) 
{
  sAllWarnings 	= '';
  bIsWarnings 	= false;
  bAllGood			= true;
  oFirstWrong 	= '';
  var obj; 
  var elementType; 

  for( i in aElements ) {
    obj = form[aElements[i][0]];
    if( aElements[i][1] )
      elementType = aElements[i][1];
    else
      elementType = false;
    
    if( !elementType || elementType == 'simple' ) {
      bAllGood = checkText( obj, aElements[i][2] );
		}
		else if( elementType == 'email' ) {
			bAllGood = checkEmail( obj );
		}
		else if( elementType == 'array') {
      bAllGood = checkArray( obj, aElements[i][2] )
    }
  }  
  
  if( bIsWarnings == true ) {
		sAllWarnings = cfLangNoWord + '\n' + sAllWarnings;
    alert ( sAllWarnings );
    if( oFirstWrong )
      oFirstWrong.focus();
    return false;
	}

return true;
} // end function checkForm

