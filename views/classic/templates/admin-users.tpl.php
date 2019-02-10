<div id="users">
    <table>
        <tr>
            <th>{lbl_auth_username}</th>
            <th>{lbl_User_name}</th>
            <th>{lbl_PermissionSet}</th>
            <th>{lbl_Close}</th>
            <th>{lbl_ACTIONS}</th>
        </tr>
        <!-- START UserList -->
        <tr>
            <td>{Name}</td>
            <td>{FullName}</td>
            <td>{PermissionSet}</td>
            <td>{Close}</td>
            <td>
                <a href="index.php?page=admin/modifyuser&ID={ID}"><img src="views/classic/images/icon/modify.png" alt="{lbl_edit}" title="{lbl_edit}"></a>
                &nbsp;
                <a href="index.php?page=admin/deleteuser&ID={ID}"><img src="views/classic/images/icon/delete.png" alt="{lbl_Delete}" title="{lbl_Delete}"></a>
            </td>
        </tr>
        <!-- END UserList -->
    </table>
    <a href="index.php?page=admin/newuser" class="action"><img src="views/classic/images/nav/adduser.png" alt="{lbl_New}">&nbsp;{lbl_NewUser}</a>
</div>
