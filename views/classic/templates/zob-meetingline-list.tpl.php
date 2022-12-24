<div id="pagecounter">
    {navigate_menu}
</div>
<h1>{Header}</h1>
<a href ="index.php?page=zob/meetingtype/list/{MeetingTypeID}" class="button"><span class="action_close">{lbl_Close}</span></a>
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
            <th>{lbl_LineType}</th>
            <th>{lbl_LineNo}</th>
            <th colspan="2">{lbl_MeetingPointText}</th>
        </tr>
        <!-- START meetinglineList -->
        <tr class="{lineclass}">      
            <td class="col_action">
                <img src="views/classic/images/icon/modify.png" title="{lbl_edit}" id="{MeetingID}" dmsClassName="{dmsClassName}""/>
                <a href="index.php?page=zob/meetingline/delete/{MeetingID}/{MeetingLineID}"><img src="views/classic/images/icon/delete.png" title="{lbl_Delete}" onclick="return ConfirmDelete();"/></a>
            </td>
            <td class="col_code">{LineType}</td>
            <td class="col_name">
                <span>
                    <a href="index.php?page=zob/meetingline/moveup/{MeetingID}/{MeetingLineID}"><img src="views/classic/images/icon/moveup.png" title="Posun bodu nahorů"/></a>&nbsp;{EntryNo}/{Year}/{LineNo}&nbsp;<a href="index.php?page=zob/meetingline/movedown/{MeetingID}/{MeetingLineID}"><img src="views/classic/images/icon/movedown.png" title="Posun bodu dolů"/></a>
                </span>
            </td>
            <td colspan="2" class="col_name">{Title}</td>
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
                        <option>Doplňující bod</option>
                    </select>
                </td>
                <td></td>
                <td>
                    <input type="text" id="fielTitle" class="value col_fullname" name="Title" value="" placeholder autofocus/>
                </td>
                <td>
                    <input type="hidden" id="fieldMeetingID" name="MeetingID" value="{MeetingID}">
                    <input type="hidden" id="fieldMeetingLineID" name="MeetingLineID" value="{MeetingLineID}">
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
