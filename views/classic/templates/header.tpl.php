<!DOCTYPE html>
<html lang="cs-CZ">
  <head>
    <meta charset="utf-8" />
    <title>PETBLA web</title>
    <link rel="stylesheet" type="text/css" href="views/classic/styles/default.css"> 
  </head>

  <body>
    <header id="page_header">
      <p>
        {dateText}<br/>
        <span id="UserName">{UserName}</span>
      </p>
      <img src="views/classic/images/logo.png" border="0" alt="" title="Obec Mistřice">
      <h1>{cfg_compName}</h1>
      <h2>{lbl_DmsLabel}</h2>
      <nav>
        <ul>
          <li><a href="index.php?page=document/list">Dokumenty</a></li>
          <li><a href="index.php?page=news/list">Novinky</a></li>
          <li><a href="index.php?page=archive/list">Archív</a></li>
          <li><a href="index.php?page=contact/list">Kontakty</a></li>
          <li><a href="https://teamup.com/ksx5ivfw8yrnn6gbqy">Kalendář</a></li>
          <li><a href="index.php?page=admin">Administrace</a></li>
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
          {loginform}
          </nav>
        </section>
        
      </section>
      <section id="content" role="main">      

        