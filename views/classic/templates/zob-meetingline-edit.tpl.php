<form id="MeetingLineID{MeetingLineID}" action="index.php?page=zob/meetingline/modify" method="post">
    <fieldset style="padding:0; border:0;">
        <table class="edit-card">
            <tr>
                <td>
                <table>
                    <tr>
                        <td>
                            <label class="col_title">{lbl_LineType}</label>
                        </td>
                        <td>
                            <select id="fielLineType{MeetingLineID}" class="value" name="LineType" value="{LineType}" onchange="this.setAttribute('value',this.options[this.selectedIndex].text);">
                                <option id="Bod{MeetingLineID}">Bod</option>
                                <option id="Podbod{MeetingLineID}">Podbod</option>
                                <option id="Doplňující bod{MeetingLineID}">Doplňující bod</option>
                            </select>
                            <script>
                                var e;
                                e = document.getElementById("{LineType}{MeetingLineID}");
                                if(e)
                                    e.setAttribute('selected',true);
                            </script>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label class="col_title">{lbl_Title}</label>
                        </td>
                        <td>
                            <input type="Text" id="fieldTitle{MeetingLineID}" name="Title" class="col_fullname" value="{Title}">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label class="col_title">{lbl_Presenter}</label>
                        </td>
                        <td>
                            <input type="Text" id="fieldPresenter{MeetingLineID}" name="Presenter" class="col_name" value="{Presenter}">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label class="col_title">{lbl_Vote}</label>
                        </td>
                        <td>
                            <input type="checkbox" id="fieldVote{MeetingLineID}" name="Vote" value="{Vote}" onClick = "validateCheckboxVote( this, {MeetingLineID} );"><br>
                            <label class="col_title">{lbl_VoteFor}</label>&nbsp;<input type="text" id="fieldVoteFor{MeetingLineID}" name="VoteFor" value="{VoteFor}" class="col_code">
                            &nbsp;&nbsp;&nbsp;
                            <label class="col_title">{lbl_VoteAgainst}</label>&nbsp;<input type="text" id="fieldVoteAgainst{MeetingLineID}" name="VoteAgainst" value="{VoteAgainst}" class="col_code">
                            &nbsp;&nbsp;&nbsp;
                            <label class="col_title">{lbl_VoteDelayed}</label>&nbsp;<input type="text" id="fieldVoteDelayed{MeetingLineID}" name="VoteDelayed" value="{VoteDelayed}" class="col_code">
                            <script>
                                var e;
                                e = document.getElementById('fieldVote{MeetingLineID}');
                                if(e){
                                    if(e.getAttribute('value') == '1' ){
                                        e.checked = true;
                                        document.getElementById('fieldVoteFor{MeetingLineID}').disabled = false;
                                        document.getElementById('fieldVoteAgainst{MeetingLineID}').disabled = false;
                                        document.getElementById('fieldVoteDelayed{MeetingLineID}').disabled = false;
                                    }else{
                                        document.getElementById('fieldVoteFor{MeetingLineID}').disabled = true;
                                        document.getElementById('fieldVoteAgainst{MeetingLineID}').disabled = true;
                                        document.getElementById('fieldVoteDelayed{MeetingLineID}').disabled = true;
                                    }
                                }
                            </script>

                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <input type="submit" name="submitEditMeeting" class="action_button" value="{lbl_Save}">
                            <input type="hidden" name="MeetingLineID" value="{MeetingLineID}">
                        </td>
                    </tr>    
                </table>
                </td>
            </tr>
            <tr>
                <td>
                    <label class="col_title big">{lbl_Content}</label><br>                   
                    <textarea  id="fieldContent{MeetingLineID}" name="Content" rows="10" cols="140" value="" class="value" onchange="saveFormMeetingLine('{MeetingLineID}');">{Content}</textarea>
                </td>
            </tr>
            <tr>
                <td>
                    <label class="col_title big">{lbl_Discussion}</label>
                    <img src="views/classic/images/icon/arrowdone.png" id="arrowdoneDiscussion{MeetingLineID}" title="Rozbalit" onClick="document.getElementById('fieldDiscussion{MeetingLineID}').style.display = 'block'; this.style.display='none';document.getElementById('arrowupDiscussion{MeetingLineID}').style.display='';"/>
                    <img src="views/classic/images/icon/arrowup.png" id="arrowupDiscussion{MeetingLineID}" title="Sbalit" onClick="document.getElementById('fieldDiscussion{MeetingLineID}').style.display = 'none'; this.style.display='none';document.getElementById('arrowdoneDiscussion{MeetingLineID}').style.display='';" style="display:none;"/>
                    <br>                   
                    <textarea  id="fieldDiscussion{MeetingLineID}" name="Discussion" rows="10" cols="140" value="" class="value" onchange="saveFormMeetingLine('{MeetingLineID}');" style="display:none;">{Discussion}</textarea>
                </td>
            </tr>
            <tr>
                <td>
                    <label class="col_title big">{lbl_DraftResolution}</label>
                    <img src="views/classic/images/icon/arrowdone.png" id="arrowdoneDraftResolution{MeetingLineID}" title="Rozbalit" onClick="document.getElementById('fieldDraftResolution{MeetingLineID}').style.display = 'block'; this.style.display='none';document.getElementById('arrowupDraftResolution{MeetingLineID}').style.display='';"/>
                    <img src="views/classic/images/icon/arrowup.png" id="arrowupDraftResolution{MeetingLineID}" title="Sbalit" onClick="document.getElementById('fieldDraftResolution{MeetingLineID}').style.display = 'none'; this.style.display='none';document.getElementById('arrowdoneDraftResolution{MeetingLineID}').style.display='';" style="display:none;"/>
                    <br>                   
                    <textarea  id="fieldDraftResolution{MeetingLineID}" name="DraftResolution" rows="10" cols="140" value="" class="value" onchange="saveFormMeetingLine('{MeetingLineID}');" style="display:none;">{DraftResolution}</textarea>
                </td>
            </tr>
        </table>
    </fieldset>
</form>
<h2>
    <img src="views/classic/images/icon/attachment.png" title="{lbl_attachment}" style="width:24px;"/>
    Přílohy
</h2>
<table class="table-child" style="width:70%;">
    <tr>
        <th style="width:100px;">.................</th>
        <th>{lbl_Attachment}</th>
        <th></th>
    </tr>
    <!-- START meetingattachmentList{MeetingLineID} -->
    <tr AttachmentID="{AttachmentID}" draggable="true" ondragstart="dragattachment(event)">
        <td class="col_action">
            <img src="views/classic/images/icon/modify.png" title="{lbl_edit}" id="{AttachmentID}" MeetingLineID="{MeetingLineID}" dmsClassName="{dmsClassName}""/>
            <a href="index.php?page=zob/meetingattachment/delete/{AttachmentID}"><img src="views/classic/images/icon/delete.png" title="{lbl_Delete}" onclick="return ConfirmDelete();"/></a>
        </td>
        <td class="col_name">
            <span>
                {Description}
            </span>
        </td>
        <td></td>
    </tr>
    <!-- END meetingattachmentList{MeetingLineID} -->
    <form action="index.php?page=zob/meetingattachment/modify"  method="post">
        <tr>
            <td>
                <img src="views/classic/images/nav/addNew.png" alt="{lbl_New}" />                    
            </td>
            <td>
                <input type="text" id="fielDescription{MeetingLineID}" class="value col_fullname" name="Description" value="" placeholder autofocus/>
            </td>
            <td>
                <input type="hidden" id="fieldAttMeetingID{MeetingLineID}" name="MeetingID" value="{MeetingID}">
                <input type="hidden" id="fieldAttMeetingLineID{MeetingLineID}" name="MeetingLineID" value="{MeetingLineID}">
                <input type="hidden" id="fieldAttAction{MeetingLineID}" name="action" value="add">
                <input type="submit" name="submitEditCard" class="action_button" value="{lbl_Save}">
            </td>
        </tr>
    </form>
</table>
<br>
