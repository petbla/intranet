<fieldset>
    <form action="index.php?page=zob/contact/add"  method="post">
        <label>Nový kontakt</label><br>
        <input type="text" class="value" name="newContactTitle" value="" placeholder="{lbl_Title}"/><br>
        <input type="text" class="value" name="newContactFirstName" value="" placeholder="{lbl_First_name}" required/><br>
        <input type="text" class="value col_lastname" name="newContactLastName" value="" placeholder="{lbl_Last_name}" required/><br>
        <input type="tel" class="value" name="newContactPhone" value="" placeholder="{lbl_Phone}"/><br>
        <input type="text" class="value col_email" name="newContactEmail" value="" placeholder="{lbl_Email}"/>
        <input type="submit" name="submitEditCard" class="action_button" value="{lbl_Save}">
        <input type="hidden" name="MeetingTypeID" value="{MeetingTypeID}">
    </form>
</fieldset>
