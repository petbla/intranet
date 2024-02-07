<div id="PageHeader">
    <a href="index.php?page=zob/meetingline/list/{MeetingID}" id="closePage" class="button"><span class="action_close">X</span></a>    
    <span class="action_close">Odkazy</span>    
    <span class="action_close" onclick="pptCreateTable('meetingattachment',{line_MeetingLineID},'MeetingLineID');">Obrázky</span>        
    <div id="table-container"></div>
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
</div>
<div id="PageFooter">
    <a href="index.php?page=zob/adv/presentation/{MeetingID}/{prevPageNo}" accesskey="q" title="Předchozí (Alt+Q)"><img src="views/classic/images/nav/prevpage24.png"/></a>
    <span class="pageno">{PageNo}</span>
    <a href="index.php?page=zob/adv/presentation/{MeetingID}/{nextPageNo}" accesskey="w" title="Následující (Alt+W)"><img src="views/classic/images/nav/nextpage24.png" /></a>
</div>