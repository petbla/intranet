<form action="index.php?page=contact/save/{ID}"  method="post">
    <table class="edit-card">
        <tr>
            <td>
                <label class="title">{lbl_Title}</label><br>
                <input type="text" class="value" name="Title" value="{Title}"/><br>
                <label class="title">{lbl_First_name}</label><br>
                <input type="text" class="value" name="FirstName" value="{FirstName}"/><br>
                <label class="title">{lbl_Last_name}</label><br>
                <input type="text" class="value col_lastname" name="LastName" value="{LastName}"/>
            </td>
            <td width="50%">
                <label class="title">{lbl_Function}</label><br>    
                <input type="text" class="value" name="Function" value="{Function}"/><br>
                <label class="title">{lbl_Company}</label><br>
                <input type="text" class="value col_company" name="Company" value="{Company}"/><br>
                <label class="title">{lbl_Web}</label><br>
                <input type="text" class="value col_web" name="Web" value="{Web}"/>
            </td>
            <td>
                <label class="title">{lbl_Address}</label><br>
                <textarea class="value col_address" name="Address" rows="5">{Address}</textarea>
            </td>
        </tr>
        <tr>
            <td>
                <label class="title">{lbl_Phone}</label><br>
                <input type="text" class="value" name="Phone" value="{Phone}"/><br>
                <label class="title">{lbl_Email}</label><br>
                <input type="text" class="value col_email" name="Email" value="{Email}"/>
            </td>
            <td>
                <label class="title">{lbl_Comment}</label><br>
                <textarea class="value col_note" name="Note" rows="10">{Note}</textarea>
            </td>
            <td>
                <label for="grouplist">{lbl_Groups}</label><br>
                <select name="grouplist{ID}" id="grouplist{ID}">
                    <option value="">{lbl_choiceAction}</option>
                    <!-- START GroupList -->
                    <option value="{Code}">{Code} - {Name}</option>
                    <!-- END GroupList -->
                </select><br>
                <label class="title">{lbl_Label}</label>
                <p class="tags{ID}">{ContactGroups}</p>
                <input type="hidden" id="ContactGroups{ID}" name="ContactGroups" value="{ContactGroups}">
            </td>
        </tr>
        <tr>
            <td colspan = "3">
                <input type="submit" name="submitEditContact" class="action" value="{lbl_Save}">
                <input type="hidden" name="ID" value="{ID}">
                <input type="hidden" name="searchText" value="{searchText}">
            </td>
        </tr>    
    </table>
</form>


