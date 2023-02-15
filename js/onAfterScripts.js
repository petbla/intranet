'use strict';

// ----------------------------------------------------------------------------------------
// Init variables
// ----------------------------------------------------------------------------------------
var setup;
var link_element;

var documentLink;
var fileTitle, fileExtension;
var linkTitle;
var pagecounter;
var search;
var formAdUser;
var password, password_confirm;
var loginForm;
var items,contacts;
var lasteditcard,lastEditContact;
var tags,deleteEntryType20;
var grouplist;
var contactGroups;
var entriesType35;
var mouseFromX,mouseFromY;
var activeForm;
var sqlrequest;

var arrGroup = null;
var grouplistnewcontact;
var fld_handled;
var fld_webroot;
var activeElectionPeriod;
var activeMemberType;
var activeMeetingLine;
var a_inbox;
var meetings;
var meetinglines;


// ----------------------------------------------------------------------------------------
// Set variables
// ----------------------------------------------------------------------------------------
documentLink = document.querySelector('#cosumentLink');
fileTitle = document.querySelector('#FileTitle');
fileExtension = document.querySelector('#FileExtension');
pagecounter = document.querySelector('#pagecounter');
search = document.querySelector('#search');
password = document.querySelector('#usr_psw1');
password_confirm = document.querySelector('#usr_psw2');
loginForm = document.querySelector('#loginForm');


deleteEntryType20 = document.querySelectorAll('#DeleteEntryType20');
items = document.querySelectorAll('[dmsClassName="item"]');
contacts = document.querySelectorAll('[dmsClassName="contact"]');
meetings = document.querySelectorAll('[dmsClassName="meeting"]');
meetinglines = document.querySelectorAll('[dmsClassName="meetingline"]');
entriesType35 = document.querySelectorAll('a[entrytype="35"]');
sqlrequest = document.querySelector('#sqlrequest');

a_inbox = document.querySelectorAll('[name="activeInbox"]');

fld_handled = document.querySelector('#fld_handled');
fld_webroot = document.querySelector('#fld_webroot');
grouplistnewcontact = document.querySelector( '[id="grouplistnewcontact"]' );

activeElectionPeriod = document.getElementById('activeElectionPeriod');
activeMemberType = document.getElementById('activeMemberType');
activeMeetingLine = document.getElementById('activeMeetingLine');

// ----------------------------------------------------------------------------------------
// Functions
// ----------------------------------------------------------------------------------------
function tags2Html( arr ){
    var newval='';
    arr.forEach(str => {
        str = "<span class='tags-item'>" + str + "</span>";
        newval += str;
    });
    return newval;
}

function validatePassword () {
    if (password.value != password_confirm.value) {
        password_confirm.setCustomValidity('Heslo se neshoduje.');
    }    
    else
    {
        password_confirm.setCustomValidity('');
    }
}

function formatElementClass (classText) {
    var att,e,i;
    att = '[class="' + classText + '"]';
    e = document.querySelectorAll(att);
    for (i = 0; i < e.length; i++) {
        e[i].innerHTML = formatText(e[i].innerHTML,classText);
    }   
}

function formatText (text, type)
{
    var newtext = ''
    var arr,val;        
    if(type === '')
    {
        return text;
    }
    type = type.toLowerCase();
    if(text === '')
    {
        return '';
    }
    arr = text.split(',');              
    arr.forEach(val => {
        switch (type) {
            case 'phone':
                val = formatPhoneNumber(val);
                break;
            case 'email':
                val = formatEmailTo(val);
                break;
        }
        if(newtext)
        {
            newtext = newtext + '<br>';
        }
        newtext = newtext + val;
    });
    return(newtext);
}   

function formatPhoneNumber (phone)
{
    var newphone;
    if(phone.length == 9) {
        newphone = phone.replace(/(\d{3})(\d{3})(\d{3})/, '$1 $2 $3'); 
    }
    if(phone.length == 14) {
        newphone = phone.replace(/(\d{5})(\d{3})(\d{3})(\d{3})/, '$1 $2 $3 $4'); 
    }
    else if(phone[0] == '+'){
        newphone = phone.replace(/(\+\d{3})(\d{3})(\d{3})(\d{3})/, '$1 $2 $3 $4'); 
    }            
    if(newphone !== '')
    {
        newphone = "<a href='tel:" + newphone + "'>" + newphone + "</a>";
    }    
    return(newphone);
}

function formatEmailTo (email)
{
    var newemail = '';
    if(email !== '')
    {
        newemail = "<a href='mailto:" + email + "'>" + email + "</a>";
    }    
    return(newemail);
}

function dragElement(elmnt) {
    var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
    var ID;
    ID = elmnt.getAttribute('form_id');
    if (document.getElementById(elmnt.id + "header" + ID)) {
        // if present, the header is where you move the DIV from:
        document.getElementById(elmnt.id + "header" + ID).onmousedown = dragMouseDown;
    } else {
        // otherwise, move the DIV from anywhere inside the DIV: 
        elmnt.onmousedown = dragMouseDown;
    }

    function dragMouseDown(e) {
        e = e || window.event;
        e.preventDefault();
        // get the mouse cursor position at startup:
        pos3 = e.clientX;
        pos4 = e.clientY;
        document.onmouseup = closeDragElement;
        // call a function whenever the cursor moves:
        document.onmousemove = elementDrag;
    }

    function closeDragElement() {
        // stop moving when mouse button is released:
        document.onmouseup = null;
        document.onmousemove = null;
    }

    function elementDrag(e) {
        e = e || window.event;
        e.preventDefault();
        // calculate the new cursor position:
        pos1 = pos3 - e.clientX;
        pos2 = pos4 - e.clientY;
        pos3 = e.clientX;
        pos4 = e.clientY;
        // set the element's new position:
        elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
        elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
    }
}

function getCookie(name) {
    var value = "; " + document.cookie;
    var parts = value.split("; " + name + "=");
    if (parts.length == 2) return parts.pop().split(";").shift();
}


// ----------------------------------------------------------------------------------------
// Code
// ----------------------------------------------------------------------------------------
if (pagecounter != null){
    if (pagecounter.innerText == ""){
        pagecounter.style.display = 'none';
    }
}

if ((fileExtension) && ('innerText' in fileExtension)) {
    switch (fileExtension.innerText) {
        case 'pdf':
            linkTitle = 'Náhled';
            break;
        case 'doc':
        case 'docx':
        case 'xls':
        case 'xlsx':
        case 'rtf':
            linkTitle = 'Stáhnout';
            break;
        case 'ppt':
        case 'pptx':
            linkTitle = 'Stáhnout';
            break;
        default:
            linkTitle = '';
            documentLink.style.display = 'none';
            break;
    }
    if (linkTitle) {
        documentLink.innerText = linkTitle;
    }   
}

if (password) {
    password.onchange = validatePassword;
}
if (password_confirm) {
    password_confirm.onkeyup = validatePassword;
}

if(items){
    items.forEach(function(item){
        item.onclick = function (e) {
            var form,card,type;
            var oldValue,inputValue,back,username;
            var activeForm;
            var isNew;
            var SelectedADocumentNo,ADocumentNo;

            // Init record on the form
            initForm('Title',e.target.id);
            initForm('FileExtension',e.target.id);
            initForm('Url',e.target.id);
            initForm('RemindResponsiblePerson',e.target.id);
            initForm('Content',e.target.id);
            initForm('RemindLastDate',e.target.id);
            initForm('Remind',e.target.id);
            initForm('RemindFromDate',e.target.id);
            initForm('RemindClose',e.target.id);

            
            // Show card
            type = e.target.getAttribute('dmsClassType');
            
            if (type =="Note")
                type = "File";
           
            card = document.querySelector('[id="edit' + type + 'Card' + e.target.id + '"]' );
            card.style.display = 'block';

            // ADocumentNo select
            SelectedADocumentNo = "SelectedADocumentNo" + e.target.id;
            ADocumentNo = document.querySelector('[ADocumentNoID="' + e.target.id + '"]' );
            if (ADocumentNo.innerHTML != ''){
                document.getElementById(SelectedADocumentNo).style.display = 'none';
            };
  
        }
    })   
}

if(meetings){
    meetings.forEach(function(meeting){
        meeting.onclick = function (e) {
            var card;
            // Show card
            card = document.querySelector('[id="editMeetingCard' + e.target.id + '"]' );
            if(card)
                card.style.display = 'block';
        }
    })
}

if(meetinglines){
    meetinglines.forEach(function(meetingline){
        meetingline.onclick = function (e) {
            var card;
            // Show card
            card = document.getElementById('editMeetingLine' + e.target.id);
            if(card)
                card.style.display = 'block';
        }
    })
}

if(contacts){
    contacts.forEach(function(contact){
        contact.onclick = function (e) {
            var grouplist;
            var tag,arrGroup;
            var card;

            // Init record on the form
            initForm('Title',e.target.id);
            initForm('FirstName',e.target.id);
            initForm('LastName',e.target.id);
            initForm('Function',e.target.id);
            initForm('Company',e.target.id);
            initForm('Web',e.target.id);
            initForm('Address',e.target.id);
            initForm('BirthDate',e.target.id);
            initForm('Phone',e.target.id);
            initForm('Email',e.target.id);
            initForm('Note',e.target.id);
            initForm('ContactGroups',e.target.id);
            initForm('Close',e.target.id);
            
            // Show card
            card = document.querySelector('[id="editContactCard' + e.target.id + '"]' );
            card.style.display = 'block';

        }
    })   
}

if (grouplistnewcontact !== null){
    grouplistnewcontact.onchange = function  (ee) {
        var tag,arrGroup; 
        var oldValue,newValue;
        var contactGroups;
        
        tag = document.querySelector('[class="tagsnewcontact"]' );
        contactGroups = document.querySelector('[id="ContactGroupsnewcontact"]' );
        if (tag !== null)
        {
            arrGroup = (contactGroups.value).split(',');
            if(arrGroup)
            {
                var idx;
                idx = arrGroup.indexOf(ee.target.value);
                if (idx > -1)
                {
                    arrGroup.splice(idx,1);
                }
                else
                {
                    arrGroup.push(ee.target.value);
                }            
                arrGroup = arrGroup.filter(function(el){ return el;});
                tag.innerHTML = tags2Html( arrGroup );       
                if(contactGroups)
                {
                    contactGroups.value = arrGroup.join(',');
                }
            }
        }
        ee.target.value = '';
    };
}


if(entriesType35){
    entriesType35.forEach( function(entry) {
        entry.onclick = function (e) {
            e.preventDefault();
        }
    })
}

formatElementClass('phone');
formatElementClass('email');



link_element = document.querySelectorAll('[SET_HREF]');
if(link_element){
    setup = document.getElementById('setup');
    link_element.forEach( function (e) {
        switch (e.getAttribute('table')) {
            case 'dmsentry':
                // <a href="" id="0828E65D-32A8-4E77-8BD8-C188AAB4DCAF" table="dmsentry" name="Obecní úřad\Reklama a grafika\POUKAZ.pdf" extension="pdf" type="30" onclick="wsLogView();">POUKAZ</a>
                // <a href="" SET_HREF id="{ID}" table="dmsentry" name="{Name}" type="{Type}" url="{Url}" onclick="wsLogView();">{Title}</a>
                switch (e.getAttribute('type')) {
                    case '30':
                        // File ()
                        var name,extension,id;
                        name = e.getAttribute('name')
                        extension = name.split('.').pop();

                        if (isValidFileExtension(extension))
                        {
                            var $url, $app;
                            $url = setup.getAttribute('webroot') + name;
                            $app = getApplication(extension);
                            e.href = $app + $url;
                            e.target = '';
                            if($app == '')
                                e.target = '_blank';
                        }else{
                            id = e.getAttribute('id');
                            e.href = 'index.php?page=document/view/' + id;
                            e.target = '';
                        }
                        break;
                    case '35':
                        // Note
                        var url;
                        url = e.getAttribute('url');
                        if(url !== ''){
                            e.href = 'http://' + url;
                            e.target = '_blank';
                        }else{
                            e.href = 'index.php?page=document/view/' + e.getAttribute('id');
                        }
                        break;
                    case '20':
                    case '25':
                        // Folder, Block
                        e.href = 'index.php?page=document/list/' + e.getAttribute('id');
                        e.target = '';
                    default:
                        break;
                }
                break;
            case 'agenda':
                // <td class="col_text" a_type="agenda" data-agenda-entryid="{EntryID}" data-dms-server="{cfg_webroot}" data-agenda-entryname="{Name}">{DocumentNo}</td>
                // <td SET_HREF class="col_text" table="agenda" entryid="{EntryID}" name="{Name}">{DocumentNo}</td>
                
                var entryid,title,link,web,entryname,id;
                var fileextension,entryname2,isChange;
                entryid = e.getAttribute('entryid');
                web = setup.getAttribute('webroot');
                entryname = e.getAttribute('name');

                switch (e.getAttribute('type')) {
                    case 'DocumentNo':
                        if(entryid !== ''){
                            title = e.innerHTML;
                            fileextension = entryname.split('.').pop();
                            if (fileextension.toLowerCase() == 'pdf'){
                                // Check to change to doc,docx,xls,xlsx
                                entryname2 = entryname.replace('.' + fileextension,'.doc');
                                isChange = doesFileExist(web + entryname2);
                                if(!isChange){
                                    entryname2 = entryname.replace('.' + fileextension,'.docx');
                                    isChange = doesFileExist(web + entryname2);
                                }
                                if(!isChange){
                                    entryname2 = entryname.replace('.' + fileextension,'.xls');
                                    isChange = doesFileExist(web + entryname2);
                                }
                                if(!isChange){
                                    entryname2 = entryname.replace('.' + fileextension,'.xlsx');
                                    isChange = doesFileExist(web + entryname2);
                                }
                                if(isChange)
                                    entryname = entryname2;
                            }
                            link = "<a href='" + web + entryname + "'  target='_blank'>" + title + "</a>";
                            e.innerHTML = link;
                            e.setAttribute('class','col_link');       
                        };
                        break;
                    case 'odkaz':
                        if(entryid != ""){
                            id = e.getAttribute('id');    
                            e.href = "index.php?page=agenda/unlink/" + id;    
                        }else{
                            e.style.display = 'none';
                        }
                        break;
                    case 'SourceFolder':
                        if(entryid !== ''){
                            e.href = 'index.php?page=document/list/' + entryid;
                            e.innerHTML = "<img src='views/classic/images/icon/folder.png' />";                
                        }
                        break;
                    case 'PDF':
                        if(entryid !== ''){
                            var isPDF = false;           
                            fileextension = entryname.split('.').pop();
                            if (fileextension.toLowerCase() != 'pdf'){
                                entryname = entryname.replace('.' + fileextension,'.pdf');
                            }
                            link = web + entryname;
                            isPDF = doesFileExist(link);
                            if (isPDF){
                                e.href = link;
                                e.innerHTML = "<img src='views/classic/images/icon/pdf.png' />";
                            }                
                        }                
                        break;
                    default:
                        break;
                }
                break;
            default:
                break;
        }
    })
}

tags = document.querySelectorAll('[class="tags"]');
if(tags){
    tags.forEach(function(tag){
        var arrGroup = (tag.innerText).split(',');
        if (tag.innerText !== ''){
            tag.innerHTML = tags2Html( arrGroup );
        }
    })
}

contactGroups = document.querySelectorAll('[name="ContactGroups"]');
if(contactGroups){
    contactGroups.forEach(function(e){
        e.onchange = function(e2) {
            var pkID = e2.target.getAttribute('pkID');
            var id = "tag";
            if(pkID) 
                id = id + pkID;
            var tags = document.querySelectorAll('[tagID="' + id + '"]')
            tags.forEach(function(tag){
                var arrGroup = (tag.getAttribute('value')).split(',');
                if(arrGroup)
                {
                    var idx;
                    idx = arrGroup.indexOf(e2.target.value);
                    if (idx > -1)
                    {
                        arrGroup.splice(idx,1);
                    }
                    else
                    {
                        arrGroup.push(e2.target.value);
                    }            
                    arrGroup = arrGroup.filter(function(el){ return el;});
                    tag.setAttribute('value',arrGroup);
                    tag.innerHTML = tags2Html( arrGroup );            
                    wsUpdate(tag);
                }
            })            
        }
    })
}

if(deleteEntryType20){
    for (let i = 0; i < deleteEntryType20.length; i++) {
        var e;
        e = deleteEntryType20[i];
        e.style.display = "none";
    }
}


if(fld_handled){
    var name;
    name = "HideHandledNote";
    fld_handled.checked = getCookie(name);
}

if(fld_webroot){
    var url;
    var xhr = new XMLHttpRequest();

    url = fld_webroot.getAttribute('data-dms-server');
    xhr.open("get",url,false);
    xhr.send(null);    
    
    if (xhr.status == 200){   
        fld_webroot.src = "views/classic/images/icon/trafficOK.png";
    }else{
        fld_webroot.src = "views/classic/images/icon/trafficFailed.png";
    }
}

if(activeElectionPeriod){
    var ElectionPeriodID, e;
    ElectionPeriodID = activeElectionPeriod.getAttribute('value');
    if(ElectionPeriodID > 0){
        e = document.getElementById('meetingtypeCard' + ElectionPeriodID);
        if(e)
            e.style.display = 'block'; 
        e = document.getElementById('MeetingTypeID' + ElectionPeriodID);
        if(e)
            e.setAttribute('value',ElectionPeriodID);
    }
}

if(activeMemberType){
    var MemberTypeID, e;
    MemberTypeID = activeMemberType.getAttribute('value');
    if(MemberTypeID > 0){
        e = document.getElementById('memberCard' + MemberTypeID);
        if(e)
            e.style.display = 'block'; 
        e = document.getElementById('MemberID' + MemberTypeID);
        if(e)
            e.setAttribute('value',MemberTypeID);
    }
}

if(activeMeetingLine){
    var MeetingLineID, e;
    MeetingLineID = activeMeetingLine.getAttribute('value');
    if(MeetingLineID > 0){
        e = document.getElementById('editMeetingLine' + MeetingLineID);
        if(e)
            e.style.display = 'block'; 
    }
}

if(a_inbox){
    a_inbox.forEach( function (inbox) {
        var InboxID;
        InboxID = inbox.getAttribute('value');
        if(document.getElementById('editInbox' + InboxID)){
            modifyTodoInbox(InboxID);
        }
    })
}

function initForm(tag,id) {
    var element, oldelement;

    element = document.querySelector( '[' + tag + 'ID="' + id + '"]' );
    oldelement = document.querySelector( '[old' + tag + 'ID="' + id + '"]' );
    if (element){
        if (oldelement){
            switch (element.type) {
                case 'checkbox':
                    if (oldelement.getAttribute('value') == '1'){
                        element.checked = true;
                    }else{
                        element.checked = false;
                    }
                    break;
                default:
                    element.value = oldelement.value;
                    break;
            }
        }
    }
}

function doesFileExist(urlToFile) {  
    var response = $.ajax({
        url: urlToFile,
        type: 'HEAD',
        async: false
    }).status;
    
    if (response != "200") {
        return false;
    } else {
        return true;
    }
}

function modifyContactGroup(Code,Name,Action,read){
    document.getElementById("fieldName").value = Name;
    document.getElementById("fieldCode").value = Code;
    document.getElementById("fieldCode").readOnly = read;
    document.getElementById("fieldAction").value = Action;
}

function modifyAgendaType(TypeID,Name,NoSeries,Action){
    document.getElementById("fieldTypeID").value = TypeID;
    document.getElementById("fieldName").value = Name;
    document.getElementById("fieldNoSeries").value = NoSeries;
    document.getElementById("fieldAction").value = Action;
}

function modifyUser(ID,Name,FullName,PermissionSet,Action,read){
    var e;

    document.getElementById("fieldID").value = ID;
    document.getElementById("fieldName").value = Name;
    document.getElementById("fieldName").readOnly = read;    
    document.getElementById("fieldFullName").value = FullName;
    document.getElementById("fieldPerSet").value = PermissionSet;
    document.getElementById("fieldAction").value = Action;
    e = document.getElementById("fieldPerSet" + Name);
    if(e){
        e.setAttribute('selected','selected');
    }
}

function modifyZobElectionPeriod(ElectionPeriodID,Name,Actual,Action){
    var e;
    document.getElementById("fieldEpElectionPeriodID").value = ElectionPeriodID;
    document.getElementById("fieldEpPeriodName").value = Name;
    e = document.getElementById("fieldEpActual");
    e.value = Actual;
    if (Actual == '1'){
        e.checked = true;
    }else{
        e.checked = false;
    };
    document.getElementById("fieldEpAction").value = Action;
}

function modifyZobMeetingType(MeetingTypeID,ElectionPeriodID,MeetingName,Members,Action){
    document.getElementById("fieldMtMeetingTypeID" + ElectionPeriodID).value = MeetingTypeID;
    document.getElementById("fieldMtElectionPeriodID" + ElectionPeriodID).value = ElectionPeriodID;
    document.getElementById("fieldMtMeetingName" + ElectionPeriodID).value = MeetingName;
    document.getElementById("fieldMtMembers" + ElectionPeriodID).value = Members;
    document.getElementById("fieldMtAction" + ElectionPeriodID).value = Action;
}

function modifyZobMember(MemberID,MeetingTypeID,ContactName,MemberTypeCSY,MemberType,Action){
    document.getElementById("fieldMemMemberID" + MeetingTypeID).value = MemberID;
    document.getElementById("fieldMemMeetingTypeID" + MeetingTypeID).value = MeetingTypeID;
    document.getElementById("fieldMemMemberType" + MeetingTypeID).value = MemberTypeCSY;
    
    document.getElementById(MemberType + MeetingTypeID).selected = 'selected';
    
    document.getElementById("fieldMemContactName" + MeetingTypeID).value = ContactName;
    document.getElementById("fieldMemAction" + MeetingTypeID).value = Action;
}

function modifyTodoInbox(InboxID){
    document.getElementById("editInbox" + InboxID).style.display = 'block';    
}


function validateCheckbox( e ){
    if (e.checked) {
        e.setAttribute('value',1);
    }else{
        e.setAttribute('value',0);
    }
    wsUpdate(e);    
}

function validateCheckboxVote( e, ID ){
    var ee, table;
    table = e.getAttribute('table');
    if (e.checked) {
        e.setAttribute('value',1);
        document.getElementById(table + 'VoteFor' + ID).disabled = false;
        document.getElementById(table + 'VoteAgainst' + ID).disabled = false;
        document.getElementById(table + 'VoteDelayed' + ID).disabled = false;
    }else{
        e.setAttribute('value',0);
        
        ee = document.getElementById(table + 'VoteFor' + ID);
        ee.value = 0;
        ee.disabled = true;

        ee = document.getElementById(table + 'VoteAgainst' + ID);
        ee.value = 0;
        ee.disabled = true;

        ee = document.getElementById(table + 'VoteDelayed' + ID);
        ee.value = 0;
        ee.disabled = true;
    }
    wsUpdate(e);    
}

function allowDrop(ev) {
    ev.preventDefault();
}
function dragattachment(ev){
    var AttachmentID;
    AttachmentID = ev.target.getAttribute('AttachmentID');
    ev.dataTransfer.setData("text",AttachmentID);
}
function dropattachment(ev){
    ev.preventDefault();
    var AttachmentID = ev.dataTransfer.getData("text");
    var MeetingLineID = ev.target.getAttribute("MeetingLineID");    
    if(MeetingLineID){
        window.open("index.php?page=zob/meetingattachment/assign/" + AttachmentID + "/" + MeetingLineID ,"_self")
    }
}

function dropattachmentadv(ev){
    ev.preventDefault();
    var AttachmentID = ev.dataTransfer.getData("text");
    var MeetingLineID = ev.target.getAttribute("MeetingLineID");
    if(MeetingLineID){
        window.open("index.php?page=zob/adv/meetingattachment/assign/" + AttachmentID + "/" + MeetingLineID ,"_self")
    }
}

autosize();
function autosize(){
    var text = $('.autosize');

    text.each(function(){
        $(this).attr('rows',1);
        resize($(this));
    });

    text.on('input', function(){
        resize($(this));
    });
    
    function resize ($text) {
        $text.css('height', 'auto');
        $text.css('height', $text[0].scrollHeight+'px');
    }
}