'use strict';

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
        url = url + '?page=general/ws/upd/' + table + '/' + pkID + '/' + field;
        Http.open("POST", url, true);
        Http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        Http.send("value=" + newvalue);
        Http.onreadystatechange=(e)=>{
            response = Http.responseText;
            if((response == 'OK' ) || (response == '' )){
                console.log(response)      
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



