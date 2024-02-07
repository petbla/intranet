<h1 id="header">{Header}</h1>
<p>
    <a href="index.php?page=zob/adv/presentation/{MeetingID}" title="Prezentace"><img src="views/classic/images/nav/present48.png" /></a>        
    <img src="views/classic/images/nav/line48.png"/>
    <a href="index.php?page=zob/print/10000/{MeetingID}" title="Tisk zápisu" target="_blank"><img src="views/classic/images/nav/printZ48.png" /></a>
    <img src="views/classic/images/nav/line48.png"/>
    <a href="index.php?page=zob/print/10030/{MeetingID}" title="Tisk usnesení" target="_blank"><img src="views/classic/images/nav/printU48.png" /></a>
    <img src="views/classic/images/nav/line48.png"/>
    <a href="index.php?page=zob/adv/print/invitation/{MeetingID}" title="Odeslat pozvánka" target="_blank"><img src="views/classic/images/nav/email48.png" /></a>
    <img src="views/classic/images/nav/line48.png"/>
    <a href="index.php?page=document/list/{ParentID}" title="Složka"><img src="views/classic/images/nav/folder48.png" /></a>
</p>
<a href="index.php?page=zob/meetingline/list/{MeetingID}" id="closePage" class="button"><span class="action_close">{lbl_Close}</span></a>
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
    <table class="table-child-adv">
        <tr>
            <th>{lbl_LineNo}</th>
            <th colspan="2">{lbl_MeetingPointText}</th>
            <th style="width:100px;">.................</th>
        </tr>
        <!-- START meetinglineList -->
        <tr ondrop="dropattachmentadv(event)" ondragover="allowDrop(event)" style="background-color:#e1e1e1;">      
            <td class="col_code200" MeetingLineID="{MeetingLineID}">
                <span MeetingLineID="{MeetingLineID}">
                    <a href="index.php?page=zob/adv/meetingline/moveup/{MeetingID}/{MeetingLineID}"><img src="views/classic/images/icon/moveup.png" title="Posun bodu nahorů"/></a>
                    <b>{LineNo}{LineNo2}</b>&nbsp;
                    <a href="index.php?page=zob/adv/meetingline/movedown/{MeetingID}/{MeetingLineID}"><img src="views/classic/images/icon/movedown.png" title="Posun bodu dolů"/></a>
                </span>
            </td>
            <td colspan="2">
                <a href="index.php?page=zob/adv/meetinglinecontent/add/{MeetingLineID}" id="anchor{MeetingLineID}" >Přidat obsah</a>
                <img src="views/classic/images/icon/verticalLine.png" />
                <a href="index.php?page=document/choice/list/meetinglineadv/{MeetingLineID}">Přidat přílohu</a>
            </td>
            <td class="col_action">
                <a href="index.php?page=zob/adv/meetingline/delete/{MeetingID}/{MeetingLineID}">
                    <img src="views/classic/images/icon/delete.png" title="{lbl_Delete}" onclick="return ConfirmDelete();"/>
                </a>
            </td>
        </tr>
        <tr>
        </tr>
        <tr>
            <td colspan="4" style="margin:0;padding:0;">
                <div id="editMeetingLine{MeetingLineID}">
                    {editdMeetingLine}
                </div>
            </td>
        </tr>
        <!-- END meetinglineList -->
        <form action="index.php?page=zob/adv/meetingline/add"  method="post">
            <tr style="border: 2px solid #000; background-color:#06F5A5;">
                <td>
                    <img src="views/classic/images/nav/addNew.png" alt="{lbl_New}" />    
                    Nový bod                
                </td>
                <td>
                    <select id="fielLineType" class="value" name="LineType" value="" onchange="this.setAttribute('value',this.options[this.selectedIndex].text);">
                        <option>Bod</option>
                        <option>Podbod</option>
                        <option>Doplňující bod</option>
                    </select>
                </td>
                <td>
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
<script>
    var anchor;
    var e;
    anchor = 'xx';
    e = document.getElementById('pageErrorMesage');
    if(e.innerHTML == '')
       anchor = '{anchor}';       
    if(document.getElementById(anchor)) 
      anchor = '#' + anchor;
    else
      anchor = '#header'; 
    window.location = anchor;
</script>