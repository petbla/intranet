<div id="editcard">
    <form action="index.php?page=agenda/type/add"  method="post">
        <ul class="edit-card">
            <li>
                <label class="title">{lbl_Name}</label>
                <input type="text" class="value" name="Name" value="{EditName}"/>
            </li>
            <li>
                <label class="title">{lbl_NoSeries}</label>
                <input type="text" class="value" name="NoSeries" value="{EditNoSeries}"/>
            </li>
            <li>
                <input type="hidden" name="TypeID" value="{EditTypeID}">    
                <input type="submit" name="submitEditCard" class="action_button" value="{lbl_Save}">
            </li>
        </ul>    
    </form>
</div>


