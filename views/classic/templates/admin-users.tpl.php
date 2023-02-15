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
    <form action="index.php?page=admin/user/add" method="post">
        <table>
            <tr>
                <th style="width:100px;">..........</th>
                <th>{lbl_auth_username}</th>
                <th>{lbl_User_name}</th>
                <th>{lbl_PermissionSet}</th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            <!-- START UserList -->
            <tr>
                <td class="col_action">
                    <img src="views/classic/images/icon/modify.png" alt="{lbl_edit}" onClick = "modifyUser('{ID}','{Name}','{FullName}','{PermissionSet}','modify',true);"/>
                    <a href="index.php?page=admin/user/delete/{ID}"><img src="views/classic/images/icon/delete.png" alt="{lbl_Delete}" id="{ID}" title="{lbl_Delete}" onclick="return ConfirmDelete();"></a>
                </td>
                <td class="col_text" recID="meetingline{ID}" pkID="{ID}" table="meetingline" name="Name">{Name}</td>
                <td class="col_text" recID="meetingline{ID}" pkID="{ID}" table="meetingline" name="FullName">{FullName}</td>
                <td class="col_text">
                    <span recID="meetingline{ID}" pkID="{ID}" table="meetingline" name="PermissionSet">{PermissionSet}</span> - <span recID="meetingline{ID}" pkID="{ID}" table="meetingline" name="Role">{Role}</span>
                </td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <!-- END UserList -->
            <tr>
                <td>
                    <img src="views/classic/images/nav/addNew.png" alt="{lbl_New}" onClick = "modifyUser('','','','','add',false);"/>                    
                </td>
                <td>                        
                    <input type="text" id="fieldName" class="value" name="Name" value="" autofocus required>
                </td>
                <td>                        
                    <input type="text" id="fieldFullName" class="value" name="FullName" placeholder="{lbl_User_name}" required>
                </td>
                <td>                        
                    <select id="fieldPerSet" class="value" name="PerSet">
                        <!-- START PermissionSet -->
                        <option id="fieldPerSet{Name}" value="{Level}" selected="">{Name}</option>
                        <!-- END PermissionSet -->
                    </select>
                </td>
                <td>
                    Heslo
                    <input type="password" id="fieldPsw1" class="value" name="Psw1" required pattern=".{4}" title="{lbl_msg_maxLenghtPsw4}" placeholder="{lbl_Password}" required>
                </td>
                <td>
                    Zopakovat heslo
                    <input type="password" id="fieldPsw2" class="value" name="Psw2" placeholder="{lbl_ConfirmPsw}" required>
                </td>
                <td>
                    <input type="hidden" id="fieldID" name="ID" value="">
                    <input type="hidden" id="fieldAction" name="action" value="add">
                    <input type="submit" name="submitEditUser" class="action_button" value="{lbl_Save}">
                </td>
            </tr>
        </table>
    </form>
</div>
    


