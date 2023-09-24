<form id="MeetingLineID{MeetingLineID}" action="" method="post">
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
                            <select id="fielLineType{MeetingLineID}" class="value" name="LineType" value="{LineType}" pkID="{MeetingLineID}" table="meetingline" onchange="this.setAttribute('value',this.options[this.selectedIndex].text); wsUpdate(this);">
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
                            <input type="Text" name="Title" class="col_fullname" value="{Title}" pkID="{MeetingLineID}" table="meetingline" onchange="wsUpdate(this);">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label class="col_title">{lbl_Presenter}</label>
                        </td>
                        <td>
                            <input type="Text" name="Presenter" class="col_name" value="{Presenter}" pkID="{MeetingLineID}" table="meetingline" onchange="wsUpdate(this);">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label class="col_title">{lbl_Vote}</label>
                        </td>
                        <td>
                            <input type="checkbox" id='meetinglineVote{MeetingLineID}' name="Vote" table="meetingline" value="{Vote}"  pkID="{MeetingLineID}" table="meetingline" onClick = "validateCheckboxVote( this, {MeetingLineID} );"><br>
                            <label class="col_title">{lbl_VoteFor}</label>&nbsp;
                            <input type="text" id='meetinglineVoteFor{MeetingLineID}' name="VoteFor" value="{VoteFor}" class="col_code" pkID="{MeetingLineID}" table="meetingline" onchange="wsUpdate(this);">
                            &nbsp;&nbsp;&nbsp;
                            <label class="col_title">{lbl_VoteAgainst}</label>&nbsp;
                            <input type="text" id='meetinglineVoteAgainst{MeetingLineID}' name="VoteAgainst" value="{VoteAgainst}" class="col_code" pkID="{MeetingLineID}" table="meetingline" onchange="wsUpdate(this);">
                            &nbsp;&nbsp;&nbsp;
                            <label class="col_title">{lbl_VoteDelayed}</label>&nbsp;
                            <input type="text" id='meetinglineVoteDelayed{MeetingLineID}' name="VoteDelayed" value="{VoteDelayed}" class="col_code" pkID="{MeetingLineID}" table="meetingline" onchange="wsUpdate(this);">
                            <script>
                                var e;
                                e = document.getElementById('meetinglineVote{MeetingLineID}');
                                if(e){
                                    if(e.getAttribute('value') == '1' ){
                                        e.checked = true;
                                        document.getElementById('meetinglineVoteFor{MeetingLineID}').disabled = false;
                                        document.getElementById('meetinglineVoteAgainst{MeetingLineID}').disabled = false;
                                        document.getElementById('meetinglineVoteDelayed{MeetingLineID}').disabled = false;
                                    }else{
                                        document.getElementById('meetinglineVoteFor{MeetingLineID}').disabled = true;
                                        document.getElementById('meetinglineVoteAgainst{MeetingLineID}').disabled = true;
                                        document.getElementById('meetinglineVoteDelayed{MeetingLineID}').disabled = true;
                                    }
                                }
                            </script>

                        </td>
                    </tr>
                </table>
                </td>
            </tr>
            <tr>
                <td>
                    <label class="col_title big">{lbl_Content}</label><br>                   
                    <textarea  id="meetinglineContent{MeetingLineID}" name="Content" rows="10" cols="140" value="" class="value" pkID="{MeetingLineID}" table="meetingline" onchange="wsUpdate(this);">{Content}</textarea>
                </td>
            </tr>
            <tr>
                <td>
                    <label class="col_title big">{lbl_Discussion}</label>
                    <img src="views/classic/images/icon/arrowdown.png" id="arrowdownDiscussion{MeetingLineID}" title="Rozbalit" onClick="document.getElementById('meetinglineDiscussion{MeetingLineID}').style.display = 'block'; this.style.display='none';document.getElementById('arrowupDiscussion{MeetingLineID}').style.display='';"/>
                    <img src="views/classic/images/icon/arrowup.png" id="arrowupDiscussion{MeetingLineID}" title="Sbalit" onClick="document.getElementById('meetinglineDiscussion{MeetingLineID}').style.display = 'none'; this.style.display='none';document.getElementById('arrowdownDiscussion{MeetingLineID}').style.display='';" style="display:none;"/>
                    <br>                   
                    <textarea  id="meetinglineDiscussion{MeetingLineID}" name="Discussion" rows="10" cols="140" value="" class="value" pkID="{MeetingLineID}" table="meetingline" onchange="wsUpdate(this);" style="display:none;">{Discussion}</textarea>
                </td>
            </tr>
            <tr>
                <td>
                    <label class="col_title big">{lbl_DraftResolution}</label>
                    <img src="views/classic/images/icon/arrowdown.png" id="arrowdownDraftResolution{MeetingLineID}" title="Rozbalit" onClick="document.getElementById('meetinglineDraftResolution{MeetingLineID}').style.display = 'block'; this.style.display='none';document.getElementById('arrowupDraftResolution{MeetingLineID}').style.display='';"/>
                    <img src="views/classic/images/icon/arrowup.png" id="arrowupDraftResolution{MeetingLineID}" title="Sbalit" onClick="document.getElementById('meetinglineDraftResolution{MeetingLineID}').style.display = 'none'; this.style.display='none';document.getElementById('arrowdownDraftResolution{MeetingLineID}').style.display='';" style="display:none;"/>
                    <br>                   
                    <textarea  id="meetinglineDraftResolution{MeetingLineID}" name="DraftResolution" rows="10" cols="140" value="" class="value" pkID="{MeetingLineID}" table="meetingline" onchange="wsUpdate(this);" style="display:none;">{DraftResolution}</textarea>
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
    <tr draggable="true" ondragstart="dragattachment(event)">
        <td class="col_action">
            <a href="index.php?page=zob/meetingattachment/delete/{AttachmentID}"><img src="views/classic/images/icon/delete.png" title="{lbl_Delete}" onclick="return ConfirmDelete();"/></a>
        </td>
        <td class="col_name">
            <span>
                <a href="" SET_HREF AttachmentID="{AttachmentID}" id="{ID}" table="dmsentry" name="{Name}" type="{Type}" url="">{Description}</a>                
            </span>
        </td>
        <td></td>
    </tr>
    <!-- END meetingattachmentList{MeetingLineID} -->
    <tr>
        <a href="index.php?page=document/choice/list/meetingline/{MeetingLineID}">Přidat přílohu</a>
    </tr>
</table>
<br>
