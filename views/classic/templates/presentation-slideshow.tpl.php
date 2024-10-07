<div id="PageHeader" pageID="{page_PageID}">
    <div id="table-container" ></div>

    

    <table>
        <tr style="background-color: white;">
            <td width="auto">    
                <h1> 
                    <span style="font-size: 30px;">{page_Point}&nbsp;</span>
                    <span id="header" style="font-size: 32px; width:90%;">{line_Title}</span>
                </h1>
            </td>
            <td width="100">
                <img src="views/classic/images/logoPrint.jpg" height ="80"/>
            </td>
        </tr>
    </table>
</div>
<div id="PageBody">
    
    <!-- START meetinglinepagelines -->
    <div>
        <textarea id="ContentLine{EntryNo}" class="autosize par noborder" style="padding:0;" name="Content" pkID="{EntryNo}" table="meetinglinepageline">{Content}</textarea>
        <img src="http://localhost:90/Obecn%C3%AD%20%C3%BA%C5%99ad/_Zastupitelstvo/2022-2026/7/Image/{ImageURL}" id="Image{EntryNo}" />        
        <input type="hidden" id="FontStyle{EntryNo}" value="{FontStyle}" pkID="{EntryNo}" />
        <input type="hidden" id="Align{EntryNo}" value="{Align}" pkID="{EntryNo}" />        
        <input type="hidden" id="ImageWidth{EntryNo}" value="{ImageWidth}" pkID="{EntryNo}" />        
        <input type="hidden" id="ImageHeight{EntryNo}" value="{ImageHeight}" pkID="{EntryNo}" />        
        <script>
            e = document.getElementById("FontStyle{EntryNo}");
            if(e)
                formatContentLine(e);
        </script>
    </div>        
    <!-- END meetinglinepagelines -->
    
    <textarea id="body" rows="10" cols="140" value="" class="value" style="border:0;" readonly >{page_Content}</textarea>
    <textarea id="body_front" rows="10" cols="140" value="" class="value" style="border:0; font-size:60px; text-align:center;" readonly >{page_Content}</textarea>
    <textarea id="body_warp" rows="10" cols="140" value="" class="value" style="border:0; font-size:50px;" readonly >{page_Content}</textarea>
    <script>
        var body = document.getElementById('body');
        var bodyfront = document.getElementById('body_front');
        var bodywarp = document.getElementById('body_warp');
        if('{page_PageType}' == "front"){
            body.style.display = 'none';
            bodyfront.style.display = '';
            bodywarp.style.display = 'none';
        }else{
            if('{page_PageType}' == "warp"){
                body.style.display = 'none';
                bodyfront.style.display = 'none';
                bodywarp.style.display = '';
            }else{
                body.style.display = '';
                bodyfront.style.display = 'none';
                bodywarp.style.display = 'none';
            }
        }
    </script>
    
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
    <a href="index.php?page=zob/adv/presentation/show/{MeetingID}/1" title="Úvodní strana (Home)"><img src="views/classic/images/nav/homepage24.png"/></a>
    <span>&nbsp;</span>
    <a href="index.php?page=zob/adv/presentation/content/{MeetingID}" title="Zavřít (ESC)"><img src="views/classic/images/nav/escpage24.png"/></a>
    <span>&nbsp;</span>
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


