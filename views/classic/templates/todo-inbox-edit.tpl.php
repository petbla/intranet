<form action="index.php?page=todo/inbox/modify" method="post">
    <table class="edit-card">
        <tr>
            <td class="col_title">
                <label>{lbl_Title}</label>
            </td>
            <td>
                <input type="text" class="value col_fullname big" name="Title" value="{Title}" pkID="{InboxID}" table="inbox" onchange="wsUpdate(this);" autofocus required/>
            </td>
        </tr>
        <tr>
            <td class="col_title">
                <label>{lbl_Folder}</label>
            </td>
            <td>
                <a href="index.php?page=todo/inbox/folder/list/{InboxID}" id="addToFolder{InboxID}" dmsEntryID="{DmsEntryID}" class="action_button">{lbl_AddToFolder}</a>
                <a href="index.php?page=document/list/{ParentID}">{DmsentryName}</a>
                <script>
                    var e;
                    e = document.getElementById('addToFolder{InboxID}');
                    if(e)
                        if(e.getAttribute('dmsEntryID') != '00000000-0000-0000-0000-000000000000')
                            e.style.display = 'none';
                </script>
            </td>
        </tr>
        <tr>
            <td class="col_title">
                <label>{lbl_SettlementType}</label>
            </td>
            <td>
                <select id="select{InboxID}" name="SettlementType" value="" onchange="this.setAttribute('value',this.options[this.selectedIndex].text);">
                    <!-- START listType{InboxID} -->
                    <option id="opt{MeetingTypeID}_{InboxID}" selected="">{MeetingName}</option>
                    <!-- END listType{InboxID} -->
                </select>
                &nbsp;
                <a href="index.php?page=zob/meetingline/list/{MeetingID}">{MeetingNo}</a>
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
<iframe id="viewDocument{InboxID}" src="" name="iframe_a" height="800px" width="100%" title="Dokument"></iframe>
<span id="attachmentDocument{InboxID}">
    <img src="views/classic/images/icon/attachment.png" />
    <a id="hreftDocument{InboxID}" href="{SourceUrl}">{Title}</a>
</span>
<script>
    var opt,sel, app, e;
    var frame,attach;
    frame = document.getElementById('viewDocument{InboxID}');
    attach = document.getElementById('attachmentDocument{InboxID}');
    e = document.getElementById('hreftDocument{InboxID}');
    if('{fileextension}' == 'pdf'){
        frame.setAttribute('src','{SourceUrl}');
        attach.style.display = 'none';
    }else{
        frame.style.display = 'none';
        $app = getApplication('{fileextension}');
        e.href = $app + '{SourceUrl}'; 
    }
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