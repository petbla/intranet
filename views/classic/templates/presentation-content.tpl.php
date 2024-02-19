<h1 id="header">{Header}</h1>
<p>
    <a href="index.php?page=zob/adv/presentation/{MeetingID}" title="Prezentace"><img src="views/classic/images/nav/present48.png" /></a>        
    <img src="views/classic/images/nav/line48.png"/>
</p>
<a href="index.php?page=zob/meetingline/list/{MeetingID}" id="closePage" class="button"><span class="action_close">{lbl_Close}</span></a>
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
                                <b>{Point}</b>&nbsp;
                            </span>
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
        console.log('jsem tu');
        if (event.key === 'Backspace') {
            window.location = "index.php?page=zob/meetingline/list/{MeetingID}";
        }
    });
</script>