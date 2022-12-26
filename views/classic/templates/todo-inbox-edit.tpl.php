<form action="index.php?page=todo/inbox/modify" method="post">
    <table class="edit-card">
        <tr>
            <td class="col_title">
                <label>{lbl_Title}</label>
            </td>
            <td>
                <input type="text" id="fieldTitle{InboxID}" class="value col_fullname" name="Title" value="{Title}" autofocus required/>
            </td>
        </tr>
        <tr>
            <td class="col_title">
                <label>{lbl_Folder}</label>
            </td>
            <td>
                <a href="index.php?page=todo/inbox/folder/list/{InboxID}" class="action_button">{lbl_AddToFolder}</a>
                {DmsentryName}
            </td>
        </tr>
        <tr>
            <td class="col_title">
                <label>{lbl_SettlementType}</label>
            </td>
            <td>
                <select id="select{InboxID}">
                    <!-- START listType{InboxID} -->
                    <option id="opt{MeetingTypeID}_{InboxID}" selected="">{MeetingName}</option>
                    <!-- END listType{InboxID} -->
                </select>
                &nbsp;
                {MeetingNo}
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <input type="hidden" id="fieldInboxID{InboxID}" name="InboxID" value="{InboxID}">
                <input type="hidden" id="fieldSourceUrl{InboxID}" name="SourceUrl" value="{SourceUrl}">
                <input type="submit" name="submitEditCard" class="action_button" value="{lbl_Save}">
            </td>
        </tr>
    </table>
</form>
<iframe id="viewDocument{InboxID}" src="{SourceUrl}" name="iframe_a" height="800px" width="100%" title="Dokument"></iframe>
<script>
    var opt,sel;
    if('{SelectMeetingTypeID}' != ''){
        opt = document.getElementById("opt{SelectMeetingTypeID}_{InboxID}");
        if(opt){
            opt.selected = 'selected';
        }
        sel = document.getElementById("select{InboxID}")
        if(sel){
            sel.disabled = true;
        }
    }
</script>