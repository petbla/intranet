<div id="admin">
    <table class="edit-card">
        <tr>
            <td colspan="3" class="title">
                ADMIN menu (config.php)
            </td>
        </tr>
        <tr>
            <td class="name">
                Verze
            </td>
            <td class="value">
                <span>
                    {cfg_sourceVersion}
                </span>
            </td>
            <td class="action">
            </td>
        </tr>
        <tr>
            <td class="name">
                Výchozí oddělovač
            </td>
            <td class="value">
                <span>
                    {s_Separator}
                </span>
            </td>
            <td class="action">
            </td>
        </tr>
        <tr>
            <td class="name">
            Přihlášený uživatel:
            </td>
            <td class="value">
                <span>
                    {s_CurrentUser}                    
                </span>
            </td>
            <td class="action">
            </td>
        </tr>
    </table>
    <table class="edit-card">
        <tr>
            <td colspan="3" class="title">
                DMS (config.php)
            </td>
        </tr>
        <tr>
            <td class="name">
                Synchronizace složky při otevření
            </td>
            <td class="value">
                <span>
                    {s_synchroFolderonOpen}
                </span>
            </td>
            <td class="action">
            </td>
        </tr>
    </table>
    <table class="edit-card">
        <tr>
            <td colspan="3" class="title">
                Databáze / Portál 
            </td>
        </tr>
        <tr>
            <td class="name">
                Server
            </td>
            <td class="value">
                <span>{cfg_db_host}</span>
            </td>
            <td class="action">
            </td>
        </tr>
        <tr>
            <td class="name">
                Databáze
            </td>
            <td class="value">
                <span>{cfg_db_name}</span>
            </td>
            <td class="action">
            </td>
        </tr>
        <tr>
            <td class="name">
                Prefix tabulek
            </td>
            <td class="value">
                <span>{cfg_dbPrefix}</span>
            </td>
            <td class="action">
            </td>
        </tr>
        <tr>
            <td class="name">
                Název portálu
            </td>
            <td class="value">
                <span>{cfg_sitename}</span>
            </td>
            <td class="action">
            </td>
        </tr>
        <tr>
            <td class="name">
                Site URL
            </td>
            <td class="value">
                <span>{cfg_siteurl}</span>
            </td>
            <td class="action">
            </td>
        </tr>
        <tr>
            <td class="name">
                Webroot
            </td>
            <td class="value">
                <input type="Text" name="Webroot" class="value" value="{cfg_webroot}" pkID="1" table="source" onchange="wsUpdate(this);">
            </td>
            <td class="action">
                <img id="fld_webroot" src='views/classic/images/icon/trafficUnknown.png' data-dms-server="{cfg_webroot}"/>
            </td>
        </tr>
        <tr>
            <td class="name">
                Fileroot 
            </td>
            <td class="value">
                <input type="Text" name="Fileroot" class="value" value="{cfg_fileroot}" pkID="1" table="source" onchange="wsUpdate(this);">
            </td>
            <td class="action">
            </td>
        </tr>
        <tr>
            <td class="name">
                ZOBroot 
            </td>
            <td class="value">
                <input type="Text" name="Zobroot" class="value" value="{cfg_zobroot}" pkID="1" table="source" onchange="wsUpdate(this);">
            </td>
            <td class="action">
            </td>
        </tr>
    </table>
    <table class="edit-card">
        <tr>
            <td colspan="3" class="title">
                Firemní informace
            </td>
        </tr>
        <tr>
            <td class="name">
                Název
            </td>
            <td class="value">
                <input type="Text" name="Name" class="col_fullname" value="{cfg_compName}" pkID="1" table="source" onchange="wsUpdate(this);">
            </td>
            <td class="action">
            </td>
        </tr>
        <tr>
            <td class="name">
                <span>Adresa - ulice,číslo</span>
            </td>
            <td class="value">
                <input type="Text" name="Address" class="value" value="{cfg_compAddress}" pkID="1" table="source" onchange="wsUpdate(this);">
            </td>
            <td class="action">
            </td>
        </tr>
        <tr>
            <td class="name">
                <span>Město</span>
            </td>
            <td class="value">
                <input type="Text" name="City" class="value" value="{cfg_compCity}" pkID="1" table="source" onchange="wsUpdate(this);"><br>
            </td>
            <td class="action">
            </td>
        </tr>
        <tr>
            <td class="name">
                <span>PSČ</span>
            </td>
            <td class="value">
                <input type="Text" name="Zip" class="value" value="{cfg_compZip}" pkID="1" table="source" onchange="wsUpdate(this);">
            </td>
            <td class="action">
            </td>
        </tr>
        <tr>
            <td class="name">
                <span>IČO</span>
            </td>
            <td class="value">
                <input type="Text" name="ICO" class="value" value="{cfg_compICO}" pkID="1" table="source" onchange="wsUpdate(this);">
            </td>
            <td class="action">
            </td>
        </tr>
        <tr>
            <td class="name">
                <span>E-mail</span>
            </td>
            <td class="value">
                <input type="Text" name="Email" class="value" value="{cfg_compEmail}" pkID="1" table="source" onchange="wsUpdate(this);">
            </td>
            <td class="action">
            </td>
        </tr>
        <tr>
            <td class="name">
                <span>Telefon</span>
            </td>
            <td class="value">
                <input type="Text" name="Phone" class="value" value="{cfg_compPhone}" pkID="1" table="source" onchange="wsUpdate(this);">
            </td>
            <td class="action">
            </td>
        </tr>
        <tr>
            <td class="name">
                <span>Web - název</span>
            </td>
            <td class="value">
                <input type="Text" name="Web" class="value" value="{cfg_websiteName}" pkID="1" table="source" onchange="wsUpdate(this);">
            </td>
            <td class="action">
            </td>
        </tr>
        <tr>
            <td class="name">
                <span>Web - link</span>
            </td>
            <td class="value">
                <input type="Text" name="Website" class="value" value="{cfg_website}" pkID="1" table="source" onchange="wsUpdate(this);">
            </td>
            <td class="action">
            </td>
        </tr>
        <tr>
            <td class="name">
                <span>Facebook</span>
            </td>
            <td class="value">
                <input type="Text" name="Facebook" class="value" value="{cfg_facebook}" pkID="1" table="source" onchange="wsUpdate(this);">
            </td>
            <td class="action">
            </td>
        </tr>
        <tr>
            <td class="name">
                <span>Datová schránka </span>
            </td>
            <td class="value">
                <input type="Text" name="DataBox" class="value" value="{cfg_compDataBox}" pkID="1" table="source" onchange="wsUpdate(this);">
            </td>
            <td class="action">
            </td>
        </tr>
    </table>
    <table class="edit-card">
        <tr>
            <td colspan="6" class="title">
                Portal
            </td>
        </tr>
        <tr>
            <th class="caption">Name</th>
            <th class="caption">DbPrefix</th>
            <th class="caption">Webroot</th>
            <th class="caption">Fileroot</th>
            <th class="caption">Default</th>
            <th class="caption">Version</th>
        </tr>
        <!-- START portalList -->
        <tr>
            <td class="value-small">{Name}</td>
            <td class="value-small">{DbPrefix}</td>
            <td class="value-small">{Webroot}</td>
            <td class="value-small">{Fileroot}</td>
            <td class="value-small">{Default}</td>
            <td class="value-small">{Version}</td>
        </tr>
        <!-- END portalList -->
    </table>
</div>

