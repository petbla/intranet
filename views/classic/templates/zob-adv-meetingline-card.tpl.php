<div id="PageHeader">
    <h1 id="header">{line_LineNo}&nbsp;{line_Title}</h1>
    <a href="index.php?page=zob/meetingline/list/{MeetingID}" id="closePage" class="button"><span class="action_close">X</span></a>
</div>
<div id="PageBody">
    <textarea  name="Content" rows="10" cols="140" value="" class="value" pkID="{page_PageID}" table="meetinglinepage" onchange="wsUpdate(this);">{page_Content}</textarea>
</div>
<div id="PageFooter">
    <a href="index.php?page=zob/adv/meetinglinecard/{MeetingID}/{prevPageNo}" accesskey="q" title="Předchozí (Alt+Q)"><img src="views/classic/images/nav/prevpage24.png"/></a>
    <span class="pageno">{PageNo}</span>
    <a href="index.php?page=zob/adv/meetinglinecard/{MeetingID}/{nextPageNo}" accesskey="w" title="Následující (Alt+W)"><img src="views/classic/images/nav/nextpage24.png" /></a>
</div>