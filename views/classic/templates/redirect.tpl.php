<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="cs" lang="cs"> 
  <head> 
  	<base href="{headbaseURL}" />
  	<title>{lbl_waitIsRedirecting}...</title>
    <meta http-equiv="refresh" content="0.1; url={redirectURL}" />
  	<meta http-equiv="content-type" content="text/html; charset=utf-8" /> 
  	<link rel="stylesheet" type="text/css" href="views/classic/default.css" />
    <script type="text/javascript" src="js/standard.js"></script>
  </head>
  <body>
    <div id="message">
      <h1>{header}</h1>
      <p>{message}</p>
      <p>
        <a href="{url}">{lbl_RedirectHandle}.</a>
      </p>
    </div>  
  </body>
</html>