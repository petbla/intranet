{search}
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
<div id="pagecounter"></div>
<h1>{pageTitle}</h1>
{navigate_menu}
<div id="ListItems">
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
                <a href="index.php?page=agenda/modify/{TypeID}"><img src="views/classic/images/icon/modify.png" alt="{lbl_edit}" id="{TypeID}" /></a>
                <a href="index.php?page=agenda/delete/{TypeID}"><img src="views/classic/images/icon/delete.png" alt="{lbl_delete}" id="{TypeID}" onclick="return ConfirnDelete();"/></a>
            </td>
            <td class="col_text">{DocumentNo}</td>
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

