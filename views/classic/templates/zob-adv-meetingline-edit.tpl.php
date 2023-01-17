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
                    <input type="Text" class="col_fullname big" value="{Title}" name="Title" pkID="{MeetingLineID}" table="meetingline" onchange="wsUpdate(this);">
                </td>
                <td>
                    <label class="col_title">{lbl_Presenter}</label>
                    <input type="Text" class="col_name" value="{Presenter}" name="Presenter" pkID="{MeetingLineID}" table="meetingline" onchange="wsUpdate(this);">                    
                </td>
            </tr>
            <tr>
                <td></td>
                <td colspan="2">
                    <textarea data-autoresize id="meetinglineContent{MeetingLineID}" name="Content" rows="5" cols="160" value="" class="autosize par" pkID="{MeetingLineID}" table="meetingline" onchange="wsUpdate(this);">{Content}</textarea>
                </td>
            </tr>
            <tr>
                <td></td>
                <td colspan="2">
                    <label class="col_title big">{lbl_Discussion}</label>
                    <img src="views/classic/images/icon/arrowup.png" id="arrowupDiscussion{MeetingLineID}" title="Sbalit" onClick="document.getElementById('meetinglineDiscussion{MeetingLineID}').style.display = 'none'; this.style.display='none';document.getElementById('arrowdownDiscussion{MeetingLineID}').style.display='';"/>
                    <img src="views/classic/images/icon/arrowdown.png" id="arrowdownDiscussion{MeetingLineID}" title="Rozbalit" onClick="document.getElementById('meetinglineDiscussion{MeetingLineID}').style.display = 'block'; this.style.display='none';document.getElementById('arrowupDiscussion{MeetingLineID}').style.display='';" style="display:none;"/>
                    <br>                   
                    <textarea  id="meetinglineDiscussion{MeetingLineID}" name="Discussion" rows="3" cols="160" value="" class="autosize par" pkID="{MeetingLineID}" table="meetingline" onchange="wsUpdate(this);">{Discussion}</textarea>
                    <script>
                        if('{isDiscussion}' == '0'){
                            document.getElementById('meetinglineDiscussion{MeetingLineID}').style.display = 'none';
                            document.getElementById('arrowdownDiscussion{MeetingLineID}').style.display='';
                            document.getElementById('arrowupDiscussion{MeetingLineID}').style.display='none';
                        }
                    </script>
                </td>
            </tr>
            <tr>
                <td></td>
                <td colspan="2">
                    <label class="col_title big">{lbl_DraftResolution}</label>
                    <img src="views/classic/images/icon/arrowdown.png" id="arrowdownDraftResolution{MeetingLineID}" title="Rozbalit" onClick="document.getElementById('meetinglineDraftResolution{MeetingLineID}').style.display = 'block'; this.style.display='none';document.getElementById('arrowupDraftResolution{MeetingLineID}').style.display='';" style="display:none;"/>
                    <img src="views/classic/images/icon/arrowup.png" id="arrowupDraftResolution{MeetingLineID}" title="Sbalit" onClick="document.getElementById('meetinglineDraftResolution{MeetingLineID}').style.display = 'none'; this.style.display='none';document.getElementById('arrowdownDraftResolution{MeetingLineID}').style.display='';"/>
                    &nbsp;&nbsp;<img src="views/classic/images/icon/copyFrom.png" style="cursor: pointer;" title="Kopie obsahu" name="DraftResolution" namefrom="Content" table="meetingline" pkID="{MeetingLineID}" onClick="wsCopyFrom(this);"/>
                    <br>                   
                    <textarea  id="meetinglineDraftResolution{MeetingLineID}" name="DraftResolution" rows="3" cols="160" value="" class="autosize par" pkID="{MeetingLineID}" table="meetingline" onchange="wsUpdate(this);">{DraftResolution}</textarea>
                    <script>
                        if('{isDraftResolution}' == '0'){
                            document.getElementById('meetinglineDraftResolution{MeetingLineID}').style.display = 'none';
                            document.getElementById('arrowdownDraftResolution{MeetingLineID}').style.display='';
                            document.getElementById('arrowupDraftResolution{MeetingLineID}').style.display='none';
                        }
                    </script>
                </td>
            </tr>
            <tr>
                <td></td>
                <td colspan="2">
                    <label class="col_title">{lbl_Vote}</label>
                    <input type="checkbox" id="meetinglineVote{MeetingLineID}" name="Vote" value="{Vote}" pkID="{MeetingLineID}" table="meetingline" onClick = "validateCheckboxVote( this, {MeetingLineID} );wsRefreshMeetingline(this);">
                    <label class="col_title">{lbl_VoteFor}</label>&nbsp;<input type="text" id="meetinglineVoteFor{MeetingLineID}" name="VoteFor" value="{VoteFor}" class="col_code" pkID="{MeetingLineID}" table="meetingline" onchange="wsUpdate(this);wsRefreshMeetingline(this);">
                    &nbsp;&nbsp;&nbsp;
                    <label class="col_title">{lbl_VoteAgainst}</label>&nbsp;<input type="text" id="meetinglineVoteAgainst{MeetingLineID}" name="VoteAgainst" value="{VoteAgainst}" class="col_code" pkID="{MeetingLineID}" table="meetingline" onchange="wsUpdate(this);wsRefreshMeetingline(this);">
                    &nbsp;&nbsp;&nbsp;
                    <label class="col_title">{lbl_VoteDelayed}</label>&nbsp;<input type="text" id="meetinglineVoteDelayed{MeetingLineID}" name="VoteDelayed" value="{VoteDelayed}" class="col_code" pkID="{MeetingLineID}" table="meetingline" onchange="wsUpdate(this);wsRefreshMeetingline(this);">
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
            <!-- START meetinglinecontent{MeetingLineID} -->
            <tr id="content{MeetingLineID}">
                <td>               
                </td>
                <td colspan="2">
                    <label class="col_title big">&nbsp;{EntryNo}/{Year}/{LineNo}{LineNo2}&nbsp;....{con_LineNo}) {lbl_Content}</label>
                    <br>                   
                    <textarea  name="Content" rows="3" cols="160" value="" class="autosize par" pkID="{con_ContentID}" table="meetinglinecontent" onchange="wsUpdate(this);">{con_Content}</textarea>
                    <br>

                    <label class="col_title big">{con_LineNo}) {lbl_Discussion}</label>
                    <img src="views/classic/images/icon/arrowdown.png" id="arrowdownDiscussionContent{con_ContentID}" title="Rozbalit" onClick="document.getElementById('meetinglinecontentDiscussion{con_ContentID}').style.display = 'block'; this.style.display='none';document.getElementById('arrowupDiscussionContent{con_ContentID}').style.display='';" style="display:none;"/>
                    <img src="views/classic/images/icon/arrowup.png" id="arrowupDiscussionContent{con_ContentID}" title="Sbalit" onClick="document.getElementById('meetinglinecontentDiscussion{con_ContentID}').style.display = 'none'; this.style.display='none';document.getElementById('arrowdownDiscussionContent{con_ContentID}').style.display='';"/>
                    <br>                   
                    <textarea id="meetinglinecontentDiscussion{con_ContentID}" name="Discussion" rows="3" cols="160" value="" class="autosize par" pkID="{con_ContentID}" table="meetinglinecontent" onchange="wsUpdate(this);">{con_Discussion}</textarea>                    
                    <script>
                        if('{con_isDiscussion}' == '0'){
                            document.getElementById('meetinglinecontentDiscussion{con_ContentID}').style.display = 'none';
                            document.getElementById('arrowdownDiscussionContent{con_ContentID}').style.display='';
                            document.getElementById('arrowupDiscussionContent{con_ContentID}').style.display='none';
                        }
                    </script>
                    <br>

                    <label class="col_title big">{con_LineNo}) {lbl_DraftResolution}</label>
                    <img src="views/classic/images/icon/arrowdown.png" id="arrowdownDraftResolutionContent{con_ContentID}" title="Rozbalit" onClick="document.getElementById('meetinglinecontentDraftResolution{con_ContentID}').style.display = 'block'; this.style.display='none';document.getElementById('arrowupDraftResolutionContent{con_ContentID}').style.display='';" style="display:none;"/>
                    <img src="views/classic/images/icon/arrowup.png" id="arrowupDraftResolutionContent{con_ContentID}" title="Sbalit" onClick="document.getElementById('meetinglinecontentDraftResolution{con_ContentID}').style.display = 'none'; this.style.display='none';document.getElementById('arrowdownDraftResolutionContent{con_ContentID}').style.display='';"/>
                    &nbsp;&nbsp;<img src="views/classic/images/icon/copyFrom.png" style="cursor: pointer;" title="Kopie obsahu" name="DraftResolution" namefrom="Content" table="meetinglinecontent" pkID="{con_ContentID}" onClick="wsCopyFrom(this);"/>
                    <br>                   
                    <textarea id="meetinglinecontentDraftResolution{con_ContentID}" name="DraftResolution" rows="3" cols="160" value="" class="autosize par" pkID="{con_ContentID}" table="meetinglinecontent" onchange="wsUpdate(this);">{con_DraftResolution}</textarea>
                    <script>
                        if('{con_isDraftResolution}' == '0'){
                            document.getElementById('meetinglinecontentDraftResolution{con_ContentID}').style.display = 'none';
                            document.getElementById('arrowdownDraftResolutionContent{con_ContentID}').style.display='';
                            document.getElementById('arrowupDraftResolutionContent{con_ContentID}').style.display='none';
                        }
                    </script>
                    <br>


                    <label class="col_title">{lbl_Vote}</label>
                    <input type="checkbox" id="meetinglinecontentVote{con_ContentID}" name="Vote" value="{con_Vote}" pkID="{con_ContentID}" table="meetinglinecontent" onClick = "validateCheckboxVote( this, {con_ContentID} );wsRefreshMeetinglinecontent(this);">
                    <label class="col_title">{lbl_VoteFor}</label>&nbsp;<input type="text" id="meetinglinecontentVoteFor{con_ContentID}" name="VoteFor" value="{con_VoteFor}" class="col_code" pkID="{con_ContentID}" table="meetinglinecontent" onchange="wsUpdate(this);wsRefreshMeetinglinecontent(this);">
                    &nbsp;&nbsp;&nbsp;
                    <label class="col_title">{lbl_VoteAgainst}</label>&nbsp;<input type="text" id="meetinglinecontentVoteAgainst{con_ContentID}" name="VoteAgainst" value="{con_VoteAgainst}" class="col_code" pkID="{con_ContentID}" table="meetinglinecontent" onchange="wsUpdate(this);wsRefreshMeetinglinecontent(this);">
                    &nbsp;&nbsp;&nbsp;
                    <label class="col_title">{lbl_VoteDelayed}</label>&nbsp;<input type="text" id="meetinglinecontentVoteDelayed{con_ContentID}" name="VoteDelayed" value="{con_VoteDelayed}" class="col_code" pkID="{con_ContentID}" table="meetinglinecontent" onchange="wsUpdate(this);wsRefreshMeetinglinecontent(this);">
                    <script>
                        var e;
                        e = document.getElementById('meetinglinecontentVote{con_ContentID}');
                        if(e){
                            if(e.getAttribute('value') == '1' ){
                                e.checked = true;
                                document.getElementById('meetinglinecontentVoteFor{con_ContentID}').disabled = false;
                                document.getElementById('meetinglinecontentVoteAgainst{con_ContentID}').disabled = false;
                                document.getElementById('meetinglinecontentVoteDelayed{con_ContentID}').disabled = false;
                            }else{
                                document.getElementById('meetinglinecontentVoteFor{con_ContentID}').disabled = true;
                                document.getElementById('meetinglinecontentVoteAgainst{con_ContentID}').disabled = true;
                                document.getElementById('meetinglinecontentVoteDelayed{con_ContentID}').disabled = true;
                            }
                        }
                    </script>
                    <br>
                </td>
            </tr>
            <script>
                if('{isNextContent}' == '0')
                    document.getElementById('content{MeetingLineID}').style.display = 'none';
            </script>            
            <!-- END meetinglinecontent{MeetingLineID} -->
            <tr>
                <td></td>
                <td colspan="2">
                    <hr>
                    <a href="index.php?page=zob/adv/meetinglinecontent/add/{MeetingLineID}" id="anchor{MeetingLineID}" class="action" ><img src="views/classic/images/nav/addNew.png" title="Přidat obsah" /> Přidat obsah</a>
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

