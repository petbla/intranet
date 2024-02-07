'use strict';
var editEnable = false;

function pptSetEditable( value ){
    editEnable = value;
}

function pptEditField(selectElement,type) {

    if (!editEnable)
        return;

    var elementID;
    var newElement = document.createElement(type);
    var pkID ,value, name, table, style

    // Selected element to edit its value
    elementID = selectElement.getAttribute('iD');
    pkID = selectElement.getAttribute('pkID');
    name = selectElement.getAttribute('name');
    table = selectElement.getAttribute('table');
    style = selectElement.getAttribute('style');
    value = selectElement.innerHTML;
    
    // New INPUT element for write change value
    if(type == 'input')
        newElement.type = 'text';
    newElement.style = style; 
    newElement.contentEditable = true;
    newElement.setAttribute('name',name);
    newElement.setAttribute('pkID',pkID);
    newElement.setAttribute('table',table); 
    newElement.setAttribute('selectElementID',elementID); 
    newElement.className = 'value autosize bgedit'; 
    newElement.value = value;
    newElement.innerHTML = value;
    newElement.addEventListener('change',function(e) {
        wsUpdate(this);
        var elementID = e.srcElement.getAttribute('selectElementID');
        var value = e.srcElement.value;
        if(value == "")
            value = e.srcElement.value;
        if (elementID){
            var selectElement = document.getElementById(elementID);
            if(selectElement){
                selectElement.style.display = "inline";
                selectElement.innerHTML = value;
                selectElement.value = value;
            };
        };
        this.remove();
    });
    newElement.addEventListener('dblclick',function(e) {
        wsUpdate(this);
        var elementID = e.srcElement.getAttribute('selectElementID');
        var value = e.srcElement.innerHTML;
        if(value == "")
            value = e.srcElement.value;
        if (elementID){
            var selectElement = document.getElementById(elementID);
            if(selectElement){
                selectElement.style.display = "inline";
                selectElement.innerHTML = value;
                selectElement.value = value;
            };
        };
        this.remove();
    });

    selectElement.after(newElement);
    selectElement.style.display = 'none';
    autosize();
}


function pptCreateTable(table,ID,field) {
    
    wsReadJson(table,ID,field,function(chyba, odpoved){
        if (chyba){
            console.error('Chyba:',chyba);
        }else{
            var jsonData = JSON.parse(odpoved);

            // Vytvoření tabulky
            var tableContainer = document.getElementById('table-container');
            tableContainer.appendChild(pptJsonToHtmlTable(jsonData));
        
        }
    });
}


function pptJsonToHtmlTable(jsonData) {
    var table = document.createElement('table');
    var thead = document.createElement('thead');
    var tbody = document.createElement('tbody');

    // Create table header
    var headerRow = document.createElement('tr');
    Object.keys(jsonData[0]).forEach(function(key) {
        var th = document.createElement('th');
        th.textContent = key;
        headerRow.appendChild(th);
    });
    thead.appendChild(headerRow);
    table.appendChild(thead);

    // Create table rows
    for (var keyRow in jsonData){
        if (jsonData.hasOwnProperty(keyRow)) {
            var row = document.createElement('tr');
            var jsonRow = jsonData[keyRow];
      
            for (var keyCol in jsonRow){
                if (jsonRow.hasOwnProperty(keyCol)) {
                    var td = document.createElement('td');
                    var value = jsonRow[keyCol];
                    
                    td.textContent = value;
                    row.appendChild(td);                            
                }
            }
            tbody.appendChild(row);
        }        
    }
    table.appendChild(tbody);
    return table;
}

