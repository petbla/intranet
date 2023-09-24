<div id="ListItems">
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
    <h2>Výběr dokumentu</h2>
    <div id="breads">
    {breads}
    </div>
    <form action="index.php?page=document/choice/search" method="post">
        <table style="width:70%;">
            <tr>
                <td>
                    <label class="title">Vyhledat:</label>
                    <input type="text" name="searchtext" />
                    <input type="submit" name="search" class="action_button" value="{lbl_Search}">
                    <input type="submit" name="storno" class="action_button" value="{lbl_Storno}">
                </td>
            </tr>
            <!-- START listFolder -->
            <tr>
                <td>       
                    <a href="" SET_HREF id="{ID}" recID="dmsentry{ID}" table="dmsentry" pkID="{ID}" name="Title" filename="{Name}" type="{DocumentType}" url="{Url}" onclick="wsLogView();">
                        {icon{DocumentType}{FileExtension}}    
                    </a>
                    <a href="index.php?page=document/choice/{Action}/{Type}/{TypeID}/{ID}" class="{DocumentClass}">
                        {Title}
                    </a>
                </td>
            </tr>
            <!-- END listFolder -->
        </table>
        <input type="hidden" id="fieldInboxID" name="Type" value="{Type}">
        <input type="hidden" id="fieldInboxID" name="TypeID" value="{TypeID}">
        <input type="hidden" id="fieldParentID" name="ParentID" value="{ParentID}">
    </form>

</div>
