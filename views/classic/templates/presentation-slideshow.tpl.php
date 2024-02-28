<div id="PageHeader" pageID="{page_PageID}">
    <div id="table-container" ></div>

    pokud bylo něco změněno, tak nepustiti prezentaci nebo na to upozornit

    <table>
        <tr style="background-color: white;">
            <td width="auto">    
                <h1> 
                    <span style="font-size: 30px;">{page_Point}&nbsp;</span>
                    <span id="header" style="font-size: 30px; width:90%;">{line_Title}</span>
                </h1>
            </td>
            <td width="100">
                <img src="views/classic/images/logoPrint.jpg" height ="80"/>
            </td>
        </tr>
    </table>
</div>
<div id="PageBody">
    <textarea id="body" rows="10" cols="140" value="" class="value" style="border:0;" readonly >{page_Content}</textarea>
    <table id="tablepageattachments" visibility="{visibleattachments}" class="ppt">
        <tbody id="pageattachments">
            <!-- START pageattachments -->
            <tr id="attachmentEntryNo{EntryNo}" class="blue">
                <td class="attachment">
                    <img src="views/classic/images/icon/attachment.png" title="{lbl_Open}"/>
                    <span><a href="" style="font-size: 24px; width:90%;" title="{lbl_Open}">{Description}</a></span>                    
                </td>
            </tr>                
            <!-- END pageattachments -->
        </tbody>
    </table>
    <script>
        var e = document.getElementById('tablepageattachments');
        if(e.getAttribute('visibility') == "no"){
            e.style.display = 'none';
        }
    </script>
</div>
<div id="PageFooter">
    <a href="index.php?page=zob/adv/presentation/show/{MeetingID}/{prevPageNo}" title="Předchozí (Alt+Q)"><img src="views/classic/images/nav/prevpage24.png"/></a>
    <span class="pageno">{PageNo}</span>
    <a href="index.php?page=zob/adv/presentation/show/{MeetingID}/{nextPageNo}" title="Následující (Alt+W)"><img src="views/classic/images/nav/nextpage24.png" /></a>
</div>
<script>
    document.addEventListener('keydown', function(event) {
        if (event.key === 'ArrowLeft') {            
            window.location = "index.php?page=zob/adv/presentation/show/{MeetingID}/{prevPageNo}";
        } else if (event.key === 'ArrowRight') {
            window.location = "index.php?page=zob/adv/presentation/show/{MeetingID}/{nextPageNo}";
        } else if (event.key === 'Home') {
            window.location = "index.php?page=zob/adv/presentation/show/{MeetingID}/1";
        } else if (event.key === 'Escape') {
            window.location = "index.php?page=zob/meetingline/list/{MeetingID}";
        } else if (event.key === 'Backspace') {
            window.location = "index.php?page=zob/meetingline/list/{MeetingID}";
        }
    });
</script>


