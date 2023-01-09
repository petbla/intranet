<fieldset style="padding:0; border:0;">
    <table>
        <form id="MeetingLineID{MeetingLineID}" action="index.php?page=zob/adv/meetingline/modify" method="post">
            <tr>
                <td>
                    <a href="#header" class="button" title="Nahorů"><img src="views/classic/images/icon/arrowup.png"></a>                   
                    <label class="col_title">{lbl_LineType}</label>
                    <select class="value" name="LineType" value="{LineType}" pkID="{MeetingLineID}" table="meetingline" onchange="this.setAttribute('value',this.options[this.selectedIndex].text); wsUpdate(this);">
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
                <td>
                    <input type="Text" class="col_fullname big" value="{Title}" name="Title" pkID="{MeetingLineID}" table="meetingline" onchange="wsUpdate(this);wsRefreshMeetingline(this);">
                </td>
                <td>
                    <label class="col_title">{lbl_Presenter}</label>
                    <input type="Text" class="col_name" value="{Presenter}" name="Presenter" pkID="{MeetingLineID}" table="meetingline" onchange="wsUpdate(this);">                    
                </td>
            </tr>
            <tr>
                <td></td>
                <td colspan="2">
                    <label class="col_title">{lbl_Vote}</label>
                    <input type="checkbox" id="fieldVote{MeetingLineID}" name="Vote" value="{Vote}" pkID="{MeetingLineID}" table="meetingline" onClick = "validateCheckboxVote( this, {MeetingLineID} );wsUpdate(this);wsRefreshMeetingline(this);">
                    <label class="col_title">{lbl_VoteFor}</label>&nbsp;<input type="text" id="fieldVoteFor{MeetingLineID}" name="VoteFor" value="{VoteFor}" class="col_code" pkID="{MeetingLineID}" table="meetingline" onchange="wsUpdate(this);wsRefreshMeetingline(this);">
                    &nbsp;&nbsp;&nbsp;
                    <label class="col_title">{lbl_VoteAgainst}</label>&nbsp;<input type="text" id="fieldVoteAgainst{MeetingLineID}" name="VoteAgainst" value="{VoteAgainst}" class="col_code" pkID="{MeetingLineID}" table="meetingline" onchange="wsUpdate(this);wsRefreshMeetingline(this);">
                    &nbsp;&nbsp;&nbsp;
                    <label class="col_title">{lbl_VoteDelayed}</label>&nbsp;<input type="text" id="fieldVoteDelayed{MeetingLineID}" name="VoteDelayed" value="{VoteDelayed}" class="col_code" pkID="{MeetingLineID}" table="meetingline" onchange="wsUpdate(this);wsRefreshMeetingline(this);">
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
                <td colspan="2">
                    <label class="col_title big">{lbl_Content}</label><br>                   
                    <textarea  id="fieldContent{MeetingLineID}" name="Content" rows="5" cols="160" value="" class="value par" pkID="{MeetingLineID}" table="meetingline" onchange="wsUpdate(this);">{Content}</textarea>
                </td>
            </tr>
            <tr>
                <td></td>
                <td colspan="2">
                    <label class="col_title big">{lbl_Discussion}</label>
                    <img src="views/classic/images/icon/arrowdone.png" id="arrowdoneDiscussion{MeetingLineID}" title="Rozbalit" onClick="document.getElementById('fieldDiscussion{MeetingLineID}').style.display = 'block'; this.style.display='none';document.getElementById('arrowupDiscussion{MeetingLineID}').style.display='';"/>
                    <img src="views/classic/images/icon/arrowup.png" id="arrowupDiscussion{MeetingLineID}" title="Sbalit" onClick="document.getElementById('fieldDiscussion{MeetingLineID}').style.display = 'none'; this.style.display='none';document.getElementById('arrowdoneDiscussion{MeetingLineID}').style.display='';" style="display:none;"/>
                    <br>                   
                    <textarea  id="fieldDiscussion{MeetingLineID}" name="Discussion" rows="3" cols="160" value="" class="value par" style="display:none;" pkID="{MeetingLineID}" table="meetingline" onchange="wsUpdate(this);">{Discussion}</textarea>
                </td>
            </tr>
            <tr>
                <td></td>
                <td colspan="2">
                    <label class="col_title big">{lbl_DraftResolution}</label>
                    <img src="views/classic/images/icon/arrowdone.png" id="arrowdoneDraftResolution{MeetingLineID}" title="Rozbalit" onClick="document.getElementById('fieldDraftResolution{MeetingLineID}').style.display = 'block'; this.style.display='none';document.getElementById('arrowupDraftResolution{MeetingLineID}').style.display='';"/>
                    <img src="views/classic/images/icon/arrowup.png" id="arrowupDraftResolution{MeetingLineID}" title="Sbalit" onClick="document.getElementById('fieldDraftResolution{MeetingLineID}').style.display = 'none'; this.style.display='none';document.getElementById('arrowdoneDraftResolution{MeetingLineID}').style.display='';" style="display:none;"/>
                    <br>                   
                    <textarea  id="fieldDraftResolution{MeetingLineID}" name="DraftResolution" rows="3" cols="160" value="" class="value par" style="display:none;" pkID="{MeetingLineID}" table="meetingline" onchange="wsUpdate(this);">{DraftResolution}</textarea>
                </td>
            </tr>
        </form>
        <tr>
            <td></td>
            <td colspan="2">
                <!-- START meetingattachmentList{MeetingLineID} -->
                <span AttachmentID="{AttachmentID}" draggable="true" ondragstart="dragattachment(event)" class="blue">
                    <img src="views/classic/images/icon/attachment.png" title="{lbl_attachment}" style="width:24px;"/>
                    <a href="index.php?page=zob/meetingattachment/delete/{AttachmentID}"><img src="views/classic/images/icon/delete.png" title="{lbl_Delete}" onclick="return ConfirmDelete();"/></a>
                    {Description}
                </span>
                <!-- END meetingattachmentList{MeetingLineID} -->
            </td>
        </tr>
    </table>    
</fieldset>

