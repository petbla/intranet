<div>
    <p id="pageErrorMesage" class="error" onClick="this.style.display = 'none';" >{errorMessage}</p>
    <script>
        var e;
        e = document.getElementById('pageErrorMesage');
        if(e.innerHTML == '')
            e.style.display = 'none';
    </script>    
    
    <table class="print">
        <tr>
            <td>
                <img src="views/classic/images/logoPrint.png" >
            </td>
            <td>
                <p class="print-title1">OBEC MISTŘICE</p>
                <p class="print-title7">STAROSTA OBCE MISTŘICE</p>   
                <p class="print-title7">SVOLÁVÁ</p>
                <p class="print-title5">NA DEN 2. 9. 2022</p>
                <p class="print-title3">VEŘEJNÉ ZASEDÁNÍ</p>
                <p class="print-title3">ZASTUPITELSTVA OBCE</p>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <p class="print-title5">ZAČÁTEK: 19:00 HODIN</p>
                <p class="print-title5">MÍSTO KONÁNÍ: ZASEDACÍ MÍSTNOST OÚ</p>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <p class="print-title4">PROGRAM:</p>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <table>
                    <!-- START meetinglineList -->
                    <tr id="MeetingLineNo{MeetingLineID}" type="{LineType}">
                        <td class="print-text1">
                            {PrintLineNo}{LineNo2}.
                        </td>
                        <td class="print-text2">
                            {Title}
                        </td>
                    </tr>
                    <script>
                        var e;
                        e = document.getElementById('MeetingLineNo{MeetingLineID}');
                        if(e.getAttribute('type') == 'Doplňující bod')
                            e.style.display = 'none';
                    </script>
                    <!-- END meetinglineList -->
                </table>
            </td>    
        </tr>
        <tr>
            <td colspan="2">
                <br><br><br><br><br>
                <p class="print-title5">STAROSTA OBCE</p>
                <br><br><br><br><br>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <p class="print-text2">Vyvěšeno: 24. 8. 2022</p>
                <br>
                <p class="print-text2">Sňato: 2. 9. 2022</p>
            </td>
        </tr>
    </table>
                        
</div>
