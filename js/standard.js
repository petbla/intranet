function Browser() {

  var ua, s, i;

  this.isIE    = false;  // Internet Explorer
  this.isOP    = false;  // Opera
  this.isNS    = false;  // Netscape
  this.version = null;

  ua = navigator.userAgent;

  s = "Opera";
  if ((i = ua.indexOf(s)) >= 0) {
    this.isOP = true;
    this.version = parseFloat(ua.substr(i + s.length));
    return;
  }

  s = "Netscape6/";
  if ((i = ua.indexOf(s)) >= 0) {
    this.isNS = true;
    this.version = parseFloat(ua.substr(i + s.length));
    return;
  }

  s = "Gecko";          // FireFox, Chrome
  if ((i = ua.indexOf(s)) >= 0) {
    this.isNS = true;
    this.version = 6.1;
    return;
  }

  s = "MSIE";
  if ((i = ua.indexOf(s))) {
    this.isIE = true;
    this.version = parseFloat(ua.substr(i + s.length));
    return;
  }
}

var browser = new Browser();
var activeButton = null;

if (browser.isIE)
  document.onmousedown = pageMousedown;
else
  document.addEventListener("mousedown", pageMousedown, true);

function pageMousedown(event) {

  var el;

  if (activeButton == null)
    return;

  if (browser.isIE)
    el = window.event.srcElement;
  else
    el = (event.target.tagName ? event.target : event.target.parentNode);

  if (el == activeButton)
    return;

  if (getContainerWith(el, "DIV", "menu") == null) {
    resetButton(activeButton);
    activeButton = null;
  }
}

function buttonClick(event, menuId) {

  var button;

  if (browser.isIE)
    button = window.event.srcElement;
  else
    button = event.currentTarget;

  button.blur();

  if (button.menu == null) {
    button.menu = document.getElementById(menuId);
    if (button.menu.isInitialized == null)
      menuInit(button.menu);
  }

  if (activeButton != null)
    resetButton(activeButton);

  if (button != activeButton) {
    depressButton(button);
    activeButton = button;
  }
  else
    activeButton = null;

  return false;
}

function buttonMouseover(event, menuId) {

  var button;

  if (browser.isIE)
    button = window.event.srcElement;
  else
    button = event.currentTarget;

  if (activeButton != null && activeButton != button)
    buttonClick(event, menuId);
}

function depressButton(button) {

  var x, y;

  button.className += " menuButtonActive";

  x = getPageOffsetLeft(button);
  y = getPageOffsetTop(button) + button.offsetHeight;

  if (browser.isIE) {
    x += button.offsetParent.clientLeft;
    y += button.offsetParent.clientTop;
  }

  button.menu.style.left = x + "px";
  button.menu.style.top  = y + "px";
  button.menu.style.visibility = "visible";
}

function resetButton(button) {

  removeClassName(button, "menuButtonActive");

  if (button.menu != null) {
    closeSubMenu(button.menu);
    button.menu.style.visibility = "hidden";
  }
}

function menuMouseover(event) {

  var menu;

  if (browser.isIE)
    menu = getContainerWith(window.event.srcElement, "DIV", "menu");
  else
    menu = event.currentTarget;

  if (menu.activeItem != null)
    closeSubMenu(menu);
}

function menuItemMouseover(event, menuId) {

  var item, menu, x, y;

  if (browser.isIE)
    item = getContainerWith(window.event.srcElement, "A", "menuItem");
  else
    item = event.currentTarget;
  menu = getContainerWith(item, "DIV", "menu");

  if (menu.activeItem != null)
    closeSubMenu(menu);
  menu.activeItem = item;

  item.className += " menuItemHighlight";

  if (item.subMenu == null) {
    item.subMenu = document.getElementById(menuId);
    if (item.subMenu.isInitialized == null)
      menuInit(item.subMenu);
  }

  x = getPageOffsetLeft(item) + item.offsetWidth;
  y = getPageOffsetTop(item);

  var maxX, maxY;

  if (browser.isIE) {
    maxX = Math.max(document.documentElement.scrollLeft, document.body.scrollLeft) +
      (document.documentElement.clientWidth != 0 ? document.documentElement.clientWidth : document.body.clientWidth);
    maxY = Math.max(document.documentElement.scrollTop, document.body.scrollTop) +
      (document.documentElement.clientHeight != 0 ? document.documentElement.clientHeight : document.body.clientHeight);
  }
  if (browser.isOP) {
    maxX = document.documentElement.scrollLeft + window.innerWidth;
    maxY = document.documentElement.scrollTop  + window.innerHeight;
  }
  if (browser.isNS) {
    maxX = window.scrollX + window.innerWidth;
    maxY = window.scrollY + window.innerHeight;
  }
  maxX -= item.subMenu.offsetWidth;
  maxY -= item.subMenu.offsetHeight;

  if (x > maxX)
    x = Math.max(0, x - item.offsetWidth - item.subMenu.offsetWidth
      + (menu.offsetWidth - item.offsetWidth));
  y = Math.max(0, Math.min(y, maxY));

  item.subMenu.style.left = x + "px";
  item.subMenu.style.top  = y + "px";
  item.subMenu.style.visibility = "visible";

  if (browser.isIE)
    window.event.cancelBubble = true;
  else
    event.stopPropagation();
}

function closeSubMenu(menu) {

  if (menu == null || menu.activeItem == null)
    return;

  if (menu.activeItem.subMenu != null) {
    closeSubMenu(menu.activeItem.subMenu);
    menu.activeItem.subMenu.style.visibility = "hidden";
    menu.activeItem.subMenu = null;
  }
  removeClassName(menu.activeItem, "menuItemHighlight");
  menu.activeItem = null;
}

function menuInit(menu) {

  var itemList, spanList;
  var textEl, arrowEl;
  var itemWidth;
  var w, dw;
  var i, j;

  if (browser.isIE) {
    menu.style.lineHeight = "2.5ex";
    spanList = menu.getElementsByTagName("SPAN");      
    for (i = 0; i < spanList.length; i++)      
      if (hasClassName(spanList[i], "menuItemText")) {
        spanList[i].style.fontFamily = "Webdings";
        spanList[i].firstChild.nodeValue = "4";
      }
  }

  itemList = menu.getElementsByTagName("A"); 
  if (itemList.length > 0){                                    
    itemWidth = itemList[0].offsetWidth;
  }else
    return;

  for (i = 0; i < itemList.length; i++) {   
    spanList = itemList[i].getElementsByTagName("SPAN");
    
    textEl  = null;
    arrowEl = null;
    
    for (j = 0; j < spanList.length; j++) {
      if (hasClassName(spanList[j], "menuItemText"))
        textEl = spanList[j];
      if (hasClassName(spanList[j], "menuItemArrow")) {
        arrowEl = spanList[j];
      }
    }
    if (textEl != null && arrowEl != null) {
      textEl.style.paddingRight = (itemWidth 
        - (textEl.offsetWidth + arrowEl.offsetWidth)) + "px";
      if (browser.isOP)
        arrowEl.style.marginRight = "0px";
    }
  }

  if (browser.isIE) {
    w = itemList[0].offsetWidth;
    itemList[0].style.width = w + "px";
    dw = itemList[0].offsetWidth - w;
    w -= dw;
    itemList[0].style.width = w + "px";
  }


  menu.isInitialized = true;
}

function getContainerWith(node, tagName, className) {

  while (node != null) {
    if (node.tagName != null && node.tagName == tagName &&
        hasClassName(node, className))
      return node;
    node = node.parentNode;
  }

  return node;
}

function hasClassName(el, name) {

  var i, list;

  list = el.className.split(" ");
  for (i = 0; i < list.length; i++)
    if (list[i] == name)
      return true;

  return false;
}

function removeClassName(el, name) {

  var i, curList, newList;

  if (el.className == null)
    return;

  newList = new Array();
  curList = el.className.split(" ");
  for (i = 0; i < curList.length; i++)
    if (curList[i] != name)
      newList.push(curList[i]);
  el.className = newList.join(" ");
}

function getPageOffsetLeft(el) {

  var x;

  x = el.offsetLeft;
  if (el.offsetParent != null)
    x += getPageOffsetLeft(el.offsetParent);

  return x;
}

function getPageOffsetTop(el) {

  var y;

  y = el.offsetTop;
  if (el.offsetParent != null)
    y += getPageOffsetTop(el.offsetParent);

  return y;
}

function del( ){
  if( confirm( delShure ) ) 
    return true;
  else 
    return false
} // end function del

function select_template(opt,objCislo)
{
  var textBody;
  var textSub;
  var datum=new Date();
  var datText;
  
  try
    {
    datText=datum.getDate().toString()+"."+datum.getMonth().toString()+"."+datum.getUTCFullYear().toString();
    
    textSub="Změna stavu objednávky číslo "+objCislo;
    textBody="Dobrý den,\n\n";  
    switch(opt.value)      
    {
      case "1":
        //Potvrzení přijetí a zpr
        textSub+=" - přijata";
        textBody+="Vaše objednávka č. "+objCislo+" byla přijata ke zpracování a v co nejkratší době bude vyřízena.\n\nDěkujeme za Váš nákup.";
        break;
      case "2":
        // Odeslání - platba převodem
        textSub+=" - odeslána";
        textBody+="Vaše objednávka č. "+objCislo+" byla dne "+datText+" odeslána na vaši adresu. Její doručení můžete očekávat do tří pracovních dnů.\n\nDěkujeme za Váš nákup";
        break;
      case "3":
        // Odeslání - platba převodem
        textSub+=" - odeslána";
        textBody+="Vaše objednávka č. "+objCislo+" byla dne "+datText+" odeslána dobírkou na vaši adresu. Její doručení můžete očekávat do tří pracovních dnů.\n\nDěkujeme za Váš nákup";
        break;
      case "4":
        // Odeslání - platba převodem
        textSub="Zpráva z Bijoux Maja";
        textBody+="rádi bychom Vám připomněli, že u nás máte připravenu objednávku číslo "+objCislo+" na Vámi objednané zboží. Částku prosím zaplaťte na náš účet 670100-2207961203/6210, variabilní symbol "+objCislo+".\n\nDěkujeme za nákup";
        break;
      case "5":
        // Odeslání - platba převodem
        textSub="Zpráva z Bijoux Maja";
        textBody+="\n";
        break;
    }
    textBody+="\n\nBijoux Maja\nwww.bijoux-maja.cz";
    
    document.getElementById('emlSub').value=textSub;
    document.getElementById('emlBod').value=textBody;
    }
  catch(err)
    {
      txt="There was an error on this page.\n\n";
      txt+="Error description: " + err.description + "\n\n";
      txt+="Click OK to continue.\n\n";
      alert(txt);    
    }
}
function previewImage( opt, imgsrc, imgtitle, e)
{
  var x,y;
  
  switch(opt)      
    {
      case 1:
        document.getElementById('imageshow').src=imgsrc;
        document.getElementById('imageshow').title = imgtitle;
        document.getElementById('imageshow').style.display='block';
        x = e.clientX;
        if (x > (document.getElementById('imageshow').width + 20))
          x = x - document.getElementById('imageshow').width - 20; 
        
        y = e.clientY;        
        if ((y + document.getElementById('imageshow').height) > 600)
          y = 600 - document.getElementById('imageshow').height; 
        
        document.getElementById('imageshow').style.top= y + 'px';
        document.getElementById('imageshow').style.left= x + 'px';
        
        break;
      case 0:
        document.getElementById('imageshow').style.display='none';
        break;
    }
}
function showProductImage( idElement, imgsrc, imgtitle)
{
  document.getElementById('product_detail').src=imgsrc;
  document.getElementById('product_detail').title = imgtitle;
}

function showButton(btnId) {
  document.getElementById(btnId).style.visibility = "visible";
}

function updateShippingCost ()
{
  var subtotal,total,shippingCost,paymentCost;
  var shipmeth,paymeth,pos;
   
  shipmeth = document.getElementById('shipping_method').value;
  pos = shipmeth.indexOf(",");
  if (pos > 0){
    shippingCost = parseInt(shipmeth.substr(pos + 1));
  }else
    shippingCost = 0;

  paymeth = document.getElementById('payment_method').value;
  pos = paymeth.indexOf(",");
  if (pos > 0){
    paymentCost = parseInt(paymeth.substr(pos + 1));
  }else
    paymentCost = 0;
  
  subtotal = parseInt(document.getElementById('varBasketSubtotal').innerHTML);
  total = subtotal + shippingCost + paymentCost;
  
  if (paymentCost > 0){
    document.getElementById('varPaymentCost').innerHTML = paymentCost.toFixed(2) ;
  }else
    document.getElementById('varPaymentCost').innerHTML = '' ;
  document.getElementById('varShippingCost').innerHTML = shippingCost.toFixed(2) ;
  document.getElementById('varBasketTotal').innerHTML = total.toFixed(2) ;
}

function setAscDesc(c_name)
{
  value = getCookie(c_name);
  switch(value)      
  {
    case "DESC":
      value = "ASC"; 
      break;
    case "ASC":
      value = "DESC"; 
      break;
  }
  setCookie(c_name,value)
}


function setCookie(c_name,value)
{
  var exdays=1;
  var exdate=new Date();
  exdate.setDate(exdate.getDate() + exdays);
  var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
  document.cookie=c_name + "=" + c_value;
}

function getCookie(c_name)
{
  var i,x,y,ARRcookies=document.cookie.split(";");
  for (i=0;i<ARRcookies.length;i++)
  {
    x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
    y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
    x=x.replace(/^\s+|\s+$/g,"");
    if (x==c_name)
      {
      return unescape(y);
      }
    }
}