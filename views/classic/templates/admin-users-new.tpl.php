<div id="users">
    <h3>Založení nového uživatele</h3>
    <form action="index.php?page=admin/adduser" method="post" id="addNewUser">
        <fieldset>
            <ul class="form-style-user">
                <li>
                    <label>Jméno</label>
                    <input type="text" name="usr_name" id="usr_name" autofocus placeholder="Jméno uživatele" required>
                </li>
                <li>
                    <label>Heslo</label>
                    <input type="password" name="usr_psw1" id="usr_psw1" required pattern=".{4}" title="Délka hesla musí být minimálně 4 znaky." placeholder="Heslo" required>
                </li>
                <li>
                    <label>Zopakovat heslo</label>
                    <input type="password" name="usr_psw2" id="usr_psw2" placeholder="Potvrdit heslo" required>
                </li>
                <li>
                    <label for="usr_perset">Role</label>
                    <select name="usr_perset" id="usr_perset">
                        <!-- START PermissionSet -->
                        <option value="{Level}">{Name}</option>
                        <!-- END PermissionSet -->
                    </select>
                </li>
                <li>
                    <input type="submit" class="action" value="Založit uživatele">
                </li>
            </ul>
        </fieldset>
    </form>
</div>
