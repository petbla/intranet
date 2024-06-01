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
// Agenda Functions
// ----------------------------------------------------------------------------------------
function getDocumentNo(e,agendaTypeName) {
    wsGetNextDocumentNo(agendaTypeName,false,function(chyba,odpoved){
        if (chyba){
            console.error('Chyba:',chyba);
        }else{
            setElementValue('DocumentNo',odpoved);
            setElementValue('FileName',agendaTypeName + '_' + odpoved);
            setElementValue('Subject',agendaTypeName);
        }    
    });
}

// ----------------------------------------------------------------------------------------
// Form element Functions BY tables
// ----------------------------------------------------------------------------------------
function formRefreshRecord(table){
    var recordId = getElementValue(table + 'RecordID');

    if(recordId == null){
        return;
    }       
    wsGetRecord(table,recordId,function(err,result){
        if (err){
            console.error('Chyba:',err);
        }else{
            if (result){
                var jsonData = JSON.parse(result);

                switch (table) {
                    case 'contact':
                            setElementValue('FullName',jsonData.FullName);
                            setElementValue('Company',jsonData.Company);
                            setElementValue('FirstName',jsonData.FirstName);
                            setElementValue('LastName',jsonData.LastName);
                            setElementValue('Title',jsonData.Title);
                            setElementValue('Address',jsonData.Address);
                            setElementValue('Email',jsonData.Email);
                            setElementValue('Phone',jsonData.Phone);
                            setElementValue('DataBox',jsonData.DataBox);
                            break;
                    case 'dmsentry':
                            console.log(jsonData.Name);
                            setElementValue('ParentName',jsonData.Name);
                            break;
                }
            }
        }
    });
}

function formClearhRecord(table){
    switch (table) {
        case 'contact':
            setElementValue('FullName');
            setElementValue('FirstName');
            setElementValue('LastName');
            setElementValue('Title');
            setElementValue('Function');
            setElementValue('Company');
            setElementValue('Note');
            setElementValue('Phone');
            setElementValue('Email');
            setElementValue('Web');
            setElementValue('Address');
            setElementValue('DataBox');
            break;
    }
}

function selectRecord(e){
    
    var table = e.getAttribute('table');
    var divId = e.getAttribute('divId');
    var param = '';

    switch (table) {
        case 'contact':
            var param = getElementValue('Company');
            break;
        case 'dmsentry':
            var param = getElementValue('ParentName');
            break;
    }  

    wsReadTable(table,param,function(err,result){
        if (err){
            console.error('Chyba:',err);
        }else{
            var selectDiv, selectElement;
            selectDiv = document.getElementById(divId);

            var records = stringToArray(result);
            var selectDivName = 'table + SelectRecordId'

            // SELECT element
            selectElement = document.getElementById(selectDivName);
            if(selectElement){
                selectElement.remove();
            }
            selectElement = document.createElement('select')
            selectElement.id = selectDivName;
            selectElement.onchange = function (e) {
                const selectedOption = selectElement.options[selectElement.selectedIndex];
                selectElement.style.display = "none";

                setElementValue(table + 'RecordID',selectedOption.id);
                formRefreshRecord(table);
            };

            // OPTION elements
            records.forEach((rec) => {
                var id = rec[0];
                var name = rec[1];
                const optionElement = document.createElement('option');
                optionElement.id = id;
                optionElement.value = name;
                optionElement.textContent = name;
                selectElement.appendChild(optionElement);
            });
            if (selectDiv){
                selectDiv.appendChild(selectElement);
            }            
        }
    });
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
                web = setup.getAttribute('webroot');
                entryname = e.getAttribute('filename');
                // <a href="" id="0828E65D-32A8-4E77-8BD8-C188AAB4DCAF" table="dmsentry" name="Obecní úřad\Reklama a grafika\POUKAZ.pdf" extension="pdf" type="30" onclick="wsLogView();">POUKAZ</a>
                // <a href="" SET_HREF id="{ID}" table="dmsentry" name="Title" filename="{Name}" type="{Type}" url="{Url}" onclick="wsLogView();">{Title}</a>
                switch (e.getAttribute('type')) {
                    case 'File':
                    case '30':
                        // File ()
                        var name,extension,id;
                        name = e.getAttribute('filename')
                        if(!name)
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
                entryname = e.getAttribute('filename');

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
                        }
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

// ----------------------------------------------------------------------------------------
// DMS Table elements Functions
// ----------------------------------------------------------------------------------------
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

// ----------------------------------------------------------------------------------------
// Contact Table elements Functions
// ----------------------------------------------------------------------------------------
function modifyContactGroup(Code,Name,Action,read){
    setElementValue('fieldName',Name);
    setElementValue('fieldCode',Code);
    var e = document.getElementById('fieldCode');
    if(e){
        e.readOnly = read;
    }
    setElementValue('fieldAction',Action);
}

// ----------------------------------------------------------------------------------------
// Agenda Table elements Functions
// ----------------------------------------------------------------------------------------
function modifyAgendaType(TypeID,Name,NoSeries,Action){
    setElementValue('fieldTypeID',TypeID);
    setElementValue('fieldName', Name);
    setElementValue('fieldNoSeries', NoSeries);
    setElementValue('fieldAction', Action);
}

// ----------------------------------------------------------------------------------------
// User Table elements Functions
// ----------------------------------------------------------------------------------------
function modifyUser(ID,Name,FullName,PermissionSet,Action,read){
    var e;

    setElementValue('fieldID', ID);
    setElementValue('fieldName', Name);
    e = document.getElementById("fieldName");
    if(e){
        e.readOnly = read;    
    }    
    setElementValue('fieldFullName', FullName);
    setElementValue('fieldPerSet', PermissionSet);
    setElementValue('fieldAction', Action);
    
    e = document.getElementById("fieldPerSet" + Name);
    if(e){
        e.setAttribute('selected','selected');
    }
}

// ----------------------------------------------------------------------------------------
// ZOB Table elements Functions
// ----------------------------------------------------------------------------------------
function modifyZobElectionPeriod(ElectionPeriodID,Name,Actual,Action){
    var e;
    setElementValue('fieldEpElectionPeriodID', ElectionPeriodID);
    setElementValue('fieldEpPeriodName', Name);
    var e = document.getElementById("fieldEpActual");
    e.value = Actual;
    if (Actual == '1'){
        e.checked = true;
    }else{
        e.checked = false;
    };
    document.getElementById("fieldEpAction").value = Action;
}

function modifyZobMeetingType(MeetingTypeID,ElectionPeriodID,MeetingName,Members,Action){
    setElementValue('fieldMtMeetingTypeID' + ElectionPeriodID, MeetingTypeID);
    setElementValue('fieldMtElectionPeriodID' + ElectionPeriodID, ElectionPeriodID);
    setElementValue('fieldMtMeetingName' + ElectionPeriodID, MeetingName);
    setElementValue('fieldMtMembers' + ElectionPeriodID, Members);
    setElementValue('fieldMtAction' + ElectionPeriodID, Action);
}

function modifyZobMember(MemberID,MeetingTypeID,ContactName,MemberTypeCSY,MemberType,Action){
    setElementValue('fieldMemMemberID' + MeetingTypeID, MemberID);
    setElementValue('fieldMemMeetingTypeID' + MeetingTypeID, MeetingTypeID);
    setElementValue('fieldMemMemberType' + MeetingTypeID, MemberTypeCSY);    
    var e = document.getElementById(MemberType + MeetingTypeID);
    if(e){
        e.selected = 'selected';
    }
    setElementValue('fieldMemContactName' + MeetingTypeID, ContactName);
    setElementValue('fieldMemAction' + MeetingTypeID, Action);
}

function modifyTodoInbox(InboxID){
    var e = document.getElementById("editInbox" + InboxID);
    if(e){
        e.style.display = 'block';    
    }
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