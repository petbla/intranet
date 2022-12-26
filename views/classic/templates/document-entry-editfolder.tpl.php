<form action="index.php?page=document/modify/{ID}" method="post">
    <table class="edit-card">
        <tr>
            <td>
                <label class="title">{lbl_Name} </label><br>
                <span>
                    <input type="text" class="value col_fullname" name="Title" value="{Title}" TitleID="{ID}">
                    <input type="hidden" name="oldTitle" value="{Title}" oldTitleID="{ID}">
                </span>
            </td>
            <td>
                <label class="title">{lbl_Created}: </label><br>
                <span class="value col_date">{CreateDate}</span><br>
                <label class="title">{lbl_Modified}: </label><br>
                <span class="value col_date">{ModifyDateTime}</span>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <label class="title"></label><br>
                <textarea class="value " name="Content" cols="160" rows="10" ContentID="{ID}">{Content}</textarea>
                <input type="hidden" name="oldContent" value="{Content}" oldContentID="{ID}"><br>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <button type="submit" name="save" class="action_button" value="Zapsat>">{lbl_Save}</button>
            </td>
        </tr>
    </form>
</table>
