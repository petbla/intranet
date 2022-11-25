<img src="views/classic/images/icon/modify.png" onclick="showDocumentCard('{editcardID}','SelectedADocumentNo{ID}','{ADocumentNo}');" />
<a href="index.php?page=document/editcontent/{ID}">
    <img src="views/classic/images/icon/edit.png" alt="OBSAH" title="{lbl_msg_ModifyContentDoc}">
</a>
<a href="index.php?page=entry/deleteFile/{ID}" onclick="return ConfirmDelete();">
    <img src="views/classic/images/icon/delete.png" alt="DELETE" id="{ID}" onclick="return ConfirmDelete();"/>
</a>
