'use strict';

function refreshRec(e){
    var recID;
    var condition, elements;
    recID = e.getAttribute('recID'); 
    if(recID){
        condition = '[recID="' + recID +'"]';
        elements = document.querySelectorAll('[recID="' + recID +'"]');
        if(elements){
            elements.forEach(function(ee){
                var val = ee.getAttribute('name');
                if(val != 'ContactGroups')
                    updateRecValue(ee);  
            })
        }
    }    
}

// ************************************************************************************
//    DATABASE functions - Update fields 
//
//    Required HTML elenets  :  name, table, pkID
//    JS script              :  wsUpdate(this);
//    element for whow ERROR :  id="pageErrorMesage"
//    ID pro set position    :  id="header" 
//    Http request           :  ?page=general/ws/upd/<table>/<pkID>/<name>
// ************************************************************************************
function wsUpdate(e) {
    
    const Http = new XMLHttpRequest();
    var url;
    var table,pkID,field,newvalue;
    var err,response;
    url = window.location.origin + window.location.pathname;
    if(e){
        table = e.getAttribute('table'); 
        pkID = e.getAttribute('pkID');
        field = e.getAttribute('name'); 
        newvalue = e.value; 
        if(newvalue == undefined)
            newvalue = e.getAttribute('value'); 
        url = url + '?page=general/ws/upd/' + table + '/' + pkID + '/' + field;
        Http.open("POST", url, true);
        Http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        Http.send("value=" + newvalue);
        Http.onreadystatechange=(ee)=>{
            response = Http.responseText;
            if((response == 'OK' ) || (response == '' )){
                console.log(response);
                if((field == 'Close') || (field == 'Actual') || (field == 'NewDocumentNo'))
                    window.location.reload();      
                    refreshRec(e);
            }else{
                console.log(response);
                err = document.getElementById('pageErrorMesage');
                if(err){
                    err.innerText = response;
                    err.style.display = 'block';
                    window.location = "#header";
                }
            }
        }    
    }
}




// ************************************************************************************
//    DATABASE functions - Copy content between two fields 
//
//    Required HTML elenets  :  name, table, pkID, namefrom
//    JS script              :  wscopyFrom(this);
//    element for whow ERROR :  id="pageErrorMesage"
//    ID pro set position    :  id="header" 
//    Http request           :  ?page=general/ws/copyfrom/<table>/<pkID>/<name>/<namefrom>
// ************************************************************************************
function wscopyFrom(e) {
    const Http = new XMLHttpRequest();
    var url;
    var table,pkID,field,fieldfrom;
    var err,response;
    if(!ConfirmAction('Zkopírovat obsah?'))
        return;
    url = window.location.origin + window.location.pathname;
    if(e){
        table = e.getAttribute('table'); 
        pkID = e.getAttribute('pkID');
        field = e.getAttribute('name'); 
        fieldfrom = e.getAttribute('namefrom'); 
        url = url + '?page=general/ws/copyFrom/' + table + '/' + pkID + '/' + field + '/' + fieldfrom;
        Http.open("POST", url, true);
        Http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        Http.send();
        Http.onreadystatechange=(e)=>{
            response = Http.responseText;
            if(response == 'OK' ){
                console.log(response);
                window.location.reload();      
            }else{
                err = document.getElementById('pageErrorMesage');
                if(err){
                    err.innerText = response;
                    err.style.display = 'block';
                    window.location = "#header";
                }
            }
        }    
    }
}

// ************************************************************************************
//    DATABASE functions - Read value from database
//    - internal function
// 
//    Required HTML elenets  :  name, table, pkID
//    JS script              :  wsRefreshField(table,ID,field)
//    RETURN                 :  value | null
//    Http request           :  ?page=general/ws/getValue/<table>/<pkID>/<name>
// ************************************************************************************
function wsRefreshField(table,ID,field) {
    const Http = new XMLHttpRequest();
    var val = null;
    var url;
    url = window.location.origin + window.location.pathname;
    url = url + '?page=general/ws/getValue/' + table + '/' + ID + '/' + field;
    Http.onreadystatechange = function(){
        val = this.responseText;
        if(val == '<NULL>'){ 
            val = null;
        }
        document.getElementById(table + field + ID).value = val;
    }    
    Http.open("POST", url, true);
    Http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    Http.send();
}

// ************************************************************************************
//    Specific DATABASE functions 
//    - refresh specific field of Meetingline table  
// 
//    Required HTML elenets  :  pkID
//    JS script              :  wsRefreshMeetingline(this)
// ************************************************************************************
function wsRefreshMeetingline(e) {
    var table,pkID,value;
    if(e){
        table = 'meetingline'; 
        pkID = e.getAttribute('pkID');        
        wsRefreshField(table,pkID,'VoteFor');        
        wsRefreshField(table,pkID,'VoteAgainst');
        wsRefreshField(table,pkID,'VoteDelayed');
    }
}

// ************************************************************************************
//    Specific DATABASE functions 
//    - refresh specific field of Meetinglinecontent table  
// 
//    Required HTML elenets  :  pkID
//    JS script              :  wsRefreshMeetinglinecontent(this)
// ************************************************************************************
function wsRefreshMeetinglinecontent(e) {
    var table,pkID,value;
    if(e){
        table = 'meetinglinecontent'; 
        pkID = e.getAttribute('pkID');        
        wsRefreshField(table,pkID,'VoteFor');        
        wsRefreshField(table,pkID,'VoteAgainst');
        wsRefreshField(table,pkID,'VoteDelayed');
    }
}

// ************************************************************************************
//    Specific DATABASE functions 
//    - Add meetingattachment to meetinglinepage   
// 
//    JS script              :  wsAddMeetinglinepageattachment(AttachmentID,PageID)
// ************************************************************************************
function wsAddMeetinglinepageattachment(e) {
    var AttachmentID,PageID;
    if(e){
        var PageID = document.getElementById('PageHeader').getAttribute('pageID');
        var val;
        var AttachmentID,Description,DmsEntryID;

        var subord = e.children;
        for (var i=0;i < subord.length ;i++){
            console.log(subord[i]);

            val = subord[i].getAttribute('attachmentid');
            if (val){
                AttachmentID = val;
            }
            val = subord[i].getAttribute('description');
            if (val){
                Description = val;
            }
            val = subord[i].getAttribute('dmsentryid');
            if (val){
                DmsEntryID = val;
            }
        };
        
        const Http = new XMLHttpRequest();
        var EntryNo;
        var url;
        url = window.location.origin + window.location.pathname;
        url = url + '?page=general/ws/addMeetinglineattachment/' + AttachmentID + '/' + PageID;
        Http.open("GET", url, true);
        Http.onreadystatechange = function(){
            if (Http.readyState === 4){
                if(Http.status === 200){
                    EntryNo = Http.responseText;
                    console.log('EntryNo: ',EntryNo);

                    if (EntryNo != 0){
                        var table = document.getElementById('pageattachments');
                        var tr = document.createElement('tr');    
                        tr.setAttribute('id','attachmentEntryNo' + EntryNo)                            
                        tr.className = "blue";
                        var td = document.createElement('td');         
                        td.className = "attachment";  
                        var imgAtt = document.createElement('img');
                        imgAtt.src = "views/classic/images/icon/attachment.png";
                        imgAtt.style.width = "24px";
                        var imgDel = document.createElement('img');
                        imgDel.src = "views/classic/images/icon/delete.png";
                        imgDel.addEventListener('click',function(){wsDeleteMeetinglinepageattachment('attachmentEntryNo' + EntryNo,EntryNo);});
                        var span = document.createElement('span');
                        span.setAttribute('id',DmsEntryID);
                        span.setAttribute('table','meetingattachment');
                        span.setAttribute('name','Description');
                        span.setAttribute('poID',AttachmentID);
                        span.style.fontSize = "24px";
                        span.style.width = "90%";
                        span.style.display = "inline";
                        span.addEventListener('click',function(){pptEditField(this,'input');});
                        span.innerHTML = Description;
                        td.appendChild(imgAtt);
                        td.appendChild(imgDel);                    
                        td.appendChild(span);
                        tr.appendChild(td);
                        table.appendChild(tr);
                    }            
                }else{
                    console.log('Chyba při volání webové služby');
                }
            }       
            if(EntryNo == '<NULL>'){ 
                EntryNo = 0;
            }
        }
        Http.send();
    }    
}

// ************************************************************************************
//    Specific DATABASE functions 
//    - Delete meetingattachment for choice page
// 
//    JS script              :  wsDeleteMeetinglinepageattachment(EntryNo)
// ************************************************************************************
function wsDeleteMeetinglinepageattachment(childName,EntryNo) {
    if(EntryNo){
        const Http = new XMLHttpRequest();
        var val;
        var url;
        url = window.location.origin + window.location.pathname;
        url = url + '?page=general/ws/deleteMeetinglineattachment/' + EntryNo;
        Http.open("GET", url, true);
        Http.onreadystatechange = function(){
            if (Http.readyState === 4){
                if(Http.status === 200){
                    val = Http.responseText;
                    console.log('OK');
                    var table = document.getElementById('pageattachments');
                    var row = document.getElementById(childName);        
                    row.remove();            
                }else{
                    console.log('Chyba při volání webové služby');
                }
            }       
            if(val == '<NULL>'){ 
                val = null;
            }
        }        
        Http.send();
    }    
}

// ************************************************************************************
//    DATABASE functions - Read value from database
//    - internal function
// ************************************************************************************
function updateRecValue(e) {
    const Http = new XMLHttpRequest();
    var val = null;
    var url;
    var table,pkID,field,value;
    table = e.getAttribute('table'); 
    pkID = e.getAttribute('pkID');
    field = e.getAttribute('name'); 
    value = e.getAttribute('value'); 

    url = window.location.origin + window.location.pathname;
    url = url + '?page=general/ws/getValue/' + table + '/' + pkID + '/' + field;
    Http.onreadystatechange = function(){
        if (Http.readyState == 4) {
            if (Http.status == 200) {
                val = Http.responseText;
                if(val == '<NULL>'){ 
                    val = null;
                }else{
                    if(val){
                        if(value){
                            e.value = val;
                        }else{
                            e.innerHTML = formatText(val, field);
                        }
                    }
                }
            }
        }
    }    
    Http.open("GET", url, true);
    Http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    Http.send();
}

// ************************************************************************************
//    DATABASE functions - Read JSON data from database
//    - internal function
// 
//    Required HTML elenets  :  name, table, pkID
//    JS script              :  wsReadJson(table,ID,field,fieldlist)
//    RETURN                 :  JSON data | null
//    Http request           :  ?page=general/ws/getJson/<table>/<pkID>/<name>
// ************************************************************************************
function wsReadJson(table,ID,field,fieldlist,callback) {
    const Http = new XMLHttpRequest();
    var val = null;
    var url;
    url = window.location.origin + window.location.pathname;
    url = url + '?page=general/ws/getJson/' + table + '/' + ID + '/' + field + '/' + fieldlist;
    Http.open("GET", url, true);
    Http.onreadystatechange = function(){
        if (Http.readyState === 4){
            if(Http.status === 200){
                val = Http.responseText;
                callback(null, val);
            }else{
                callback(new Error('Chyba při volání webové služby'));
            }
        }       
        if(val == '<NULL>'){ 
            val = null;
        }
    }    
    Http.send();
}


// ************************************************************************************
//    DATABASE functions - actions 
// ************************************************************************************
// App PART: todo - set remind 
//
function wsSetRemindEntry(ID) {
    const Http = new XMLHttpRequest();
    var url, result;
    url = window.location.origin + window.location.pathname;
    url = url + '?page=todo/WS/setRemind/' + ID;
    Http.open("GET", url);
    Http.send();
    Http.onreadystatechange = (e) => {
        result = Http.responseText;
        if(result == 'OK'){
            window.location.reload();
        }       
    };
}
// App PART: unlink EntryID from Agenda table
// 
function wsUnlinkAgenda(AgendaID) {
    const Http = new XMLHttpRequest();
    var url, result;
    url = window.location.origin + window.location.pathname;
    url = url + '?page=agenda/WS/unlink/' + AgendaID;
    Http.open("GET", url);
    Http.send();
    Http.onreadystatechange = (e) => {
        result = Http.responseText;
        if(result == 'OK'){
            window.location.reload();
        }
    };
}


// ************************************************************************************
//    DATABASE functions - log event to the database 
// ************************************************************************************
function wsLogMessage(message) {
    const Http = new XMLHttpRequest();
    var url;

    url = window.location.origin + window.location.pathname;
    url= url + '?page=general/ws/log';
    Http.open("POST", url, true);
    Http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    Http.send("message=" + message);
    Http.onreadystatechange=(e)=>{
        response = Http.responseText;
        console.log(Http.responseText)      
    }
}

function wsLogView(e) {
    const Http = new XMLHttpRequest();
    var url;
    ID = e.getAttribute('id');
    table = e.getAttribute('table');

    url = window.location.origin + window.location.pathname;
    url= url + '?page=general/ws/log/' + table + '/' + ID;
    Http.open("POST", url, true);
    Http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    Http.send();
    Http.onreadystatechange=(e)=>{
        response = Http.responseText;
        console.log(Http.responseText)      
    }
}

function wsLogDocumentView(ID) {
    const Http = new XMLHttpRequest();
    var url;
    url = window.location.origin + window.location.pathname;
    url= url + '?page=general/ws/log/dmsentry/' + ID;
    Http.open("POST", url, true);
    Http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    Http.send();
    Http.onreadystatechange=(e)=>{
        response = Http.responseText;
        console.log(Http.responseText)      
    }
}

// ************************************************************************************
//    FILE functions - new dmsentry items
//
//    Required HTML elenets  :  parentID
//    JS script              :  wsDmsentry(this);
//    element with TEXT      :  id="newDmsentryText"
//    element for whow ERROR :  id="pageErrorMesage"
//    ID pro set position    :  id="header" 
//    Http request           :  ?document/ws/newDmsentry/<action>/<parentID>/<name>
//      - action             :  Block|Folder|Note
// ************************************************************************************
function wsDmsentry(e, action) {
    const Http = new XMLHttpRequest();
    var url;
    var name, parentID;
    var err,inText,response;
    url = window.location.origin + window.location.pathname;
    if(e){
        inText = document.getElementById('newDmsentryText');
        if(inText)
            name = inText.innerHTML; 
        console.log(name);
        parentID = e.getAttribute('parentID');
        url = url + '?page=document/ws/newDmsentry/' + action + '/' + parentID + '/' + name;
        Http.open("POST", url, true);
        Http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        Http.send();
        Http.onreadystatechange=(e)=>{
            response = Http.responseText;
            if((response == 'OK' ) || (response == '' )){
                console.log(response);
                window.location.reload();      
            }else{
                err = document.getElementById('pageErrorMesage');
                if(err){
                    err.innerText = response;
                    wsLogMessage(response);
                    err.style.display = 'block';
                    window.location = "#header";
                }
            }
        }    
    }
}



