<div id="breads">
{breads}
</div>
<div id="document">
    <table>
        <tr>
            <td>Dokument</td>
            <td>{Title}</td>
        </tr>
        <tr>
            <td>Vytvořeno</td>
            <td>{CreateDate}</td>
        </tr>
        <tr>
            <td>Změněno</td>
            <td>ModifyDateTime</td>
        </tr>
    </table>
    <object data="test.pdf" type="application/pdf" width="300" height="200">
        alt : <a href="{filePath}">test.pdf</a>
    </object>
    <a href="{filePath}">test.pdf</a>
</div>