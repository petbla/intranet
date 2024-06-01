<form action="index.php?page=contact/save/{ID}"  method="post">
    <table class="edit-card">
        <tr>
            <td>
                <label class="title">{lbl_Title}</label><br>
                <input type="text" class="value" value="{Title}" recID="contact{ID}" pkID="{ID}" table="contact" name="Title" onchange="wsUpdate(this);"/><br>
                <label class="title">{lbl_First_name}</label><br>
                <input type="text" class="value col_name" value="{FirstName}" recID="contact{ID}" pkID="{ID}" table="contact" name="FirstName" onchange="wsUpdate(this);"/><br>
                <label class="title">{lbl_Last_name}</label><br>
                <input type="text" class="value col_lastname" value="{LastName}" recID="contact{ID}" pkID="{ID}" table="contact" name="LastName" onchange="wsUpdate(this);"/>
            </td>
            <td width="50%">
                <label class="title">{lbl_Function}</label><br>    
                <input type="text" class="value" value="{Function}" recID="contact{ID}" pkID="{ID}" table="contact" name="Function" onchange="wsUpdate(this);"/><br>
                <label class="title">{lbl_Company}</label><br>
                <input type="text" class="value col_company" value="{Company}" recID="contact{ID}" pkID="{ID}" table="contact" name="Company" onchange="wsUpdate(this);"/><br>
                <label class="title">{lbl_Web}</label><br>
                <input type="text" class="value col_web" value="{Web}" recID="contact{ID}" pkID="{ID}" table="contact" name="Web" onchange="wsUpdate(this);"/>
            </td>
            <td>
                <label class="title">{lbl_Address}</label><br>
                <textarea class="value col_address" rows="5" recID="contact{ID}" pkID="{ID}" table="contact" name="Address" onchange="wsUpdate(this);">{Address}</textarea>
                <label class="title">{lbl_BirthDate}</label><br>
                <input type="date" class="value col_date" value="{BirthDate}" recID="contact{ID}" pkID="{ID}" table="contact" name="BirthDate" onchange="wsUpdate(this);"/>
            </td>
        </tr>
        <tr>
            <td>
                <label class="title">{lbl_Phone}</label><br>
                <input type="tel" class="value" value="{Phone}" recID="contact{ID}" pkID="{ID}" table="contact" name="Phone" onchange="wsUpdate(this);"/><br>
                <label class="title">{lbl_Email}</label><br>
                <input type="text" class="value col_email" value="{Email}" recID="contact{ID}" pkID="{ID}" table="contact" name="Email" onchange="wsUpdate(this);"/>
                <label class="title">{lbl_DataBox}</label><br>
                <input type="text" class="value" value="{DataBox}" recID="contact{ID}" pkID="{ID}" table="contact" name="DataBox" onchange="wsUpdate(this);"/><br>
                <label class="title">{lbl_CloseCard}</label>
                <input type="checkbox" id = "Close{ID}" class="value" value="{Close}" recID="contact{ID}" pkID="{ID}" table="contact" name="Close" onchange = "validateCheckbox(this);"/ >
                <script>
                    var e;
                    e = document.getElementById('Close{ID}');
                    if (e.value == 1)
                        e.checked = true;
                </script>
            </td>
            <td width="50%">
                <label class="title">{lbl_Comment}</label><br>
                <textarea class="value col_note" rows="10" recID="contact{ID}" pkID="{ID}" table="contact" name="Note" onchange="wsUpdate(this);">{Note}</textarea>
            </td>
            <td>
                <label class="title">{lbl_ChoiceGroup}</label><br>
                <select recID="contact{ID}" pkID="{ID}" table="contact" name="ContactGroups" onchange="this.setAttribute('value',this.options[this.selectedIndex].text), wsUpdate(this);">
                    <option value="" class="value">{lbl_choiceAction}</option>
                    <!-- START GroupList -->
                    <option value="{Code}" class="value">{Code} - {Name}</option>
                    <!-- END GroupList -->
                </select><br>
                
                <label class="title">{lbl_Label}</label>
                <p class="tags" tagID="tag{ID}" recID="contact{ID}" pkID="{ID}" table="contact" name="ContactGroups" value="{ContactGroups}">{ContactGroups}</p>
            </td>
        </tr>
    </table>
</form>


