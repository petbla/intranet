<div id="DocumentItems">
 
    <p id="pageTitle" class="error" onClick="this.style.display = 'none';" >{pageTitle}</p>
    <script>
        var e;
        e = document.getElementById('pageTitle');
        if(e.innerHTML == '')
            e.style.display = 'none';
    </script>
    <table class="table-child">
        <tr>
            <th style="width:100px;">.................</th>
            <th>{lbl_Actual}</th>
            <th>{lbl_EntryNo}</th>
            <th>{lbl_AtDate}</th>
            <th>{lbl_PostedUpDate}</th>
            <th colspan="2">{lbl_PostedDownDate}</th>
        </tr>
        <!-- START meetingList -->
        <tr class="{lineclass}">      
            <td class="col_action">
                <img src="views/classic/images/icon/modify.png" title="{lbl_edit}" id="{MeetingID}" dmsClassName="{dmsClassName}""/>
                <a href="index.php?page=zob/meeting/delete/{MeetingTypeID}/{MeetingID}"><img src="views/classic/images/icon/delete.png" title="{lbl_Delete}" onclick="return ConfirmDelete();"/></a>
                <a href="index.php?page=zob/meetingline/list/{MeetingID}"><img src="views/classic/images/icon/meeting.png" title="Body jednání"/></a>
            </td>
            <td class="col_text"><img src="views/classic/images/icon/remind0{Actual}.png" alt="{lbl_Actual}"/></td>
            <td class="col_text">{EntryNo}/{Year}</td>
            <td class="col_text">{AtDate_view}</td>
            <td class="col_text">{PostedUpDate_view}</td>
            <td colspan="2" class="col_text">{PostedDownDate_view}</td>
        </tr>
        <tr>
        </tr>
        <tr>
            <td colspan="8" style="margin:0;padding:0;">
                <div id="editMeetingCard{MeetingID}" style="margin:0 100px; display:none;">
                    <span class="action_close" onclick="document.getElementById('editMeetingCard{MeetingID}').style.display = 'none';">{lbl_Close}</span>
                    <br>
                    {editdMeetingCard}
                </div>
            </td>
        </tr>
        <!-- END meetingList -->
        <form action="index.php?page=zob/meeting/add"  method="post">
            <tr>
                <td colspan="3">
                    <img src="views/classic/images/nav/addNew.png" alt="{lbl_New}" />                    
                </td>
                <td>
                    <input type="date" id="fielAtDate" class="value" name="AtDate" value="" autofocus/>
                </td>
                <td>
                    <input type="text" id="fielPostedUpDate" class="value" name="PostedUpDate" value=""/>
                </td>
                <td>
                    <input type="text" id="fielPostedDownDate" class="value" name="PostedDownDate" value=""/>
                </td>
                <td>
                    <input type="hidden" id="fieldMeetingTypeID" name="MeetingTypeID" value="{MeetingTypeID}">
                    <input type="hidden" id="fieldAction" name="action" value="add">
                    <input type="submit" name="submitEditCard" class="action_button" value="{lbl_Save}">
                </td>
            </tr>
        </form>
    </table>
</div>
