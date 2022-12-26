<div id="ListItems">
    <p id="pageMessage" class="message" onClick="this.style.display = 'none';" >{message}</p>
    <script>
        var e;
        e = document.getElementById('pageMessage');
        if(e.innerHTML == '')
            e.style.display = 'none';
    </script>
    <p id="pageErrorMesage" class="error" onClick="this.style.display = 'none';" >{errorMessage}</p>
    <script>
        var e;
        e = document.getElementById('pageErrorMesage');
        if(e.innerHTML == '')
            e.style.display = 'none';
    </script>

    <a href="index.php?page=todo/inbox/refresh" class="button"><span class="action_close">Aktualizace</span></a>
    <table class="table-child">
        <tr>
            <th style="width:100px;">....................</th>
            <th>{lbl_Title}</th>
            <th>{lbl_MeetingNo}</th>
            <th>{lbl_CreateDate}</th>
        </tr>
        <!-- START listInbox -->
        <tr>      
            <td class="col_action">
                <img src="views/classic/images/icon/{ismodify}modify.png" alt="{lbl_edit}" onClick = "modifyTodoInbox('{InboxID}');"/>
                &nbsp;
                <img src="views/classic/images/icon/{isfolder}folder.png" alt="" />
                <img src="views/classic/images/icon/{ismeeting}meeting.png" alt="" />
            </td>
            <td class="col_text">
                <span class="pointer" url="{SourceUrl}" onClick = "modifyTodoInbox('{InboxID}');" >{Title}</span>
            </td>            
            <td class="col_text">{MeetingNo}</td>
            <td class="col_text">{CreateDate}</td>
        </tr>
        <tr>
        </tr>
        <tr>
            <td colspan="4" style="margin:0;padding:0;">
                <div id="editInbox{InboxID}" style="margin:0 100px; display:none;">
                    <span class="action_close" onclick="document.getElementById('editInbox{InboxID}').style.display = 'none';">{lbl_Close}</span>
                    <br>
                    {inboxCard}
                </div>
            </td>
        </tr>
        <!-- END listInbox -->
    </table>
    <input type="hidden" id="activeInbox" name="activeInbox" value="{activeInboxID}" />
</div>
