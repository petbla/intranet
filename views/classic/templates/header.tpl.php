<!DOCTYPE html>
<html lang="cs-CZ">
  <head>
    <meta charset="utf-8" />
    <title>{cfg_sitename}</title>
    <link rel="stylesheet" type="text/css" href="views/classic/styles/default.css"> 
    <link rel="stylesheet" type="text/css" href="views/classic/styles/slideshow.css"> 
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script> 
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
        -------------------------------------------------
        <br/>
        <a href="http://www.ikal.cz/" id="ikal-jmena">kalendář jmen</a>
        <script type="text/javascript" src="https://www.ikal.cz/widget/kalendar-jmen.js.php"></script>
        <script type="text/javascript">
          iKAL_JMENA.init('ctext: 337AB7, htext: 14, showcount: 3');
          iKAL_JMENA.show();
        </script>
      </p>
      <img src="views/classic/images/logo.png" border="0" alt="" title="{cfg_compName}">
      <h1>{compName}</h1>
      <h2>{lbl_DmsLabel}</h2>
      <nav>
        <ul>
          <li><a href="index.php?page=document/listTodo">{lbl_Todos}</a></li>
          <li><a href="index.php?page=document/listTodoClose">{lbl_RemindClosed}</a></li>
          <li><a href="index.php?page=document/list">{lbl_Documents}</a></li>
          <li><a href="index.php?page=agenda/type/list">{lbl_Agenda}</a></li>
          {newsBarMenuItem}
          {archiveBarMenuItem}
          {contactBarMenuItem}
          {calendarBarMenuItem}
          {adminBarMenuItem}
          {portalBarMenuItem}
        </ul>
      </nav>
    </header>
    
    <div id="envelope">
      <section id="panel_left">      
        <section id="login">
          <form action="/prihlaseni" method="post">
            <fieldset>
              <legend>{lbl_SingUp}</legend>
              <ol>
                <li>
                  <label>Login</label>
                  <input id="username" type="text" name="username" autocomplete="on">
                </li>
                <li>
                  <label>{lbl_Password}</label>
                  <input id="psw" type="password" name="psw" value="" autocomplete="off"/>
                </li>
                <li><input type="submit" value="{lbl_SingUp}"></li>
              </ol>
            </fieldset>
          </form>
        </section>
        
        <section id="navigate" nav role="navigation">
          <nav>
          {categories}
          {loginform}
          </nav>
        </section>
        
      </section>
      <section id="content" role="main">      

        