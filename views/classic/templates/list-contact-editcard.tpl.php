<div id="editwindow" form_id="{ID}" style="display: none;">
<div id="editwindowheader{ID}" class="editwindowheader">{lbl_msg_EditContact}</div>
    <form action="index.php?page=contact/save/{ID}" method="post">
        <fieldset>
            <button type="submit" name="save" value="Zapsat">{lbl_Save}</button>
            <button name="back_id" value="zpět" back_id="{ID}">{lbl_Cancel}</button>
            <hr>
            <label for="newFirstName">
                <span>{lbl_First_name}</span>
                <input type="text" class="editInLine" name="newFirstName" inputFirstName_id="{ID}" autofocus>
            </label>
            <label for="newLastName">
                <span>{lbl_Last_name}</span>
                <input type="text" class="editInLine" name="newLastName" inputLastName_id="{ID}">
            </label>
            <label for="newTitle">
                <span>{lbl_Title}</span>
                <input type="text" class="editInLine" name="newTitle" inputTitle_id="{ID}">
            </label>
            <label for="newFunction">
                <span>{lbl_Function}</span>
                <input type="text" class="editInLine" name="newFunction" inputFunction_id="{ID}">
            </label>
            <label for="newCompany">
                <span>{lbl_Company}</span>
                <input type="text" class="editInLine" name="newCompany" inputCompany_id="{ID}">
            </label>
            <label for="newPhone">
                <span>{lbl_Phone}</span>
                <input type="tel" class="editInLine" name="newPhone" inputPhone_id="{ID}">
            </label>
            <label for="newEmail">
                <span>{lbl_Email}</span>
                <input type="email" class="editInLine" name="newEmail" inputEmail_id="{ID}">
            </label>
            <label for="newWeb">
                <span>WWW</span>
                <input type="text" class="editInLine" name="newWeb" inputEmail_id="{ID}">
            </label>
            <label for="newAddress">
                <span>{lbl_Address}</span>
                <textarea class="editInLine" name="newAddress" inputAddress_id="{ID}" rows="5">{Address}</textarea>
            </label>
            <label for="newNote">
                <span>{lbl_Comment}</span>
                <textarea class="editInLine" name="newNote" inputNote_id="{ID}" rows="10">{Note}</textarea>
            </label>
            <label for="grouplist">{lbl_Groups}</label>
                <select name="grouplist" id="grouplist{ID}">
                    <option value="" selected>{lbl_choiceAction}</option>
                    <!-- START GroupList -->
                    <option value="{Code}">{Code} - {Name}</option>
                    <!-- END GroupList -->
                </select>
            </label>
            <label class="title">{lbl_Label}</label>
                <p class="tags{ID}">{ContactGroups}</p>
                <input type="hidden" id="ContactGroups{ID}" name="ContactGroups" value="{ContactGroups}">
            </label>
            <button type="submit" name="save" value="Zapsat">{lbl_Save}</button>
            <button name="back_id" value="zpět" back_id="{ID}">{lbl_Cancel}</button>
            <input type="hidden" name="ID" value="{ID}">
            <input type="hidden" name="FirstName" value="{FirstName}" oldFirstName_id="{ID}">
            <input type="hidden" name="LastName" value="{LastName}" oldLastName_id="{ID}">
            <input type="hidden" name="Title" value="{Title}" oldTitle_id="{ID}">
            <input type="hidden" name="Function" value="{Function}" oldFunction_id="{ID}">
            <input type="hidden" name="Company" value="{Company}" oldCompany_id="{ID}">
            <input type="hidden" name="Phone" value="{Phone}" oldPhone_id="{ID}">
            <input type="hidden" name="Email" value="{Email}" oldEmail_id="{ID}">
            <input type="hidden" name="Web" value="{Web}" oldEmail_id="{ID}">
            <input type="hidden" name="Address" value="{Address}" oldAddress_id="{ID}">
            <input type="hidden" name="Note" value="{Note}" oldNote_id="{ID}">
            <input type="hidden" name="sqlrequest" value="{sqlrequest}" oldNote_id="{ID}">
        </fieldset>
    </form>
</div>

