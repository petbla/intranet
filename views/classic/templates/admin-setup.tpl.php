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
                <span>{cfg_webroot}</span>
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
                <span>{cfg_fileroot}</span>
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
                název
            </td>
            <td class="value">
                <span>{cfg_compName}</span><br>
            </td>
            <td class="action">
            </td>
        </tr>
        <tr>
            <td class="name">
                Adresa
            </td>
            <td class="value">
                <span>{cfg_compAddress}</span><br>
                <span>{cfg_compCity}</span><br>
                <span>{cfg_compZip}</span><br>
                <span>{cfg_compICO}</span><br>
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

