<div id="actionpanel">
    <table>
        <tr>
            <td>
                <a href="index.php?page=agenda/add/{TypeID}"><img src="views/classic/images/nav/addNew.png" alt="{lbl_NewAgenda}" title="{lbl_NewAgenda}"></a>
                <br>
                {lbl_NewAgenda}
            </td>
        </tr>
    </table>
</div>
<div id="DocumentItems">
    <table>
        <tr>
            <th style="width:100px;">..........</th>
            <th>{lbl_DocumentNo}</th>
            <th>{lbl_Description}</th>
            <th>{lbl_CreateDate}</th>
            <th>{lbl_ExecuteDate}</th>
        </tr>
        <!-- START AgendaList -->
        <tr>      
            <td class="col_action">
                <!-- <a href="index.php?page=agenda/modify/{ID}"><img src="views/classic/images/icon/modify.png" alt="{lbl_edit}" id="{ID}" /></a> -->
                <a href="index.php?page=agenda/delete/{TypeID}"><img src="views/classic/images/icon/delete.png" alt="{lbl_delete}" id="{TypeID}" onclick="return ConfirmDelete();"/></a>
                <a href="index.php?page=agenda/unlink/{ID}" a_type="agendaUnlink" data-agenda-entryid="{EntryID}" ><img src="views/classic/images/icon/unlink.png" alt="{lbl_delete}" id="{ID}" onclick="return ConfirmUnlink();"/></a>
                
                <a href="" a_type="agendaSourceFolder" data-agenda-entryid="{EntryID}" data-dms-server="{cfg_webroot}" data-agenda-entryname="{Name}"></a>                
                <a href="" a_type="agendaPDF" data-agenda-entryid="{EntryID}" data-dms-server="{cfg_webroot}" data-agenda-entryname="{Name}"></a>                

            </td>
            <td class="col_text" a_type="agenda" data-agenda-entryid="{EntryID}" data-dms-server="{cfg_webroot}" data-agenda-entryname="{Name}">{DocumentNo}</td>
            
            <td class="col_text">{Description}</td>
            <td class="col_text">{CreateDate}</td>
            <td class="col_text">{ExecuteDate}</td>
        </tr>
        <!-- END AgendaList -->
    </table>
</div>
<div id="pagecounter" class="bottom">
{navigate_menu}
</div>

