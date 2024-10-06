<style>
        .textbox-container {
            display: flex;
            justify-content: space-between;
            width: 100%;
            max-width: 1800px;
            margin: 0 auto;
        }

        .textbox-container textarea {
            flex: 1;
            margin-right: 10px;
            padding: 2px;
            font-size: 16px;
        }

        .textbox-container textarea:last-child {
            margin-right: 0;
        }
        .lineoptions {
            width: 20px;
            height: 20px;
            margin-right: 5px;
        }
        select.lineoptions {
            width: 50px;
            height: 20px;
            margin-right: 5px;
        }
    </style>

<div id="PageHeader" pageID="{page_PageID}">
    <a href="index.php?page=zob/adv/presentation/content/{MeetingID}" id="closePage" accesskey="x" class="button"><span class="action_close">{lbl_Close}</span></a>    
    <a href="index.php?page=zob/adv/presentation/show/{MeetingID}/{PageNo}" title="Náhled" target="_blank" class="button"><span class="action_close">Náhled</span></a>    

    <span class="action_close" onclick="pptCreateTable('meetingattachment',{line_MeetingLineID},'MeetingLineID','AttachmentID,Description,DmsEntryID',function(e){wsAddMeetinglinepageattachment(this);})">Přílohy {AttachmentCount}</span>        
    <span class="action_close" onclick="document.getElementById('meetingcontent').style.display='';">Text zápisu</span>        
    <div id="table-container" ></div>
    <table>
        <tr style="background-color: white;">
            <td width="auto">    
                <h1> 
                    <span>
                        <img src="views/classic/images/nav/changeMark.png" id="changeMark{page_PageID}" title="Změna obsahu" name="Changed" value="0" pkID="{page_PageID}" table="meetinglinepage" onclick="wsUpdate(this);this.style.display='none';"/>&nbsp;                                                
                    </span>
                    <span style="font-size: 30px;">{page_Point}&nbsp;</span>                    
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
    <!-- START meetinglinepagelines -->
    <div class="textbox-container">
        <select id="FontStyle{EntryNo}" class="lineoptions" name="FontStyle" value="{FontStyle}" pkID="{EntryNo}" table="meetinglinepageline" onchange="this.setAttribute('value',this.options[this.selectedIndex].text); wsUpdate(this); onChangeFontStyle(this);">
            <option id="X{EntryNo}"></option>
            <option id="H1{EntryNo}">H1</option>
            <option id="H2{EntryNo}">H2</option>
            <option id="H3{EntryNo}">H3</option>
            <option id="T1{EntryNo}">T1</option>
            <option id="T2{EntryNo}">T2</option>
            <option id="IMG{EntryNo}">IMG</option>
        </select>
        <img src="views/classic/images/icon/delete.png" id="Deleteline{EntryNo}" height ="24" pkID="{EntryNo}" table="meetinglinepageline" onclick="wsDelete(this);" />
        <textarea id="ContentLine{EntryNo}" style="display:inline;" rows="1" cols="40" value="" class="autosize par" name="Content" pkID="{EntryNo}" table="meetinglinepageline" onchange="wsUpdate(this);">{Content}</textarea>
        <input type="text" id="Image{EntryNo}" style="display:none; width:100%;" value="ImageURL" class="lineoptions" name="ImageURL" pkID="{EntryNo}" table="meetinglinepageline" onchange="wsUpdate(this);">{ImageURL}</input>
        <script>
            var e;
            e = document.getElementById("{FontStyle}{EntryNo}");
            if(e)
                e.setAttribute('selected',true);
            e = document.getElementById("FontStyle{EntryNo}");
            if(e)
                onChangeFontStyle(e);
        </script>
    </div>        
    <!-- END meetinglinepagelines -->
    <br>
    <textarea id="ContentLine0" style="display:inline;" rows="1" cols="40" value="" class="autosize par" name="Content" pkID="0" table="meetinglinepageline" parentPkID="{page_PageID}" parentTable="meetinglinepage" onchange="wsUpdateNew(this);" placeholder="Vložit řádek"></textarea>
    <div id="meetingcontent" ondblclick="this.style.display='none';">
        <span><b>Text zápisu</b><span><br>
        <p class="blue" style="border:1px solid grey; font-size:16px; background-color: yellow;" readonly>
            {page_MeetingContent}
        </p>
    </div>
    <table id="tablepageattachments" visibility="{visibleattachments}" class="ppt">
        <tbody id="pageattachments">
            <!-- START pageattachments -->
            <tr id="attachmentEntryNo{EntryNo}" class="blue">
                <td class="attachment">
                    <img src="views/classic/images/icon/delete.png" title="{lbl_Delete}" onclick="wsDeleteMeetinglinepageattachment('attachmentEntryNo{EntryNo}',{EntryNo});"/>
                    <span style="font-size: 24px; width:90%;" table="meetingattachment" name="Description" pkID="{AttachmentID}" onclick="pptEditField(this,'input');">{Description}</span>                    
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
    <a href="index.php?page=zob/adv/presentation/edit/{MeetingID}/1" title="Úvodní strana (Home)"><img src="views/classic/images/nav/homepage24.png"/></a>
    <span>&nbsp;</span>
    <a href="index.php?page=zob/adv/presentation/content/{MeetingID}" title="Zavřít (ESC)"><img src="views/classic/images/nav/escpage24.png"/></a>
    <span>&nbsp;</span>
    <a href="index.php?page=zob/adv/presentation/edit/{MeetingID}/{prevPageNo}" title="Předchozí (Alt+Q)"><img src="views/classic/images/nav/prevpage24.png"/></a>
    <span class="pageno">{PageNo}</span>
    <a href="index.php?page=zob/adv/presentation/edit/{MeetingID}/{nextPageNo}" title="Následující (Alt+W)"><img src="views/classic/images/nav/nextpage24.png" /></a>
</div>
<script>
    var e =document.getElementById('changeMark{page_PageID}');
    if('{page_Changed}' != '1'){
        e.style.display = 'none';
    }
</script>


