<div id="contact">
    <form action="index.php?page=contact/save/{ID}"  method="post">
        <ul class="contact-card">
            <li>
                <label class="title">{lbl_First_name}</label>
                <input type="text" class="value" name="FirstName" value="{FirstName}"/>
            </li>
            <li>
                <label class="title">{lbl_Last_name}</label>
                <input type="text" class="value" name="LastName" value="{LastName}"/>
            </li>
            <li>
                <label class="title">{lbl_Title}</label>
                <input type="text" class="value" name="Title" value="{Title}"/>
            </li>
            <li>
                <label class="title">{lbl_Function}</label>    
                <input type="text" class="value" name="Function" value="{Function}"/>
            </li>
            <li>
                <label class="title">{lbl_Company}</label>    
                <input type="text" class="value" name="Company" value="{Company}"/>
            </li>
            <li>
                <label class="title">{lbl_Phone}</label>
                <input type="text" class="value" name="Phone" value="{Phone}"/>
            </li>
            <li>
                <label class="title">{lbl_Email}</label>
                <input type="text" class="value" name="Email" value="{Email}"/>
            </li>
            <li>
                <label class="title">{lbl_Web}</label>
                <input type="text" class="value" name="Web" value="{Web}"/>
            </li>
            <li>
                <label class="title">{lbl_Address}</label>
                <textarea class="value" name="Address" rows="5">{Address}</textarea>
            </li>
            <li>
                <label class="title">{lbl_Comment}</label>
                <textarea class="value" name="Note" rows="10">{Note}</textarea>
            </li>
            <li>
                <label for="grouplist">{lbl_Groups}</label>
                <select name="grouplist" id="grouplist">
                    <!-- START GroupList -->
                    <option value="{Code}">{Name}</option>
                    <!-- END GroupList -->
                </select>
            </li>
            <li>
                <label class="title">{lbl_Label}</label>
                <p class="tags">{Groups}</p>
            </li>
            <li>
                <input type="submit" name="submitEditContact" class="action" value="{lbl_Save}">
            </li>
            <li>
                <input type="hidden" name="ID" value="{ID}">
            </li>
        </ul>    
    </form>
</div>


