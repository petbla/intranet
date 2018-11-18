<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="cs" lang="cs"> 
<head> 
	<base href="{headbaseURL}" />
	<title>{headtitle}</title> 
	<meta http-equiv="content-type" content="text/html; charset=utf-8" /> 
	<meta name="description" content="{metadescription}" /> 
	<meta name="keywords" content="{metakeywords}" /> 
	<link rel="stylesheet" type="text/css" href="views/classic/default.css" />
	<!--[if IE]>
    <link rel="stylesheet" type="text/css" href="views/classic/IE.css" />
  <![endif]-->
  <script type="text/javascript" src="js/standard.js"></script>
  <script type="text/javascript" src="js/adminGlobals.js"></script>
  <script type="text/javascript" src="js/checkForm.js"> </script>
</head> 
<body>
<script type="text/javascript"> 
<!-- 
document.write("<img src=\"Statistic.php?screenres=" + screen.width + "x" + screen.height + "&colordepth=" + screen.colorDepth + "\" width=\"1\" height=\"1\" alt=\"\" />");
// --> 
</script>
<div id="wrapper">
	<div id="header">						
    <div id="barmenu">
      <p>{dateText}</p>
    	<ul>
        <li class="item">
    			<a rel="nofollow" href="" title="HOME"><span><strong>Úvodní strana</strong></span></a>
    		</li>
    		<li class="item">
    			<a rel="nofollow" href="about" title="{lbl_aboutTitle}"><span><strong>{lbl_about}</strong></span></a>
    		</li>
    		<li class="item">
    			<a rel="nofollow" href="contact" title="{lbl_contactTitle}"><span><strong>{lbl_contact}</strong></span></a>
    		</li>
    		<li class="item">
    			<a rel="nofollow" href="help" title="{lbl_helpTitle}"><span><strong>{lbl_help}</strong></span></a>
    		</li>
        {accountmenu}
    		{advance}
    	</ul>
    </div>
    <div id="panel">
      {basket}
    </div>
	</div>
	<div id="main">
    <div id="column">
      {AKCE}
      {categories}
      <script type="text/javascript">
      <!--
        var aCheckFormContact =  Array(
          Array( "news_email", "email" )
        );
        var cfLangNoWord = "{lbl_cf_no_word}";
        var cfLangMail = "{lbl_cf_mail}";
      //-->
      </script>
      <form action="{actionSearch}" method="post">
        <h1 class="title">{lbl_searchLabel}</h1>
        <ul>
    		  <li class="item">
  			    <input type="text" id="search_text" name="search_text" />
  			  </li>
    		  <li class="item">
            <input type="submit" id="search" class="action" name="search" value=" {lbl_search} " />
          </li>
        </ul>
        <h1 class="title">{lbl_ItemsOnPage}</h1>
        <ul>
    		  <li class="item">
          	<!-- START showItems -->
            <a href="{headbaseURL}/products?showItems={showItem_items}" class="{showItem_class}">{showItem_items}</a>
          	<!-- END showItems -->
  			  </li>
        </ul>
        <div class="foot"></div>
  		</form>
      <form action="news" method="post" onsubmit="return checkForm( this, aCheckFormContact );">
        <h1 class="first">{lbl_SendNews}</h1>
        <ul>
    		  <li class="item">
  			    <b>{lbl_YourEmail}:</b>
  			  </li>
    		  <li class="item">
            <input type="text" id="news_email" name="news_email" />
  			  </li>
          <li class="item">
            <input type="submit" id="singUp" class="action" name="news" value=" {lbl_SingUp} " />
            <input type="submit" id="singOff" class="action" name="news" value=" {lbl_SingOff} " />
          </li>
        </ul>
        <div class="foot"></div>
  		</form>
      {loginform}
      <p style="text-align:center;">
        <a href="http://www.toplist.cz/">
        <script type="text/javascript">
          <!--
          document.write('<img src="http://toplist.cz/count.asp?id=1095414&amp;logo=mc&amp;http='+escape(document.referrer)+'&amp;t='+escape(document.title)+
          '&amp;wi='+escape(window.screen.width)+'&amp;he='+escape(window.screen.height)+'&amp;cd='+escape(window.screen.colorDepth)+'" width="88" height="60" border=0 alt="TOPlist" />'); 
          //-->
        </script>
        </a>
        <noscript>
          <img src="http://toplist.cz/count.asp?id=1095414&amp;logo=mc" alt="TOPlist" width="88" height="60" />
        </noscript> 
      </p>
      
    </div>
  	<div id="content">  