<div id="pagecounter"></div>
{pageTitle}
{navigate_menu}
<div id="ListItems">
    <form action="index.php?page=agenda/type/add"  method="post">
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
                    <a href="index.php?page=agenda/type/delete/{TypeID}"><img src="views/classic/images/icon/delete.png" alt="{lbl_delete}" id="{TypeID}" onclick="return ConfirmDelete();"/></a>
                </td>
                <td class="col_text">
                    <a href="index.php?page=agenda/list/{TypeID}">{Name}</a>
                </td>
                <td class="col_text">{NoSeries}</td>
                <td class="col_text">{LastNo}</td>
            </tr>
            <!-- END AgendaTypeList -->

            <tr>
                <td>
                    <img src="views/classic/images/nav/addNew.png" alt="{lbl_New}" />                    
                </td>
                <td>
                    <input type="text" class="value" name="Name" value="{EditName}"/>
                </td>
                <td>
                    <input type="text" class="value" name="NoSeries" value="{EditNoSeries}"/>
                    </td>
                <td>
                    <input type="hidden" name="TypeID" value="{EditTypeID}">    
                    <input type="submit" name="submitEditCard" class="action" value="{lbl_Save}">
                </td>
            </tr>
        </table>
    </form>
</div>
<div id="pagecounter" class="bottom">
{navigate_menu}
</div>

