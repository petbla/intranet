<div id="ListItems">
    <table class="table-child">
        <tr onClick="document.getElementById('meetingtypeCard{ElectionPeriodID}').style.display = 'none';">
            <th style="width:100px;">.................</th>
            <th>{lbl_meetingtype}</th>
            <th colspan="2" class="center">{lbl_Members}</th>
        </tr>
        <!-- START meetingTypeList{ElectionPeriodID} -->
        <tr>      
            <td class="col_action">
                <img src="views/classic/images/icon/modify.png" alt="{lbl_edit}" onClick = "modifyZobMeetingType('{MeetingTypeID}','{ElectionPeriodID}','{MeetingName}','{Members}','modify');"/>
                <a href="index.php?page=zob/meetingtype/delete/{MeetingTypeID}/{ElectionPeriodID}"><img src="views/classic/images/icon/delete.png" alt="{lbl_delete}" onclick="return ConfirmDelete();"/></a>
                <img src="views/classic/images/icon/contact.png" alt="Seznam členů" onClick="document.getElementById('memberCard{MeetingTypeID}').style.display = 'block';"/>
            </td>
            <td class="col_fullname">
                {MeetingName}
                <div id ="memberCard{MeetingTypeID}" style="display:none;">
                    {memberCard}
                </div >
            </td>
            <td colspan="2" class="col_text center">{Members}</td>
        </tr>
        <!-- END meetingTypeList{ElectionPeriodID} -->
        <form action="index.php?page=zob/meetingtype/add"  method="post">
            <tr>
                <td>
                    <img src="views/classic/images/nav/addNew.png" alt="{lbl_New}" onClick = "modifyZobMeetingType('','','','','add');"/>                    
                </td>
                <td>
                    <input type="text" id="fieldMtMeetingName{ElectionPeriodID}" class="value col_function" name="MeetingName" value="" autofocus required/>
                </td>
                <td>
                    <input type="text" id="fieldMtMembers{ElectionPeriodID}" class="value center" name="Members" value="0" onClick = "validateCheckbox( this );" required/>
                    </td>
                <td>
                    <input type="hidden" id="fieldMtMeetingTypeID{ElectionPeriodID}" name="MeetingTypeID" value="{MeetingTypeID}">
                    <input type="hidden" id="fieldMtElectionPeriodID{ElectionPeriodID}" name="ElectionPeriodID" value="{ElectionPeriodID}">
                    <input type="hidden" id="fieldMtAction{ElectionPeriodID}" name="action" value="add">
                    <input type="submit" name="submitEditCard" class="action_button" value="{lbl_Save}">
                </td>
            </tr>
        </form>
    </table>
</div>
