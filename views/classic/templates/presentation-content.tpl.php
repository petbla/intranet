<h1 id="header">{Header}</h1>
<p>
    <a href="index.php?page=zob/adv/presentation/edit/{MeetingID}/1" title="Příprava prezentace"><img src="views/classic/images/nav/makepresent48.png" /></a>        
    <img src="views/classic/images/nav/line48.png"/>
    <a href="index.php?page=zob/adv/presentation/show/{MeetingID}" title="Prezentace"><img src="views/classic/images/nav/present48.png" /></a>        
</p>
<a href="index.php?page=zob/meetingline/list/{MeetingID}" accesskey="x" id="closePage" class="button"><span class="action_close">{lbl_Close}</span></a>
<div id="DocumentItems">
    <p id="pageMessage" class="message" onClick="this.style.display = 'none';" >{message}</p>
    <p id="pageErrorMesage" class="error" onClick="this.style.display = 'none';" >{errorMessage}</p>
    
    <table>
        <tr style="vertical-align: top;background-color: #fff">
            <td>
                <table class="table-child">
                    <tr>
                        <th>Strana</th>
                        <th colspan="2">Bod</th>
                        <th>&nbsp;</th>
                        <th>Název</th>
                    </tr>
                    <!-- START meetinglinepageList -->
                    <tr>      
                        <td>
                            {PageNo}
                        </td>
                        <td>
                            {Lin_LineType}
                        </td>
                        <td class="col_code200">
                            <span MeetingLineID="{MeetingLineID}">
                                <a href="">
                                    <img src="views/classic/images/nav/addNew.png" width="16px" title="Nová strana"/>
                                </a>
                                &nbsp;
                                <b>{Point}</b>&nbsp;
                            </span>
                        </td>
                        <td>
                            <a href="index.php?page=zob/adv/presentation/edit/{MeetingID}/{PageNo}">
                                <img src="views/classic/images/nav/changeMark.png" id="changeMark{PageNo}" title="Změna obsahu"/>&nbsp;
                            </a>
                            <script>
                                var e =document.getElementById('changeMark{PageNo}');
                                if('{Changed}' != '1'){
                                    e.style.display = 'none';
                                }
                            </script>
                        </td>
                        <td>
                            {Lin_Title}
                        </td>
                    </tr>
                    <!-- END meetinglinepageList -->
                </table>
            </td>
        </tr>
    </table>

</div>

<script>
    var e;
    e = document.getElementById('pageMessage');
    if(e.innerHTML == '')
        e.style.display = 'none';

    e = document.getElementById('pageErrorMesage');
    if(e.innerHTML == '')
        e.style.display = 'none';

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            window.location = "index.php?page=zob/meetingline/list/{MeetingID}";
        }
    });
</script>