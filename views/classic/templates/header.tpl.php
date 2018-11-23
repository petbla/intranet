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
      <a href="www.petbla.cz"><img src="images/logo.png" border="0" alt="www.petbla.cz" title="www.petbla.cz"></a>
      <nav>
        <ul>
          <li><a href="#agendy">Agendy</a></li>
          <li><a href="#tour">Turistika</a></li>
          <li><a href="#application">Aplikace</a></li>
          <li><a href="#forms">Formuláře</a></li>
          <li><a href="#ebooks">eBooks</a></li>
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
        <section id="linksession">
          <header>
          </header>
          <p>
          {dateText}
          <ul>
            <li><a href=''>APošta</a></li>
            <li><a href=''>BAdmin MAILU</a></li>
            <li><a href=''>CFTP/FTPS</a></li>
            <li><a href=''>DphpMyAdmin</a></li>
            <li><a href=''>EMySQL</a></li>
            <li><a href=''>FAministrace</a></li>
            <li><a href=''>GPošta</a></li>
            <li><a href=''>HAdmin MAILU</a></li>
            <li><a href=''>1_FTP/FTPS</a></li>
            <li><a href=''>2_phpMyAdmin</a></li>
            <li><a href=''>3_MySQL</a></li>
            <li><a href=''>4_Aministrace</a></li>
            <li><a href=''>5_Pošta</a></li>
            <li><a href=''>Admin MAILU</a></li>
            <li><a href=''>FTP/FTPS</a></li>
            <li><a href=''>phpMyAdmin</a></li>
            <li><a href=''>MySQL</a></li>
            <li><a href=''>Aministrace</a></li>
            <li><a href=''>Pošta</a></li>
            <li><a href=''>Admin MAILU</a></li>
            <li><a href=''>FTP/FTPS</a></li>
            <li><a href=''>phpMyAdmin</a></li>
            <li><a href=''>MySQL</a></li>
            <li><a href=''>5_Pošta</a></li>
            <li><a href=''>Admin MAILU</a></li>
            <li><a href=''>FTP/FTPS</a></li>
            <li><a href=''>phpMyAdmin</a></li>
            <li><a href=''>MySQL</a></li>
            <li><a href=''>Aministrace</a></li>
            <li><a href=''>Pošta</a></li>
            <li><a href=''>Admin MAILU</a></li>
            <li><a href=''>FTP/FTPS</a></li>
            <li><a href=''>phpMyAdmin</a></li>
            <li><a href=''>MySQL</a></li>
            <li><a href=''>Aministrace</a></li>
            <li><a href=''>5_Pošta</a></li>
            <li><a href=''>Admin MAILU</a></li>
            <li><a href=''>FTP/FTPS</a></li>
            <li><a href=''>phpMyAdmin</a></li>
            <li><a href=''>MySQL</a></li>
            <li><a href=''>Aministrace</a></li>
            <li><a href=''>Pošta</a></li>
            <li><a href=''>Admin MAILU</a></li>
            <li><a href=''>FTP/FTPS</a></li>
            <li><a href=''>phpMyAdmin</a></li>
            <li><a href=''>MySQL</a></li>
            <li><a href=''>Aministrace</a></li>
            <li><a href=''>Aministrace</a></li>
          </ul>
        </section>
        
        <section id="tablelist">
          <header>          
          </header>
          <table>
             <tr>
                <th>Položka</th>
                <th>Cena</th>
                <th>Položka</th>
                <th>Cena</th>
                <th>Počet</th>
                <th>Položka</th>
                <th>Cena</th>
                <th>Počet</th>
                <th>Položka</th>
                <th>Cena</th>
                <th>Počet</th>
                <th>Položka</th>
                <th>Počet</th>
                <th>Celkem</th>
             </tr>
             <tr>
                <td>Šálek na kávu</td>
                <td>100 Kč</td>
                <td>Šálek na kávu</td>
                <td>100 Kč</td>
                <td>5</td>
                <td>500 Kč</td>
                <td>Šálek na kávu</td>
                <td>100 Kč</td>
                <td>5</td>
                <td>500 Kč</td>
                <td>5</td>
                <td>500 Kč</td>
                <td>Šálek na kávu</td>
                <td>100 Kč</td>
                <td>5</td>
                <td>500 Kč</td>
             </tr>
             <tr>
                <td>Tričko</td>
                <td>200 Kč</td>
                <td>1 000 Kč</td>
                <td>Tričko</td>
                <td>200 Kč</td>
                <td>5</td>
                <td>5</td>
                <td>1 000 Kč</td>
                <td>Tričko</td>
                <td>200 Kč</td>
                <td>5</td>
                <td>1 000 Kč</td>
             </tr>
             <tr>
                <td>Červená sešívačka</td>
                <td>90 Kč</td>
                <td>1 000 Kč</td>
                <td>Tričko</td>
                <td>200 Kč</td>
                <td>5</td>
                <td>4</td>
                <td>360 Kč</td>
             </tr>
             <tr>
                <td colspan="3">Mezisoučet</td>
                <td>1 860 Kč</td>
             </tr>
             <tr>
                <td colspan="3">Doprava</td>
                <td>120 Kč</td>
             </tr>
             <tr>
                <td colspan="3">Celkem</td>
                <td>1 980 Kč</td>
             </tr>
          </table>
          <br />
          <br />
          <br />
          <br />
          <br />
          <br />
          <br />
          <br />
          <br />
          <br />
          <br />
          <br />
          <br />
          <br />
          <br />
          <br />
          <br />
          <br />
          <br />
          <br />
          <br />
          <br />
          <br />
          <br />
          <br />
          <br />
          <br />
          <br />
          <br />
          <br />
          <br />
        </section>
        
      </section>
    </div>    
    
    <footer id="page_footer" role="contentinfo">
      <p>&copy; petbla 2012</p>
    </footer>
      
  </body>
</html>

