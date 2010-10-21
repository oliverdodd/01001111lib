/*	dom+.js - Additional DOM methods or routines.
 *		Requires +.js and prototype.js.
 *
 *	Copyright (c) 2007 Oliver C Dodd
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a 
 *  copy of this software and associated documentation files (the "Software"),
 *  to deal in the Software without restriction, including without limitation
 *  the rights to use, copy, modify, merge, publish, distribute, sublicense,
 *  and/or sell copies of the Software, and to permit persons to whom the 
 *  Software is furnished to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in
 *  all copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL 
 *  THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 *  FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 *  DEALINGS IN THE SOFTWARE.
 */
/*----------------------------------------------------------------------------*\
|* ELEMENT EXTENSIONS - require prototype.js                                  *|
\*----------------------------------------------------------------------------*/
if ((typeof Element != "undefined")&&(Element.addMethods)) Element.addMethods({
	/* Show/Hide based on boolean value                                   */
	showb : function(element,b,useVisibility)
	{
		if (b === undefined) b = true;
		if (useVisibility === undefined) useVisibility = false;
		if (useVisibility)
			$(element).style.visibility = b ? "visible" : "hidden";
		else if (b)	$(element).show();
		else		$(element).hide();
	},
	/* Append/Prepend to an element rather than replace entire contents   */
	append : function(element,html)
	{
		html = typeof html == "undefined" ? "" : html.toString();
		$(element).innerHTML += html.stripScripts();
		setTimeout(function() {html.evalScripts()},10);
		return element;
	},
	prepend : function(element,html)
	{
		html = typeof html == "undefined" ? "" : html.toString();
		$(element).innerHTML = html.stripScripts()+$(element).innerHTML;
		setTimeout(function() {html.evalScripts()},10);
		return element;
	},
	/* EVENTS/CALLBACKS - Because prototype forces "on"+event naming rule */
	addCallback : function(element,event,callback)
	{
		element = $(element);
		try {	if (element.addEventListener)
				element.addEventListener(event,callback,false);
			else	element.attachEvent(event,callback);
		} catch(e) {}
	},
	removeCallback : function(element,event,callback)
	{
		element = $(element);
		try {	if (element.removeEventListener)
				element.removeEventListener(event,callback,false);
			else	element.detachEvent(event,callback);
		} catch(e) {}
	},
	/* XML DOM +                                                          */
	clearChildren :  function(element)
	{
		element = $(element);
		while (element.firstChild)
			element.removeChild(element.firstChild);
	},
	setChild :  function(element,child)
	{
		element = $(element);
		element.clearChildren();
		element.appendChild(child);
	}
});

/*----------------------------------------------------------------------------*\
|* EVENT EXTENSIONS - require prototype.js                                    *|
\*----------------------------------------------------------------------------*/
/* check for caps lock */
if (typeof Event != "undefined")
	Event.capsLock = function(e)
	{
		var key = (typeof e.which != "undefined")
			? e.which
			: e.keyCode;
		var shifted = e.shiftKey;
		return	((key >= 65 && key <= 90) && !shifted) ||
			((key >= 97 && key <= 122) && shifted);
	};


/*----------------------------------------------------------------------------*\
|* FORM EXTENSIONS - require prototype.js                                     *|
\*----------------------------------------------------------------------------*/
/** Ajax.Submit - Perform an AJAX submit with callback or update element
 * @param form			- the form id or object
 * @param callbackOrElement	- the callback function or element to update
 */
Ajax.Submit = function(form,callbackOrElement)
{
	form = $(form);
	if (!form) return;
	if (callbackOrElement === undefined)
		callbackOrElement = form.parentNode;
	var args = form.serialize();
	var method = form.method ? form.method : "POST";
	var url = form.action ? form.action : window.location.href;
	if (isFunction(callbackOrElement))
		var r = new Ajax.Request(url,{
				method:method,
				parameters:args,
				onSuccess:callbackOrElement});
	else if ($(callbackOrElement))
		var r = new Ajax.Updater(callbackOrElement,url,{
				method:method,
				parameters:args,
				evalScripts:true});
	else return true;
	return false;
}

/** Ajax.Upload - Perform an upload using a hidden iframe and a callback or
 *	update element
 * @param form			- the form id or object
 * @param callbackOrElement	- the callback function or element to update
 */
Ajax.Upload = function(form,callbackOrElement)
{
	form = $(form);
	if (!form) return;
	if (callbackOrElement === undefined)
		callbackOrElement = form.parentNode;
	var method = form.method ? form.method : "POST";
	var url = form.action ? form.action : window.location.href;
	var name = "iframe"+Date.microtime();
	var iframe = document.createElement("iframe");
	var onload = isFunction(callbackOrElement)
		? function() {
			callbackOrElement(iFrame.content(this));
		}.bind(iframe)
		: function() {
			Element.update(callbackOrElement,iFrame.content(this));
		}.bind(iframe);
	iframe.name = name;
	iframe.setAttribute("id",	name);
	iframe.setAttribute("name",	name);
	iframe.setAttribute("src",	"about:blank");
	iframe.setAttribute("style",	"display:none;width:0px;height:0px;");
	Event.observe(iframe,"load",onload);
	document.body.appendChild(iframe);
	iframe.hide();
	self.frames[name].name = name;
	form.setAttribute("target",name);
	return true;
}

/** Form.data - return form data as an object or a serialized query string
 * @param form		- the form id or object
 * @param serialize	- serialize as query string?
 */
if (typeof Form != "undefined") Form.data = function(form,serialize)
{
	form = $(form);
	if (!form) return;
	if (serialize === undefined) serialize = false;
	var inputs = form.select("input,select");
	var k,v,input,data = {};
	for (var i = 0; i < inputs.length; i++) {
		input = inputs[i];
		k = input.name ? input.name : input.id;
		v = input.format ? input.format(input.value) : input.value;
		if (k&&!(	(input.type=="checkbox" && !input.checked)||
				(input.type=="radio" && !input.selected)||
				(input.disabled)))
			data[k] = escape(v);
	}
	return serialize ? $H(data).toQueryString() : data;
}

/** Form arguments - pass input id or array of ids, generate a parameter string
 */
$FA = function(id)
{
	args = "";
	if (!id) return args;
	if (isArray(id)) for (var i = 0; i < id.length; i++) args += $FA(id[i]);
	else if (isObject(id)) for (var i in id) args += $FA(id[i]);
	else {	var name = (typeof $(id).name !== "undefined")&&($(id).name) 
			? $(id).name
			: id;
		args = "&"+name+"="+escape($F(id));
	}
	return args;
}

/*----------------------------------------------------------------------------*\
|* ONHOVER                                                                    *|
\*----------------------------------------------------------------------------*/
processOnHover = function()
{
	var elements = $$("[onhover]");
	if (isArray(elements)) elements = Array.toObject(elements);
	for (var i in elements) try {
		elements[i].alt = elements[i].innerHTML;
		Event.observe(elements[i],"mouseover",function() {
			this.innerHTML = this.getAttribute("onhover"); });
		Event.observe(elements[i],"mouseout",function() {
			this.innerHTML = this.alt; });
	} catch(e) { continue; }
};

/*----------------------------------------------------------------------------*\
|* IFRAME EXTENSIONS                                                          *|
\*----------------------------------------------------------------------------*/
if (typeof iFrame == "undefined") iFrame = {};
iFrame.content = function(iframe)
{
	var d;
	if (iframe.contentDocument)	d = iframe.contentDocument;
	else if (iframe.contentWindow)	d = iframe.contentWindow.document;
	else				d = window.frames[iframe.id].document;
	return d.body.innerHTML;
};

/*----------------------------------------------------------------------------*\
|* BROWSER                                                                    *|
\*----------------------------------------------------------------------------*/
/** Fit the window to the screen (takes in optional screen argument for
 *  multi-screen displays.
 *  @param screen_x	= the screen x position, 0 is default
 *  @param screen_y	= the screen y position, 0 is default
 *	         | 1 |
 *	|-2 | -1 | 0 | 1 | 2 |
 *		 |-1 |
 */
window.fitToScreen = function(screen_x,screen_y)
{
	if (screen_x === undefined) screen_x = 0;
	if (screen_y === undefined) screen_y = 0;
	window.moveTo(0,0);
	window.resizeTo(screen.width,screen.height);
	window.moveTo(screen.width*screen_x,screen.height*-screen_y);
};
window.reload = function() { window.location.href = window.location.href; }

if (navigator.appName === "Microsoft Internet Explorer")
	Mouse = {
		left:	1,
		middle:	4,
		right:	2
	};
else	Mouse = {
		left:	0,
		middle:	1,
		right:	2
	};

/*----------------------------------------------------------------------------*\
|* TABLES                                                                     *|
\*----------------------------------------------------------------------------*/
Table = {
	/* CONSTANTS */
	oR_C	: 0,	/* data[row][column] */
	oC_R	: 1	/* data[column][row] */
};
/** Create a table from a 2D object/array
 * @param data	= the object/array
 * @param order	= how is the data structured? (default oR_C)
 *			data[row][column] : oR_C (default)
 *			data[column][row] : oC_R
 */
Table.create = function(data,order)
{
	var table = document.createElement("table");
	if (!isObject(data)) return table;
	var e = empty(data), ci = 0;
	if (order == undefined) order = Table.oR_C;
	if (order == Table.oR_C) {
		for (r in data)
			if (e[r] == undefined) Table.addRow(table,data[r]);
	}
	else {	for (c in data)
		if (e[c] == undefined) Table.addColumn(table,data[c],ci++);
	}
	return table;
};
/** Add an object/array of data to a table as a row
 * @param table	= the table
 * @param data	= the row data object/array
 */
Table.addRow = function(table,data)
{
	if (!isObject(data)) return;
	var ri = table.rows.length;
	table.insertRow(ri);
	Table.fillRow(table.rows[ri],data);
};
Table.fillRow = function(row,data)
{
	var e = empty(data), ci = 0;
	for (c in data) if (e[c] == undefined) {
		row.insertCell(ci);
		row.cells[ci].innerHTML = data[c];
		ci++;
	}
};
/** Add an object/array of data to a table as a column
 * @param table	= the table
 * @param data	= the column data object/array
 * @param ci	= the column index
 */
Table.addColumn = function(table,data,ci)
{
	if (!isObject(data)) return;
	var e = empty(data);
	if (ci == undefined) ci = 0;
	var ri = 0, cc = 0;
	for (r in data) if (e[r] == undefined) {
		if (table.rows.length <= ri)
			table.insertRow(ri);
		cc = table.rows[ri].cells.length - 1;
		while (cc <= ci) table.rows[ri].insertCell(cc++);
		table.rows[ri].cells[ci].innerHTML = data[r];
		ri++;
	}
};
/** Add an object/array of data to a table as a header
 * @param table	= the table
 * @param data	= the header data object/array
 */
Table.addHeader = function(table,data)
{
	if (!isObject(data)) return;
	if (table.tHead == undefined) table.createTHead();
	Table.addRow(table.tHead,data);
};
/** Add an object/array of data to a table as a footer
 * @param table	= the table
 * @param data	= the footer data object/array
 */
Table.addFooter = function(table,data)
{
	if (!isObject(data)) return;
	if (table.tFoot == undefined) table.createTFoot();
	Table.addRow(table.tFoot,data);
};

/*----------------------------------------------------------------------------*\
|* SELECT/OPTIONS                                                             *|
\*----------------------------------------------------------------------------*/
Select = {
	/* CONSTANTS */
	oVV_NK	: 0,	/* <option value="VALUE">KEY</option> */
	oVK_NV	: 1,	/* <option value="KEY">VALUE</option> */
	oVV_NV	: 2,	/* <option value="VALUE">VALUE</option> */
	oVK_NK	: 3	/* <option value="KEY">KEY</option> */
};
/** Create a select box from an object/array
 * @param data	= the object/array
 * @param order	= how is the data structured?
 */
Select.create = function(data,order)
{
	var select = document.createElement("select");
	if (!isObject(data)) return select;
	Select.addOptions(select,data,order);
	return select;
};
/** Create select box options from an object/array
 * @param select	= the select box
 * @param data		= the object/array
 * @param order		= how is the data structured?
 */
Select.addOptions = function(select,data,order)
{
	if (!isObject(data)) return;
	var e = empty(data);
	var i = select.options.length;
	for (k in data) if (e[k] == undefined)
		select.options[i++] = Select.option(k,data[k],order);
};
Select.option = function(k,v,order)
{
	switch (order) {
		case Select.oVK_NV:	return new Option(v,k);
		case Select.oVV_NV:	return new Option(v,v);
		case Select.oVK_NK:	return new Option(k,k);
		case Select.oVV_NK:
		default:		return new Option(k,v);
	}
};
/** Clear a select box
 * @param select	= the select box
 */
Select.clear = function(select) { select.options.length = 0; };
/** Try select a value or default to 1st option
 * @param select	= the select box
 * @param value		= the value to select
 */
Select.selectValue = function(select,value)
{
	if ((value != undefined)&&(value != ""))
				select.value = value;
	else			select.selectedIndex = 0;
	if (select.onchange) select.onchange();
}

/*----------------------------------------------------------------------------*\
|* EMAIL                                                                      *|
\*----------------------------------------------------------------------------*/
/** Decode obfuscated email addresses and open a mailto: link
 */
eMail = {
	mailto:	function(a) {  window.location.href = "mailto:"+a; },
	b64: function(a) { eMail.mailto(Base64.decode(a)); },
	charCodes: function(a) { eMail.mailto(String.fromCharCodes(a)); },
	reversed: function(a) { eMail.mailto(a.reverse()); }
};

/*----------------------------------------------------------------------------*\
|* OUTPUT                                                                     *|
\*----------------------------------------------------------------------------*/
/** Output a message to the browser, uses alert if no valid element is supplied
 *  @param msg		- the string
 *  @param eID		- the element id (optional)
 *  @param append	- append to the element (optional, default = true)
 */
echo = function(msg,eID,append)
{
	 if (append === undefined) append = true;
	 try {	var e = document.getElementById(eID);
		if (e.innerHTML != undefined)
			e.innerHTML = (append) ? e.innerHTML + msg : msg;
		else if (e.value != undefined)
			e.value = (append) ? e.value + msg : msg;
		else	alert(msg);
		return;
	 } catch(e) { alert(msg); } 
};
