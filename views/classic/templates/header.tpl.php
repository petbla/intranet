<!DOCTYPE html>
<html lang="cs-CZ">
  <head>
    <meta charset="utf-8" />
    <title>{cfg_sitename}</title>
    <link rel="stylesheet" type="text/css" href="views/classic/styles/default.css"> 
    <script type="text/javascript" src="js/functions.js"></script>
  </head>

  <body>    
    <header id="page_header">
      <p>
        {dateText}<br/>
        <span id="UserName">{UserName}</span>
      </p>
      <img src="views/classic/images/logo.png" border="0" alt="" title="{cfg_compName}">
      <h1>{cfg_compName}</h1>
      <h2>{lbl_DmsLabel}</h2>
      <nav>
        <ul>
          <li><a href="index.php?page=document/list">{lbl_Documents}</a></li>
          {newsBarMenuItem}
          {archiveBarMenuItem}
          {contactBarMenuItem}
          {calendarBarMenuItem}
          {adminBarMenuItem}
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

        