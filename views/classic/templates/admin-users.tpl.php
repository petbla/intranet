<div id="message">
    <h2>{message}</h2>
</div>
<div id="users">    
    <form action="index.php?page=admin/adduser" method="post">
        <table>
            <tr>
                <th>{lbl_auth_username}</th>
                <th>{lbl_User_name}</th>
                <th>{lbl_PermissionSet}</th>
                <th>{lbl_ACTIONS}</th>
            </tr>
            <!-- START UserList -->
            <tr>
                <td>{Name}</td>
                <td>{FullName}</td>
                <td>{PermissionSet} - {Role}</td>
                <td>
                    <a href="index.php?page=admin/modifyuser&ID={ID}"><img src="views/classic/images/icon/modify.png" alt="{lbl_edit}" title="{lbl_edit}"></a>
                    &nbsp;
                    <a href="index.php?page=admin/deleteuser&ID={ID}"><img src="views/classic/images/icon/delete.png" alt="{lbl_Delete}" title="{lbl_Delete}" onclick="return ConfirmDelete();"></a>
                </td>
            </tr>
        <!-- END UserList -->
        </table>

        <table>
            <tr>
                {lbl_CreateNewUser}
            </tr>
            <tr>
                <th>{lbl_auth_username}</th>
                <th>{lbl_User_name}</th>
                <th>Heslo</th>
                <th>Zopakovat heslo</th>
                <th for="usr_perset">Role</th>
                <th></th>
            </tr>
            <tr>
                <td>                        
                    <input type="text" name="usr_name" id="usr_name" autofocus placeholder="{lbl_auth_username}" required>
                </td>
                <td>                        
                    <input type="text" name="usr_fullname" id="usr_fullname" autofocus placeholder="{lbl_User_name}" required>
                </td>
                <td>                        
                    <input type="password" name="usr_psw1" id="usr_psw1" required pattern=".{4}" title="{lbl_msg_maxLenghtPsw4}" placeholder="{lbl_Password}" required>
                </td>
                <td>                        
                    <input type="password" name="usr_psw2" id="usr_psw2" placeholder="{lbl_ConfirmPsw}" required>
                </td>
                <td>                        
                    <select name="usr_perset" id="usr_perset">
                        <!-- START PermissionSet -->
                        <option value="{Level}">{Name}</option>
                        <!-- END PermissionSet -->
                    </select>
                </td>
                <td>                        
                    <input type="submit" class="action" value="Uložit">
                </td>
            </tr>
        </table>
    </form>
</div>
    


