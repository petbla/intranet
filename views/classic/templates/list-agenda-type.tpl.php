{search}
<div id="pagecounter"></div>
{pageTitle}
{navigate_menu}
<div id="ListItems">
    <table>
        <tr>
            <th style="width:100px;">..........</th>
            <th>{lbl_Name}</th>
            <th>{lbl_NoSeries}</th>
            <th>{lbl_LastNo}</th>
        </tr>
        <!-- START AgendaTypeList -->
        <tr>      
            <td class="col_action">
                <a href="index.php?page=agenda/type/modify/{TypeID}"><img src="views/classic/images/icon/modify.png" alt="{lbl_edit}" id="{TypeID}" /></a>
                <a href="index.php?page=agenda/type/delete/{TypeID}"><img src="views/classic/images/icon/delete.png" alt="{lbl_delete}" id="{TypeID}" onclick="return ConfirnDelete();"/></a>
            </td>
            <td class="col_text">
                <a href="index.php?page=agenda/list/{TypeID}">{Name}</a>
            </td>
            <td class="col_text">{NoSeries}</td>
            <td class="col_text">{LastNo}</td>
        </tr>
        <!-- END AgendaTypeList -->
    </table>
</div>
<br />
{editcard}                
<div id="pagecounter" class="bottom">
{navigate_menu}
</div>

