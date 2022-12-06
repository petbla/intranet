<form action="index.php?page=contact/save/{ID}"  method="post">
    <table class="edit-card">
        <tr>
            <td>
                <label class="title">{lbl_Title}</label><br>
                <input type="text" class="value" name="Title" value="{Title}" TitleID="{ID}"/><br>
                <input type="hidden" name="oldTitle" value="{Title}" oldTitleID="{ID}">               
                <label class="title">{lbl_First_name}</label><br>
                <input type="text" class="value" name="FirstName" value="{FirstName}" FirstNameID="{ID}"/><br>
                <input type="hidden" name="oldFirstName" value="{FirstName}" oldFirstNameID="{ID}"/>
                <label class="title">{lbl_Last_name}</label><br>
                <input type="text" class="value col_lastname" name="LastName" value="{LastName}" LastNameID="{ID}"/>
                <input type="hidden" name="oldLastName" value="{LastName}" oldLastNameID="{ID}"/>
            </td>
            <td width="50%">
                <label class="title">{lbl_Function}</label><br>    
                <input type="text" class="value" name="Function" value="{Function}" FunctionID="{ID}"/><br>
                <input type="hidden" name="oldFunction" value="{Function}" oldFunctionID="{ID}"/>

                <label class="title">{lbl_Company}</label><br>
                <input type="text" class="value col_company" name="Company" value="{Company}" CompanyID="{ID}"/><br>
                <input type="hidden" name="oldCompany" value="{Company}" oldCompanyID="{ID}"/>

                <label class="title">{lbl_Web}</label><br>
                <input type="url" class="value col_web" name="Web" value="{Web}" WebID="{ID}"/>
                <input type="hidden" name="oldWeb" value="{Web}" oldWebID="{ID}"/>
            </td>
            <td>
                <label class="title">{lbl_Address}</label><br>
                <textarea class="value col_address" name="Address" rows="5" AddressID="{ID}">{Address}</textarea>
                <input type="hidden" name="oldAddress" value="{Address}" oldAddressID="{ID}"/><br>

                <label class="title">{lbl_BirthDate}</label><br>
                <input type="date" class="value col_date" name="BirthDate" value="{BirthDate}" BirthDateID="{ID}"/>
                <input type="hidden" name="oldBirthDate" value="{BirthDate}" oldBirthDateID="{ID}"/><br>
            </td>
        </tr>
        <tr>
            <td>
                <label class="title">{lbl_Phone}</label><br>
                <input type="tel" class="value" name="Phone" value="{Phone}" PhoneID="{ID}"/><br>
                <input type="hidden" name="oldPhone" value="{Phone}" oldPhoneID="{ID}"/>

                <label class="title">{lbl_Email}</label><br>
                <input type="text" class="value col_email" name="Email" value="{Email}" EmailID="{ID}"/>
                <input type="hidden" name="oldEmail" value="{Email}" oldEmailID="{ID}"/>

                <label class="title">{lbl_CloseCard}</label>
                <input type="checkbox" class="value" name="Close" value="{Close}" CloseID="{ID}"/>
                <input type="hidden" name="oldClose" value="{Close}" oldCloseID="{ID}"/>                
            </td>
            <td width="50%">
                <label class="title">{lbl_Comment}</label><br>
                <textarea class="value col_note" name="Note" rows="10" NoteID="{ID}">{Note}</textarea>
                <input type="hidden" name="oldNote" value="{Note}" oldNoteID="{ID}"/>
            </td>
            <td>
                <label class="title" for="grouplist">{lbl_ChoiceGroup}</label><br>
                <select name="grouplist{ID}" id="grouplist{ID}">
                    <option value="" class="value">{lbl_choiceAction}</option>
                    <!-- START GroupList -->
                    <option value="{Code}" class="value">{Code} - {Name}</option>
                    <!-- END GroupList -->
                </select><br>
                <label class="title">{lbl_Label}</label>
                <p class="tags{ID}" ContactGroupsID="{ID}">{ContactGroups}</p>
                <input type="hidden" name="oldContactGroups" value="{ContactGroups}" oldContactGroupsID="{ID}"/>
                <input type="hidden" id="ContactGroups{ID}" name="ContactGroups" value="{ContactGroups}">
            </td>
        </tr>
        <tr>
            <td colspan = "3">
                <input type="submit" name="submitEditContact" class="action_button" value="{lbl_Save}">
                <input type="hidden" name="ID" value="{ID}">
                <input type="hidden" name="searchText" value="{searchText}">
            </td>
        </tr>    
    </table>
</form>


