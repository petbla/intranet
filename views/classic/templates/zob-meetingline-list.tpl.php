<h1 id="header">{Header}</h1>
<p>
    <a href="index.php?page=zob/adv/{MeetingID}" title="Zápis jednání"><img src="views/classic/images/nav/meeting48.png" /></a>
    <img src="views/classic/images/nav/line48.png"/>
    <a href="index.php?page=zob/xxx" title="Prezentace"><img src="views/classic/images/nav/present48.png" /></a>        
    <img src="views/classic/images/nav/line48.png"/>
    <a href="index.php?page=zob/xxx" title="Tisk zápisu"><img src="views/classic/images/nav/printZ48.png" /></a>
    <img src="views/classic/images/nav/line48.png"/>
    <a href="index.php?page=zob/xxx" title="Tisk usnesení"><img src="views/classic/images/nav/printU48.png" /></a>
    <img src="views/classic/images/nav/line48.png"/>
    <a href="index.php?page=document/list/{ParentID}" title="Složka"><img src="views/classic/images/nav/folder48.png" /></a>
</p>
<a href="index.php?page=zob/meeting/list/{MeetingTypeID}" id="closePage" class="button"><span class="action_close">{lbl_Close}</span></a>
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
    
    <table>
        <tr style="vertical-align: top;background-color: #fff">
            <td>
                <table class="table-child">
                    <tr>
                        <th style="width:100px;">....................</th>
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
                        <td class="col_code200" MeetingLineID="{MeetingLineID}" onClick="document.getElementById('editMeetingLine{MeetingLineID}').style.display='block';">
                            <span MeetingLineID="{MeetingLineID}" class="pointer">
                            {LineType}&nbsp;&nbsp;&nbsp;
                            </span>
                        </td>
                        <td class="col_code200" MeetingLineID="{MeetingLineID}">
                            <span MeetingLineID="{MeetingLineID}">
                                <a href="index.php?page=zob/meetingline/moveup/{MeetingID}/{MeetingLineID}"><img src="views/classic/images/icon/moveup.png" title="Posun bodu nahorů"/></a>
                                <b>{LineNo}{LineNo2}</b>&nbsp;
                                <a href="index.php?page=zob/meetingline/movedown/{MeetingID}/{MeetingLineID}"><img src="views/classic/images/icon/movedown.png" title="Posun bodu dolů"/></a>
                            </span>
                        </td>
                        <td colspan="2" class="value {bold} pointer" MeetingLineID="{MeetingLineID}" onClick="document.getElementById('editMeetingLine{MeetingLineID}').style.display='block';">{Title}</td>
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
            </td>
            <td>
                <table class="table-child" style="width:70%;">
                    <tr>
                        <th colspan="3">
                            <h2 ondrop="dropattachment(event)" ondragover="allowDrop(event)" MeetingLineID="0">
                                <img src="views/classic/images/icon/attachment.png" title="{lbl_attachment}" style="width:24px;"/>
                                Přílohy jednání
                            </h2>
                        </th>
                    </tr>
                    <tr>
                        <th style="width:100px;">.................</th>
                        <th></th>
                        <th></th>
                    </tr>
                    <!-- START meetingattachmentListNo0 -->
                    <tr draggable="true" ondragstart="dragattachment(event)">      
                        <td class="col_action">
                            <img src="views/classic/images/icon/modify.png" title="{lbl_edit}" id="{AttachmentID}" MeetingLineID="0" dmsClassName="{dmsClassName}""/>
                            <a href="index.php?page=zob/meetingattachment/delete/{AttachmentID}"><img src="views/classic/images/icon/delete.png" title="{lbl_Delete}" onclick="return ConfirmDelete();"/></a>
                        </td>
                        <td colspan="2" class="col_name">
                            <span>
                                <a href="" SET_HREF AttachmentID="{AttachmentID}" id="{ID}" table="dmsentry" name="{Name}" type="{Type}" url="">{Description}</a>                
                            </span>
                        </td>
                    </tr>
                    <!-- END meetingattachmentListNo0 -->
                    <form action="index.php?page=document/addFiles" method="POST" enctype="multipart/form-data">
                        <tr>
                            <td>
                                <img src="views/classic/images/nav/addNew.png" alt="{lbl_New}" />                    
                            </td>
                            <td>
                                <input type="file" name="fileToUpload[]" id="fileToUpload" multiple class="action" >  
                            </td>
                            <td>
                                <input type="image" src="views/classic/images/nav/upload.png" name="submit" id="submitAddFile">
                                <input type="hidden" name="ParentID" value="{ParentID}">
                            </td>
                        </tr>
                    </form>
                </table>

            </td>
        </tr>
    </table>
                        
</div>
<a href="#header" class="button"><span class="action_close">Nahorů</span></a>
<a href="index.php?page=zob/meeting/list/{MeetingTypeID}" class="button"><span class="action_close">{lbl_Close}</span></a>
<script>window.location = "#page_header";</script>
