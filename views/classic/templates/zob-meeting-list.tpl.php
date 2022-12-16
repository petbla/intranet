<div id="ListItems">
    <table class="table-child">
        <tr>
            <th style="width:100px;">.................</th>
            <th>{lbl_EntryNo}</th>
            <th>{lbl_AtDate}</th>
            <th>{lbl_PostedUpDate}</th>
            <th colspan="2">{lbl_PostedDownDate}</th>
        </tr>
        <!-- START meetingList -->
        <tr>      
            <td class="col_action">
                <img src="views/classic/images/icon/modify.png" alt="{lbl_edit}"/>
                <a href="index.php?page=zob/meeting/delete/{MeetingID}"><img src="views/classic/images/icon/delete.png" alt="{lbl_delete}" onclick="return ConfirmDelete();"/></a>
            </td>
            <td class="col_text">{EntryNo}/{Year}</td>
            <td class="col_text">{AtDate}</td>
            <td class="col_text">{PostedUpDate}</td>
            <td colspan="2" class="col_text">{PostedDownDate}</td>
        </tr>
        <!-- END meetingList -->
        <form action="index.php?page=zob/meeting/add"  method="post">
            <tr>
                <td colspan="2">
                    <img src="views/classic/images/nav/addNew.png" alt="{lbl_New}" />                    
                </td>
                <td>
                    <input type="date" id="fielAtDate" class="value" name="AtDate" value="" autofocus required/>
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
