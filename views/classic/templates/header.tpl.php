<!DOCTYPE html>
<html lang="cs-CZ">
  <head>
    <meta charset="utf-8" />
    <title>{cfg_sitename}</title>
    <link rel="stylesheet" type="text/css" href="views/classic/styles/default.css"> 
    <link rel="stylesheet" type="text/css" href="views/classic/styles/slideshow.css"> 
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/onBeforeScripts.js"></script>
    <style>
      .mySlides {display:none}
    </style>
  </head>

  <body>    
    <header id="page_header">
      <p>
        {dateText}
        <br/>
        -------------------------------------------------<br>
        {logininfo}<br>
        {loginform}
        <!--
        // TODO: Tento kód zpomalil systém
        <br/>
        <a href="http://www.ikal.cz/" id="ikal-jmena">kalendář jmen</a>
        <script type="text/javascript" src="https://www.ikal.cz/widget/kalendar-jmen.js.php"></script>
        <script type="text/javascript">
          iKAL_JMENA.init('ctext: 337AB7, htext: 14, showcount: 3');
          iKAL_JMENA.show();
        </script>
        -->
      </p>
      <img src="views/classic/images/logo.png" border="0" alt="" title="{cfg_compName}" class="imageLogo">
      <h1>{lbl_DmsLabel} - {compName}</h1>
      <nav>
        <ul>
          <li><a href="index.php?page=todo/inbox">{lbl_Todos}</a></li>
          <li><a href="index.php?page=document/list">{lbl_Documents}</a></li>
          <li><a href="index.php?page=agenda/type/list">{lbl_Agenda}</a></li>
          <li><a href="index.php?page=zob/electionperiod">{lbl_ZOB}</a></li>
          {contactBarMenuItem}
          {calendarBarMenuItem}
          {adminBarMenuItem}
        </ul>
      </nav>
    </header>
    
    <div id="envelope">
      <section id="panel_left">             
        <section id="navigate" nav role="navigation">
          {search}
          <nav>
          {categories}          
          </nav>
        </section>
        
      </section>
      <section id="content" role="main">      

        