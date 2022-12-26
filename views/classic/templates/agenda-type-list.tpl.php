<div id="pagecounter">{navigate_menu}</div>
<div id="ListItems">
    <p id="pageMessage" class="message" onClick="this.style.display = 'none';" >{message}</p>
    <script>
        var e;
        e = document.getElementById('pageMessage');
        if(e.innerHTML == '')
            e.style.display = 'none';
    </script>
    <p id="pageErrorMesage" class="error" onClick="this.style.display = 'none';" >{errorMessage}</p>
    <script>
        var e;
        e = document.getElementById('pageErrorMesage');
        if(e.innerHTML == '')
            e.style.display = 'none';
    </script>
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
                    <img src="views/classic/images/icon/modify.png" alt="{lbl_edit}" onClick = "modifyAgendaType('{TypeID}','{Name}','{NoSeries}','modify');"/>
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
                    <img src="views/classic/images/nav/addNew.png" alt="{lbl_New}" onClick = "modifyAgendaType('','','','add');"/>                    
                </td>
                <td>
                    <input type="text" id="fieldName" class="value" name="Name" value="" required/>
                </td>
                <td>
                    <input type="text" id="fieldNoSeries" class="value" name="NoSeries" value="" required/>
                    </td>
                <td>
                    <input type="hidden" id="fieldTypeID" name="TypeID" value="">
                    <input type="hidden" id="fieldAction" name="action" value="add">
                    <input type="submit" name="submitEditCard" class="action_button" value="{lbl_Save}">
                </td>
            </tr>
        </table>
    </form>
</div>
<div id="pagecounter" class="bottom">{navigate_menu}</div>

