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
<div id="wrapper">
	<div id="header">						
    <div id="barmenu">
      <p>{dateText}</p>
    	<ul>
        <li class="item">
    			<a rel="nofollow" href="" title="HOME"><span><strong>Úvodní strana</strong></span></a>
    		</li>
    		<li class="item">
    			<a rel="nofollow" href="about" title="{lbl_aboutTitle}"><span><strong>qw_barMenu1</strong></span></a>
    		</li>
    		<li class="item">
    			<a rel="nofollow" href="contact" title="{lbl_contactTitle}"><span><strong>qw_barMenu2</strong></span></a>
    		</li>
    	</ul>
    </div>
	</div>
	<div id="main">
    <div id="column">
      {categories}
      <form action="{actionSearch}" method="post">
        <ul>
    		  <li class="item">
  			    <input type="text" id="search_text" name="search_text" />
  			  </li>
    		  <li class="item">
            <input type="submit" id="search" class="action" name="search" value=" {lbl_search} " />
          </li>
        </ul>
        <div class="foot"></div>
  		</form>     
    </div>
  	<div id="content">  