        <header>
            <table>
                <tr>
                    <td colspan="2" class="logo">
                        <img src="views/classic/images/logoPrint.png" />
                        <br>
                        <table>
                            <tr>
                                <td><small>Bankovní spojení:</small></td>
                                <td><small>{cfg_compBankName}</small></td>
                            </tr><tr>
                                <td><small>Bankovní účet:</small></td>
                                <td><small>{cfg_compBankNo}</small></td>
                            </tr><tr>
                                <td><small>IČO:</small></td>
                                <td><small>{cfg_compICO}</small></td>
                            </tr><tr>
                                <td><small>Web:</small></td>
                                <td><small class="weblink">{cfg_websiteName}</small></td>
                            </tr><tr>
                                <td><small>Email:</small></td>
                                <td><small><b>{cfg_compEmail}</b></small></td>
                            </tr><tr>
                                <td><small>Telefon:</small></td>
                                <td><small><b>{cfg_compPhone}</b></small></td>
                            </tr><tr>
                                <td><small>Datová schránka:</small></td>
                                <td><small><b>{cfg_compDataBox}</b></small></td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        <table>
                            <tr>
                                <td colspan="2" class="center">
                                    <span class="big company">OBEC MISTŘICE</span><br>
                                    <span class="small company">PSČ 687 12 okres Uherské Hradiště</span>
                                    <br>
                                    <br>
                                    <br>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    &nbsp;
                                </td>
                                <td class="address">
                                    {FullHtmlAddress}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </header>
        <main>
            <table>
                <tr>
                    <td colspan="4">
                        <br>
                    </td>
                </tr>
                <tr>
                    <td class="info-line" style="width: 30%;">
                        <span class="title">Váš dopis značky / ze dne</span><br>
                        <span>&nbsp;</span>
                    </td>
                    <td class="info-line" style="width: 25%;">
                        <span class="title">naše značka</span><br> 
                        <span>{DocumentNo}&nbsp;</span>
                    </td>
                    <td class="info-line" style="width: 30%;">
                        <span class="title">vyřizuje / linka</span><br> 
                        <span>{PresenterName} / {PresenterPhone}&nbsp;</span>
                    </td>
                    <td class="info-line" style="width: 15%;">
                        <span class="title">Mistřice</span><br> 
                        <span>{AtDate}&nbsp;</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        <br><br>
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        <span>Věc</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        <span class="subject">{Subject}</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        <div class="ql-editor">
{Content}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        <br><br>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        &nbsp;
                    </td>
                    <td class="sign">
                        <span>{SignatureName}</span><br>
                        <span>{SignatureFunction}</span>
                    </td>
                    <td>
                        &nbsp;
                    </td>                    
                </tr>
            </table>
        </main>
