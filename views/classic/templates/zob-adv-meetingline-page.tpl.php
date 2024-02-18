<div id="PageHeader" pageID="{page_PageID}">
    <a href="index.php?page=zob/meetingline/list/{MeetingID}" id="closePage" class="button"><span class="action_close">X</span></a>    
    <span class="action_close">Media</span>    
    <span class="action_close" onclick="pptCreateTable('meetingattachment',{line_MeetingLineID},'MeetingLineID','AttachmentID,Description,DmsEntryID',function(e){wsAddMeetinglinepageattachment(this);})">Přílohy {AttachmentCount}</span>        
    <div id="table-container" ></div>
    <table>
        <tr style="background-color: white;">
            <td width="auto">    
                <h1> 
                    <span style="font-size: 30px;">{line_LineNo}&nbsp;</span>
                    <span id="header" style="font-size: 30px; width:90%;" name="Title" pkID="{line_MeetingLineID}" table="meetingline" onclick="pptEditField(this,'input');">{line_Title}</span>
                </h1>
            </td>
            <td width="100">
                <img src="views/classic/images/logoPrint.jpg" height ="80"/>
            </td>
        </tr>
    </table>
</div>
<div id="PageBody">
    <textarea id="body" rows="10" cols="140" value="" class="value" name="Content" pkID="{page_PageID}" table="meetinglinepage" onclick="pptEditField(this,'textarea');">{page_Content}</textarea>
    <table visibility="{visibleattachments}" class="ppt">
        <tbody id="pageattachments">
            <!-- START pageattachments -->
            <tr id="attachmentEntryNo{EntryNo}" class="blue">
                <td class="attachment">
                    <img src="views/classic/images/icon/attachment.png" title="{lbl_attachment}" style="width:24px;"/>
                    <img src="views/classic/images/icon/delete.png" title="{lbl_Delete}" onclick="wsDeleteMeetinglinepageattachment('attachmentEntryNo{EntryNo}',{EntryNo});"/>
                    <span style="font-size: 24px; width:90%;" table="meetingattachment" name="Description" pkID="{AttachmentID}" onclick="pptEditField(this,'input');">{Description}</span>                    
                </td>
            </tr>                
            <!-- END pageattachments -->
        </tbody>
    </table>
    <script>
        var e = document.getElementById('pageattachments');
        if(e.getAttribute('visibility') == "no"){
            e.style.display = 'none';
        }
    </script>
</div>
<div id="PageFooter">
    <a href="index.php?page=zob/adv/presentation/{MeetingID}/{prevPageNo}" accesskey="q" title="Předchozí (Alt+Q)"><img src="views/classic/images/nav/prevpage24.png"/></a>
    <span class="pageno">{PageNo}</span>
    <a href="index.php?page=zob/adv/presentation/{MeetingID}/{nextPageNo}" accesskey="w" title="Následující (Alt+W)"><img src="views/classic/images/nav/nextpage24.png" /></a>
</div>