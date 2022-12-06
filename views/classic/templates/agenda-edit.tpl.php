<div id="editcard">
    <form action="index.php?page=agenda/add"  method="post">
        <ul class="edit-card">
            <li>
                <label class="title">{lbl_DocumentNo}</label>
                <input type="text" class="value" name="DocumentNo" value="{EditDocumentNo}"/>
            </li>
            <li>
                <label class="title">{lbl_Description}</label>
                <input type="text" class="value" name="Description" value="{EditDescription}"/>
            </li>
            <li>
                <input type="hidden" name="ID" value="{EditID}">    
                <input type="submit" name="submitEditCard" class="action_button" value="{lbl_Save}">
            </li>
        </ul>    
    </form>
</div>


