<div id="actionpanel">
    <table>
        <tr>
            <td>
                <a href="index.php?page=zob/print/20000/51" title="Formulář"><img src="views/classic/images/icon/attachment.png" /></a>
                <br>
                Formulář
            </td>
        </tr>
    </table>
</div>
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
        <tr>
            <th style="width:100px;">..........</th>
            <th>{lbl_DocumentNo}</th>
            <th>{lbl_Description}</th>
            <th>{lbl_CreateDate}</th>
            <th>{lbl_ExecuteDate}</th>
        </tr>
        <!-- START AgendaList -->
        <tr>      
            <td class="col_action">
                <a href="" SET_HREF table="agenda" entryid="{EntryID}" type="odkaz" name="" id="{ID}"><img src="views/classic/images/icon/unlink.png" alt="{lbl_delete}" onclick="return ConfirmUnlink();"/></a>                
                <a href="" SET_HREF table="agenda" entryid="{EntryID}" type="SourceFolder" name="{Name}"></a>                
                <a href="" SET_HREF table="agenda" entryid="{EntryID}" type="PDF" name="{Name}"></a>                

            </td>
            <td SET_HREF class="col_text" table="agenda" entryid="{EntryID}" type="DocumentNo" name="{Name}">{DocumentNo}</td>
            
            <td class="col_text">{Description}</td>
            <td class="col_text">{CreateDate}</td>
            <td class="col_text">{ExecuteDate}</td>
        </tr>
        <!-- END AgendaList -->
    </table>
</div>
<div id="pagecounter" class="bottom">{navigate_menu}</div>

