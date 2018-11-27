<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml"  lang="cs-CZ">
  <head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="views/classic/styles/default.css" type="text/css"> 
    <link rel="stylesheet" href="views/classic/styles/print.css" type="text/css" media="print">
    <title>PETBLA web</title>
    <script type="text/javascript" charset="utf-8" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="js/html5.js"></script>
    <script type="text/javascript" charset="utf-8" src="js/jquery.corner.js"></script>
    
    <!--[if (gte IE 5.5)&(lte IE 8)]>
      <script type="text/javascript" 
              src="js/DOMAssistantCompressed-2.8.js"></script>
      <script type="text/javascript" 
              src="js/ie-css3.js"></script>
    <![endif]-->
    <!--[if lte IE 7]>
      <script type="text/javascript" src="print.js"></script>
    <![endif]-->
    <!--[if IE]>
      <style>
        fieldset legend{margin-top: -10px }
      </style>
    <![endif]-->
  </head>

  <body>
    <header id="page_header">
      <p>{dateText}</p>
      <img src="views/classic/images/logo.png" border="0" alt="" title="Obec Mistřice">
      <h1>{cfg_compName}</h1>
      <h2>{lbl_DmsLabel}</h2>
      <nav>
        <ul>
          <li><a href="index.php?page=document/list">Dokumenty</a></li>
          <li><a href="index.php?page=news/list">Novinky</a></li>
          <li><a href="index.php?page=archive/list">Archvív</a></li>
          <li><a href="index.php?page=contact/list">Kontakty</a></li>
          <li><a href="https://teamup.com/ksx5ivfw8yrnn6gbqy">Kalendář</a></li>
        </ul>
      </nav>
    </header>
    
    <div id="envelope">
      <section id="panel_left">      
        <section id="login">
          <form action="/prihlaseni" method="post">
            <fieldset>
              <legend>Přihlášení</legend>
              <ol>
                <li>
                  <label for="username">Login</label>
                  <input id="username" type="text" name="username"
                   autocomplete="on">
                </li>
                <li>
                  <label for="heslo">Heslo</label>
                  <input id="psw" type="password"
                   name="psw" value="" autocomplete="off"/>
                </li>
                <li><input type="submit" value="Přihlásit"></li>
              </ol>
            </fieldset>
          </form>
        </section>
        
        <section id="navigate" nav role="navigation">
          <nav>
          {categories}
          </nav>
        </section>
        
      </section>
      <section id="content" role="main">      

        