<div id="ListItems">
    <table class="table-child">
        <tr onClick="document.getElementById('memberCard{MeetingTypeID}').style.display = 'none';">
            <th style="width:100px;">.................</th>
            <th>{lbl_First_name}</th>
            <th colspan="2">{lbl_MemberType}</th>
        </tr>
        <!-- START memberList{MeetingTypeID} -->
        <tr>      
            <td class="col_action">
                <img src="views/classic/images/icon/modify.png" alt="{lbl_edit}" onClick = "modifyZobMember('{MemberID}','{MeetingTypeID}','{ContactName}','{MemberType}','modify');"/>
                <a href="index.php?page=zob/member/delete/{MemberID}/{MeetingTypeID}"><img src="views/classic/images/icon/delete.png" alt="{lbl_delete}" onclick="return ConfirmDelete();"/></a>
            </td>
            <td class="col_text">{ContactName{MeetingTypeID}}</td>
            <td colspan="2" class="col_text">{MemberType{MeetingTypeID}}</td>
        </tr>
        <!-- END memberList{MeetingTypeID} -->
        <form action="index.php?page=zob/member/add"  method="post">
            <tr>
                <td>
                    <img src="views/classic/images/nav/addNew.png" alt="{lbl_New}" onClick = "modifyZobMember('','','','','add');"/>                    
             </td>
                <td>
                    <input type="text" id="fieldMemContactName{MeetingTypeID}" class="value" name="ContactName" value="" autofocus required/>
                </td>
                <td>
                    {memberTypeSelect}
                </td>
                <td>
                    <input type="hidden" id="fieldMemMemberID{MeetingTypeID}" name="MemberID" value="">
                    <input type="hidden" id="fieldMemMeetingTypeID{MeetingTypeID}" name="MeetingTypeID" value="{MeetingTypeID}">
                    <input type="hidden" id="fieldMemAction{MeetingTypeID}" name="action" value="add">
                    <input type="submit" name="submitEditCard" class="action_button" value="{lbl_Save}">
                    &nbsp; <img src="views/classic/images/nav/addContact.png" title="Nový kontakt" onClick="document.getElementById('newContactCard{MeetingTypeID}').style.display = 'block';"/>
                </td>
            </tr>
        </form>
        <tr>
            <td>
            </td>
            <td colspan="3">
                <div id="newContactCard{MeetingTypeID}" style="display:none;" >
                <img src="views/classic/images/icon/delete.png" onClick="document.getElementById('newContactCard{MeetingTypeID}').style.display = 'none';">
                {newContactCard}
                </div>
            </td>
        </tr>
    </table>
</div>
