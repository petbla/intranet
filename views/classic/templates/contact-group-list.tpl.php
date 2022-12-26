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
    <form action="index.php?page=contact/group/add"  method="post">
        <table>
            <tr>
                <th style="width:100px;">..........</th>
                <th>{lbl_Name}</th>
                <th>{lbl_Code}</th>
                <th></th>
            </tr>
            <!-- START ContactGroupList -->
            <tr>      
                <td class="col_action">
                    <img src="views/classic/images/icon/modify.png" alt="{lbl_edit}" id="{Code}" onClick = "modifyContactGroup('{Code}','{Name}','modify',true);"/>
                    <a href="index.php?page=contact/group/delete/{Code}"><img src="views/classic/images/icon/delete.png" alt="{lbl_delete}" id="{Code}" onclick="return ConfirmDelete();"/></a>
                </td>
                <td class="col_text">
                    <a href="index.php?page=agenda/list/{TypeID}">{Name}</a>
                </td>
                <td class="col_text">{Code}</td>
                <td></td>
            </tr>
            <!-- END ContactGroupList -->

            <tr>
                <td>
                    <img src="views/classic/images/nav/addNew.png" alt="{lbl_New}" onClick = "modifyContactGroup('','','add',false);"/>                    
                </td>
                <td>
                    <input type="text" id="fieldName" class="value col_function" name="Name" value=""/>
                </td>
                <td>
                    <input type="text" id="fieldCode" class="value" name="Code" value=""/>
                    </td>
                <td>
                    <input type="hidden" id="fieldAction" name="action" value="add">
                    <input type="submit" name="submitContactGroup" class="action_button" value="{lbl_Save}">
                </td>
            </tr>
        </table>
    </form>
</div>
<div id="pagecounter" class="bottom">{navigate_menu}</div>
