<h1>{Header}</h1>
<a href ="index.php?page=zob/meeting/list/{MeetingTypeID}" class="button"><span class="action_close">{lbl_Close}</span></a>
<div id="DocumentItems">
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
    <table class="table-child">
        <tr>
            <th style="width:100px;">.................</th>
            <th>{lbl_LineType}</th>
            <th>{lbl_LineNo}</th>
            <th colspan="2">{lbl_MeetingPointText}</th>
        </tr>
        <!-- START meetinglineList -->
        <tr ondrop="dropattachment(event)" ondragover="allowDrop(event)">      
            <td class="col_action">
                <img src="views/classic/images/icon/modify.png" title="{lbl_edit}" id="{MeetingLineID}" dmsClassName="{dmsClassName}"/>
                <a href="index.php?page=zob/meetingline/delete/{MeetingID}/{MeetingLineID}"><img src="views/classic/images/icon/delete.png" title="{lbl_Delete}" onclick="return ConfirmDelete();"/></a>
                <img src="views/classic/images/icon/attachment.png" title="{lbl_attachment}" MeetingLineID="{MeetingLineID}"/>
                <small>(<b>{Attachments}</b>)</small>
            </td>
            <td class="col_code200" MeetingLineID="{MeetingLineID}">
                <span MeetingLineID="{MeetingLineID}">
                {LineType}&nbsp;&nbsp;&nbsp;
                </span>
            </td>
            <td class="col_code200" MeetingLineID="{MeetingLineID}">
                <span MeetingLineID="{MeetingLineID}">
                    <a href="index.php?page=zob/meetingline/moveup/{MeetingID}/{MeetingLineID}"><img src="views/classic/images/icon/moveup.png" title="Posun bodu nahorů"/></a>
                    &nbsp;{EntryNo}/{Year}/<b>{LineNo}</b>&nbsp;
                    <a href="index.php?page=zob/meetingline/movedown/{MeetingID}/{MeetingLineID}"><img src="views/classic/images/icon/movedown.png" title="Posun bodu dolů"/></a>
                </span>
            </td>
            <td colspan="2" class="value {bold}" MeetingLineID="{MeetingLineID}">{Title}</td>
        </tr>
        <tr>
        </tr>
        <tr>
            <td colspan="5" style="margin:0;padding:0;">
                <div id="editMeetingLine{MeetingLineID}" style="margin:0 100px; display:none;">
                    <span class="action_close" onclick="document.getElementById('editMeetingLine{MeetingLineID}').style.display = 'none';">{lbl_Close}</span>
                    <br>
                    {editdMeetingLine}
                </div>
            </td>
        </tr>
        <!-- END meetinglineList -->
        <form action="index.php?page=zob/meetingline/add"  method="post">
            <tr>
                <td>
                    <img src="views/classic/images/nav/addNew.png" alt="{lbl_New}" />                    
                </td>
                <td>
                    <select id="fielLineType" class="value" name="LineType" value="" onchange="this.setAttribute('value',this.options[this.selectedIndex].text);">
                        <option>Bod</option>
                        <option>Podbod</option>
                        <option>Doplňující bod</option>
                    </select>
                </td>
                <td colspan="2">
                    <input type="text" id="fielTitle" class="col_fullname" name="Title" value="" placeholder autofocus/>
                </td>
                <td>
                    <input type="hidden" id="fieldMeetingID" name="MeetingID" value="{MeetingID}">
                    <input type="hidden" id="fieldMeetingLineID" name="MeetingLineID" value="">
                    <input type="hidden" id="fieldAction" name="action" value="add">
                    <input type="submit" id="butttonTemplate" isEmpty="{isEmpty}" name="submitTemplate" class="action_button" value="Vložit ze šablony" style="display:none;">
                    <input type="submit" name="submitEditCard" class="action_button" value="{lbl_Save}">
                    <script>
                        var e;
                        e = document.getElementById('butttonTemplate');
                        if(e.getAttribute('isEmpty') == 1){
                            e.style.display = 'block';
                        }
                    </script>
                </td>
            </tr>
        </form>
    </table>
</div>
<h2 ondrop="dropattachment(event)" ondragover="allowDrop(event)" MeetingLineID="0">
    <img src="views/classic/images/icon/attachment.png" title="{lbl_attachment}" style="width:24px;"/>
    Přílohy jednání
</h2>
<div id="DocumentItems">
    <table class="table-child" style="width:70%;">
        <tr>
            <th style="width:100px;">.................</th>
            <th>{lbl_Attachment}</th>
            <th></th>
        </tr>
        <!-- START meetingattachmentListNo0 -->
        <tr AttachmentID="{AttachmentID}" draggable="true" ondragstart="dragattachment(event)">      
            <td class="col_action">
                <img src="views/classic/images/icon/modify.png" title="{lbl_edit}" id="{AttachmentID}" MeetingLineID="0" dmsClassName="{dmsClassName}""/>
                <a href="index.php?page=zob/meetingattachment/delete/{AttachmentID}"><img src="views/classic/images/icon/delete.png" title="{lbl_Delete}" onclick="return ConfirmDelete();"/></a>
            </td>
            <td class="col_name">
                <span>
                    {Description}
                </span>
            </td>
            <td></td>
        </tr>
        <!-- END meetingattachmentListNo0 -->
        <form action="index.php?page=zob/meetingattachment"  method="post">
            <tr>
                <td>
                    <img src="views/classic/images/nav/addNew.png" alt="{lbl_New}" />                    
                </td>
                <td>
                    <input type="text" id="fielDescription0" class="value col_fullname" name="Description" value="" placeholder autofocus/>
                </td>
                <td>
                    <input type="hidden" id="fieldAttMeetingID0" name="MeetingID" value="{MeetingID}">
                    <input type="hidden" id="fieldAttMeetingLineID0" name="MeetingLineID" value="0">
                    <input type="hidden" id="fieldAttAction0" name="action" value="add">
                    <input type="submit" name="submitEditCard" class="action_button" value="{lbl_Save}">
                    <input type="hidden" id="activeMeetingLine" name="activeMemberType" value="{activeMeetingLineID}">
                </td>
            </tr>
        </form>
    </table>
</div>
