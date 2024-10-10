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


function pptCreateTable(table,ID,field,fieldlist,roweventaction) {
    wsReadJson(table,ID,field,fieldlist,function(chyba, odpoved){
        if (chyba){
            console.error('Chyba:',chyba);
        }else{
            var jsonData = JSON.parse(odpoved);
            if(jsonData != '<NULL>'){

                // Vytvoření tabulky
                var tableContainerId = 'table-container';
                var tableContainer = document.getElementById(tableContainerId);
                tableContainer.style.display = '';
                tableContainer.innerHTML = '';
                tableContainer.appendChild(pptJsonToHtmlTable(jsonData,tableContainerId,roweventaction));
            }else{
                console.error('Error, not Json format.');
            }
        }
    });
}


function pptJsonToHtmlTable(jsonData,tableContainerId,roweventaction) {
    var table = document.createElement('table');
    var thead = document.createElement('thead');
    var tbody = document.createElement('tbody');

    table.style.width = '70%';
    table.style.fontSize = '18px';
    table.style.margin = '10px';
    
    // Create table header
    var headerRow = document.createElement('tr');
    headerRow.addEventListener("click",function(e){document.getElementById(tableContainerId).style.display = 'none';});
    
    
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
            row.style.cursor = 'pointer';
            row.addEventListener('click',roweventaction)
            row.style.color = 'blue';

            var jsonRow = jsonData[keyRow];
      
            for (var keyCol in jsonRow){
                if (jsonRow.hasOwnProperty(keyCol)) {
                    var value = jsonRow[keyCol];
                    var td = document.createElement('td');
                    td.style.padding = '5px';
                    td.style.border = '1px solid black';
                    td.setAttribute(keyCol,value);
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

function formatContentLine(e){
    var entryNo = e.getAttribute("pkID");
    var e_content = document.getElementById("ContentLine" + entryNo);
    var e_image = document.getElementById("Image" + entryNo);
    var e_fontstyle = document.getElementById("FontStyle" + entryNo)
    var e_align = document.getElementById("Align" + entryNo)
    var e_imageWidth = document.getElementById("ImageWidth" + entryNo)
    var e_imageHeight = document.getElementById("ImageHeight" + entryNo)
    var fontstyle = "";
    if(e_fontstyle)
        fontstyle = e_fontstyle.getAttribute("value");
    var align = "";
    if(e_align)
        align = e_align.getAttribute("value");
    var imageWidth = "";
    if(e_imageWidth)
        imageWidth = e_imageWidth.getAttribute("value");
    var imageHeight = "";
    if(e_imageHeight)
        imageHeight = e_imageHeight.getAttribute("value");

    if(e_content){
        switch (fontstyle) {
            case "IMG":
                if(e_image){
                    e_image.style.display="inline";
                    e_imageWidth.style.display="inline";
                    e_imageHeight.style.display="inline";
                    if(imageWidth > 0)
                        e_image.width = imageWidth;
                    if(imageHeight > 0)
                        e_image.height = imageHeight;
                }
                e_content.style.display="none";
                e_align.style.display="none";                
                break;                    
            default:
                if(e_image){
                    e_image.style.display="none";
                    e_imageWidth.style.display="none";
                    e_imageHeight.style.display="none";
                }
                e_content.style.display="inline";
                e_align.style.display="inline";
                
                //Reset
                e_content.style.textDecoration = "none";
                e_content.style.fontWeight = "normal";
                e_content.style.fontStyle = "normal";
                e_content.style.textAlign = 'Left';
                switch (fontstyle) {
                    case "H1":
                        e_content.style.fontWeight = "bold";
                        e_content.style.fontFamily = "Calibri,Arial";
                        e_content.style.fontSize = "100px";
                        break
                    case "H2":
                        e_content.style.fontWeight = "bold";
                        e_content.style.fontSize = "54px";
                        break
                    case "H3":
                        e_content.style.fontWeight = "bold";
                        e_content.style.fontSize = "34px";
                        break
                    case "T1":
                        e_content.style.fontSize = "26px";
                        break
                    case "T2":
                        e_content.style.fontWeight = "bold";
                        e_content.style.fontSize = "26px";
                        break
                    case "T3":
                        e_content.style.fontSize = "18px";
                        break
                }
                switch (align) {
                    case "Left":
                        e_content.style.textAlign = 'Left';
                        break;
                    case "Center":
                        e_content.style.textAlign = 'Center';
                        break;
                    case "Right":
                        e_content.style.textAlign = 'Right';
                        break;
                }
        }
    }
}
