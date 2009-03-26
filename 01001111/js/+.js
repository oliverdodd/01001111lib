/*	+.js - Various conversion and formatting functions extending the native
 *		Javascript objects.
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
|* TYPES                                                                      *|
\*----------------------------------------------------------------------------*/
isDefined = function(o) { return (o !== undefined); };
isUndefined = function(o) { return (o === undefined); };
isNull = function(o) { return (o === null); };

getObjectType = function(o) { return Object.prototype.toString.call(o); };
isObject = function(o) { return (getObjectType(o) == "[object Object]"); };
isArray = function(a) { return (getObjectType(a) == "[object Array]"); };
isRegExp = function(r) { return (getObjectType(r) == "[object RegExp]"); };
isFunction = function(o) { return (getObjectType(o) == "[object Function]"); };

type = function(o)
{
	var t = typeof o;
	return (t == "object") ? (isArray(o) ? "array" : t) : t;
};
empty = function(o)
{
	switch(type(o)) {
		case "string":	return "";
		case "number":	return 0;
		case "boolean":	return false;
		case "function":return function(){};
		case "array":	return [];
		case "object":	return {};
		default:	return null;
	}
};

/*----------------------------------------------------------------------------*\
|* ARRAYS                                                                     *|
\*----------------------------------------------------------------------------*/
/** Searches an array for a specific value.
 * @param a	= the array
 * @param v	= the value to search for
 */
Array.search = function(a,v)
{
	for (var i = 0; i < a.length; i++)
		if (a[i] == v) return i;
	return -1;
};

/** Searches an array of objects for a specific key/value pair.
 * @param a	= the array of objects
 * @param k	= the key to search for
 * @param v	= the value to search for
 */
Array.findObjects = function(a,k,v)
{
	var objects = [], i = 0;
	for (n in a)
		if (Array.search(Object.search(a[n],v),k).length)
			objects[i++] = a[n];
	return objects;
};

/** Converts an array to an object/hash.
 * @param a	= the array of objects
 */
Array.toObject = function(a)
{
	var o = {};
	for (var i = 0; i < a.length; i++) o[i] = a[i];
	return o;
};

/*----------------------------------------------------------------------------*\
|* OBJECTS                                                                    *|
\*----------------------------------------------------------------------------*/
/** Return the keys of an object.
 * @param o	= the object
 */
Object.keys = function(o)
{
	var keys = [], i = 0;
	for (k in o) keys[i++] = k;
	return keys;
};

/** Merge the attributes of two objects.  Later objects take precedence over
 *	earlier arguments so the last value will be used if keys intersect.
 * @param o1,o2,o3...	= the variable object arguments
 */
Object.merge = function()
{
	var m = {}, o = {};
	for (var i = arguments.length; i >= 0 ; i--) {
		o = arguments[i];
		if (!isObject(o)) continue;
		for (var k in o) if (m[k] === undefined) m[k] = o[k];
	}
	return m;
};

/** Count the number of elements in an object
 *	note: check your own types
 */
Object.count = function(o)
{
	var i = 0;
	for(var k in o) if(!(k in Object.prototype)) i++;
	return i;
};

/** Searches an object for a specific value.
 * @param o	= the object
 * @param v	= the value to search for
 */
Object.search = function(o,v)
{
	var keys = [], i = 0;
	for (k in o) if (o[k] == v) keys[i++] = k;
	return keys;
};

/** Unset, like the php unset() method, deletes a key completely from an
 *	Object/Hash or Array.
 * @param o	= the object
 * @param k	= the key
 */
unset = function(o,k) { delete o[k]; };

/*----------------------------------------------------------------------------*\
|* _ OBJECT (helpful functions encased in a wrapper class)                    *|
\*----------------------------------------------------------------------------*/

_ = {/** V() - return the value at a certain key of an array or object if it
	 *	exists or a default if it is undefined
	 *  @param o	= the object
	 *  @param k	= the key
	 *  @param d	= the default
	 */
	V : function(o,k,d) { return isDefined(o[k]) ? o[k] : d; },
	
	/** K() - return the first key of a certain value in an array or object
	 *	if it exists or a default if it is undefined
	 *  @param o	= the object
	 *  @param v	= the value
	 *  @param d	= the default
	 */
 	K : function(o,v,d) { for (k in o) if (o[k] === v) return k; return d; },
	
	/** F() - call a function if it exists and is callable, if not return a
	 *	default value
	 *  @param f	= the function
	 *  @param d	= the default
	 */
 	F : function(f,d) { return (isDefined(f)&&(isFunction(f))) ? f() : d; }
};

/*----------------------------------------------------------------------------*\
|* NUMBERS/MATH                                                               *|
\*----------------------------------------------------------------------------*/

/** Number.toBase() - convert a decimal integer to another base
 *  @param n	= the base - if 0 or undefined defaults to the length of sym
 *  @param sym	= an array or string of symbols, defaults to alphanumeric chars
 */
Number.prototype.toBase = function(n,sym)
{
	if ((isUndefined(sym))||isNull(sym))
		sym = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	if (!isArray(sym)) sym = sym.toString().split("");
	n = ((isUndefined(n))||(n <= 1)) ? sym.length : n;
	var dec = this; var s = "";
	do { s = sym[dec%n] + s; dec = Math.floor(dec/n); } while(dec>0);
	return s;
};

/** Number.chunk() - chunk a number into segments of length n, delimited by d
 *	 and padded with char p (converts to a string and uses String.chunk())
 */
Number.prototype.chunk = function(n,d,p) {return this.toString().chunk(n,d,p);};

/** Number.ordinalSuffix() -return the ordinal suffix (st,nd,rd,th) for a number
 */
Number.prototype.ordinalSuffix = function()
{
	var os = ["","st","nd","rd","th"];
	return (this < 4) ? os[this] : os[4];
};

/** bound() - ensure a number falls between a min and a max value
 *  @param n	= the number
 *  @param min	= minimum value
 *  @param max	= maximum value
 */
Math.bound = function(n,min,max)
{
	if (isUndefined(min)) min = 0;
	if (isUndefined(max)) max = 0;
	//if min > max, switch
	if (min > max) { m = min; min = max; max = m; }
	return Math.min(Math.max(n,min),max);
};

/** rollover() - roll a number over if it surpasses a certain value
 *  @param n	= the number
 *  @param max	= maximum value
 */
Math.rollover = function(n,max)
{
	if (isUndefined(max)||isNaN(max)||(max== 0)) return 0;
	while(n < 0) n += max;
	while(n >= max) n -= max;
	return n;
};

/*----------------------------------------------------------------------------*\
|* STRINGS                                                                    *|
\*----------------------------------------------------------------------------*/

/** Replace all of occurences of a string within a string
 *	@find		- string or array of strings to find
 *	@replace	- string or array of strings to replace
 */
String.prototype.replaceAll = function(find,replace)
{
	var newStr = "";
	var oldStr = this.toString();
	var i = 0;
	if (isArray(find)) {
		newStr = oldStr;
		if (!isArray(replace))
			for (i = 0; i < find.length; i++)
				newStr = newStr.replaceAll(find[i],replace);
		else if (replace.length == find.length)
			for (i = 0; i < find.length; i++)
				newStr = newStr.replaceAll(find[i],replace[i]);
	}
	else {	while((i = oldStr.indexOf(find)) !== -1) {
			newStr += oldStr.substr(0,i) + replace;
			i += find.length;
			oldStr = oldStr.substr(i); }
		if (oldStr) newStr += oldStr;
	}
	return newStr;
};

String.prototype.addslashes = function()
{
	return this.replaceAll(["\\",'"'],["\\\\",'\\"']);
};

String.prototype.toNumber = function() { return Number(this); };

String.prototype.reverse = function()
{
	var s = "";
	var i = this.length;
	while (i--) s += this[i];
	return s;
};

String.prototype.ltrim = function() { return this.replace(/^\s+/,""); };
String.prototype.rtrim = function() { return this.replace(/\s+$/,""); };
String.prototype.trim = function() { return this.ltrim().rtrim(); };


/** Character Codes Functions - convert a string to and from an array or
 *	delimited string of character codes.
 *  @param d	= the delimiter
 */
String.prototype.charCodes = function()
{
	var a = [];
	for (var i = 0; i < this.length; i++) a[i] = this.charCodeAt(i);
	return a;
};
String.prototype.charCodesStr = function(d)
{
	if (isUndefined(d)) d = ",";
	return this.charCodes().join(d);
};
String.fromCharCodes = function(ca)
{
	var s = "";
	for (var i = 0; i < ca.length; i++)
		s += String.fromCharCode(parseInt(ca[i]));
	return s;
};
String.fromCharCodeStr = function(s,d)
{
	if (isUndefined(d)) d = ",";
	return String.fromCharCodes(s.split(d));
};

/** String.pad() - pad a string with char p on the left (default) or right
 *  @param n	= the length of the padded string
 *  @param p	= the pad string, defaults to " "
 *  @param t	= the padding type, left (t < 0), right (t > 0),
 *		or both (t === 0), defaults to right
 */
String.prototype.pad = function(n,p,t)
{
	//check inputs/assign defaults
	if (isUndefined(n)||isNull(n)||(n === 0)) return this;
	if (isUndefined(p)||isNull(p)) p = " ";
	if (isUndefined(t)||isNull(t)) t = 1;
	if (this.length >= n) return this;
	if (p === "") return this;
	//initialize variables
	var l = r = ""; var ln = rn = 0; var tn = n - this.length;
	//determine pad lengths for either side
	if (t < 0) ln = tn;
	else if (t > 0) rn = tn;
	else if	(t == 0) { ln = Math.floor(tn/2); rn = tn - ln; }
	//create pad strings
	for (var li = 0; li < ln; li++) l += p; l = l.substr(0,ln);
	for (var ri = 0; ri < rn; ri++) r += p; r = r.substr(0,rn);
	return l+this+r;
};

/** String.chunk() - chunk a string into segments of length n, delimited by d
 *	and padded with char p
 *  @param n	= the length of the chunk
 *  @param d	= the delimiter character, defaults to " "
 *  @param p	= the padding character, defaults to ""
 *  @param t	= the padding type, left (t < 0), right (t > 0),
 *		or both (t === 0), defaults to LEFT
 */
String.prototype.chunk = function(n,d,p,t)
{
	if (isUndefined(n)||isNull(n)||(n === 0)) return this;
	if (isUndefined(d)||isNull(d)) d = " ";
	if (isUndefined(p)||isNull(p)) p = "";
	if (isUndefined(t)||isNull(t)) t = -1;
	var s = this;
	var l = s.length;
	var m = (n > l)	? n - l : Math.ceil(l/n)*n - l;
	s = s.pad(l+m,p,t);
	var i = ((p !== "")&&(p !== null)) ? n : n-m;
	var c = s.substr(0,i);
	for (i; i < s.length; i+=n) c += d+s.substr(i,n);
	return c;
};

/** String.toDec() - convert a number from another base to a decimal number
 *  @param n	= the base - if 0 or undefined defaults to the length of sym
 *  @param sym	= an array or string of symbols, defaults to alphanumeric chars
 */
String.prototype.toDec = function(n,sym)
{
	if (isUndefined(sym)||isNull(sym))
		sym = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	if (!isArray(sym)) sym = sym.toString().split("");
	n = (isUndefined(n)||(n <= 1)) ? sym.length : n;
	var dec = 0;
	var s = this.toUpperCase();
	for (var i = 0; i < s.length; i++)
		dec += _.K(sym,s.charAt(i),0) * Math.pow(n,s.length-i-1);
	return dec;
};

/** implode - php style array/object join
 *  @param d	= the delimeter
 *  @param o	= the array/object
 */
implode = function(d,o)
{
	if (isArray(o)) return o.join(d);
	var s = "";
	for (var k in o) s += ((!s) ? "" : d)+o[k];
	return s;
};

/** explode - php style string split
 *  @param d	= the delimeter
 *  @param s	= the string
 */
explode = function(d,s)
{
	var o = {}, i = 0, l = 0;
	while(s) {
		i = s.indexOf(d);
		if (i == -1) i = s.length;
		o[l++] = s.substring(0,i);
		s = s.substring(i+1);
	}
	return o;
};


/*----------------------------------------------------------------------------*\
|* DATE                                                                       *|
\*----------------------------------------------------------------------------*/
/** Date object for this instant */
Date.now = function() { return (new Date()); };
/** Timestamp in milliseconds */
Date.microtime = function() { return Date.now().getTime(); };
/** Timestamp in seconds */
Date.time = function() { return Math.floor(Date.microtime()/1000); };
/** Convert time in milliseconds to a date object */
Date.fromTime = function(ms) { var d = new Date(); d.setTime(ms); return d; };
/** Convert time to GMT */
Date.prototype.toGMT = function()
{
	var d = Date.fromTime(this.getTime());;
	d.setFullYear(		d.getUTCFullYear());
	d.setMonth(		d.getUTCMonth());
	d.setDate(		d.getUTCDate());
	d.setHours(		d.getUTCHours());
	d.setMinutes(		d.getUTCMinutes());
	d.setSeconds(		d.getUTCSeconds());
	d.setMilliseconds(	d.getUTCMilliseconds());
	return d;
};
/** Convert time from GMT */
Date.prototype.fromGMT = function()
{
	var d = Date.fromTime(this.getTime());;
	d.setUTCFullYear(	d.getFullYear());
	d.setUTCMonth(		d.getMonth());
	d.setUTCDate(		d.getDate());
	d.setUTCHours(		d.getHours());
	d.setUTCMinutes(	d.getMinutes());
	d.setUTCSeconds(	d.getSeconds());
	d.setUTCMilliseconds(	d.getMilliseconds());
	return d;
};
/** Add Functions */
Date.prototype.add = function(y,m,d,h,i,s,u)
{
	if (isFinite(y)) this.setFullYear(	this.getFullYear()	+y);
	if (isFinite(m)) this.setMonth(		this.getMonth()		+m);
	if (isFinite(d)) this.setDate(		this.getDate()		+d);
	if (isFinite(h)) this.setHours(		this.getHours()		+h);
	if (isFinite(i)) this.setMinutes(	this.getMinutes()	+i);
	if (isFinite(s)) this.setSeconds(	this.getSeconds()	+s);
	if (isFinite(u)) this.setMilliseconds(	this.getMilliseconds()	+u);
	return this;
}
Date.prototype.addYears		= function(y) { return this.add(y); }
Date.prototype.addMonths	= function(m) { return this.add(0,m); }
Date.prototype.addDays		= function(d) { return this.add(0,0,d); }
Date.prototype.addHours		= function(h) { return this.add(0,0,0,h); }
Date.prototype.addMinutes	= function(i) { return this.add(0,0,0,0,i); }
Date.prototype.addSeconds	= function(s) { return this.add(0,0,0,0,0,s); }
Date.prototype.addMilliseconds	= function(u) { return this.add(0,0,0,0,0,0,u); }

/** Date constants */
Date.Months	= ["January","February","March","April","May","June","July",
		   "August","September","October","November","December"];
Date.months	= ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct",
		   "Nov","Dec"];
Date.Days	= ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday",
		   "Saturday"];
Date.days	= ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"];

/** Convert the date to a hash of values based on the php date() format function
 *	Y	= A full numeric representation of a year
 *	y	= A two digit representation of a year	Examples: 99 or 03
 *	L	= Whether it"s a leap year
 *	n	= Numeric representation of a month, without leading zeros
 *	m	= Numeric representation of a month, with leading zeros
 *	F	= A full textual representation of a month
 *	M	= A short textual representation of a month, three letters
 *	t	= Number of days in the given month
 *	j	= Day of the month without leading zeros
 *	d	= Day of the month, 2 digits with leading zeros
 *	S	= English ordinal suffix for the day of the month
 *	w	= Numeric representation of the day of the week
 *	D	= A textual representation of a day, three letters
 *	l	= A full textual representation of the day of the week
 *	N	= ISO-8601 numeric representation of the day of the week
 *	G	= 24-hour format of an hour without leading zeros
 *	H	= 24-hour format of an hour with leading zeros
 *	g	= 12-hour format of an hour without leading zeros
 *	h	= 12-hour format of an hour with leading zeros
 *	i	= Minutes with leading zeros
 *	min	= Minutes, without leading zeros, not in php
 *	s	= Seconds, with leading zeros
 *	sec	= Seconds, without leading zeros, not in php
 *	u	= Milliseconds, with leading zeros, not part of php
 *	a	= Lowercase Ante meridiem and Post meridiem
 *	A	= Uppercase Ante meridiem and Post meridiem
 *	to	= timezone offset in minutes
 *	O	= Difference to Greenwich time (GMT) in hours Example: +0200
 *	P	= Difference to Greenwich time (GMT) with : between hrs and min
 */
Date.prototype.toHash = function()
{
	/* year */
	var Y	= this.getFullYear();
	var y	= Y.toString().substr(2,2);
	var L	= (Y%100 == 0)?(Y%400 == 0):(Y%4 == 0);
	/* month */
	var _t	= [31,((L)?29:28),31,30,31,30,31,31,30,31,30,31];
	var n	= this.getMonth() + 1;
	var m	= n.chunk(2,"",0);
	var F	= Date.Months[n-1];
	var M	= Date.months[n-1];
	var t	= _t[n-1];
	/* day of the month */
	var j	= this.getDate();
	var d	= j.chunk(2,"",0);
	var S	= j.ordinalSuffix();
	/* day of the week */
	var w	= this.getDay();
	var D	= Date.days[w];
	var l	= Date.Days[w];
	var N	= (w == 0) ? 7 : w;
	/* hours */
	var G	= this.getHours();
	var H	= G.chunk(2,"",0);
	var g	= (G > 12) ? G-12 : ((G == 0)? 12 : G);
	var h	= g.chunk(2,"",0);
	/* minutes */
	var i	= this.getMinutes().chunk(2,"",0);
	var min	= this.getMinutes();
	/* seconds */
	var s	= this.getSeconds().chunk(2,"",0);
	var sec	= this.getSeconds();
	/* milliseconds */
	var u	= this.getMilliseconds().chunk(3,"",0);
	/* am/pm */
	var a	= (G < 12) ? "am" : "pm";
	var A	= a.toUpperCase();
	/*  timezone */
	var to	= this.getTimezoneOffset();
	var Z	= -to*60;
	var abto= Math.abs(to);
	var GMT_= (to > 0) ? "-" : "+";
	var GMTh= Math.floor(abto/60).chunk(2,"",0);
	var GMTm= (abto%60).chunk(2,"",0);
	var O	= GMT_+GMTh+GMTm;
	var P	= GMT_+GMTh+":"+GMTm;
	return {Y:Y,y:y,L:L,n:n,m:m,F:F,M:M,t:t,j:j,d:d,S:S,w:w,D:D,l:l,N:N,G:G,
		H:H,g:g,h:h,i:i,s:s,u:u,a:a,A:A,O:O,P:P,Z:Z,min:min,sec:sec};
};

/** Format the date like the php date() function (does not ignore escaped
 *	characters)
 *  @param f	= the format string
 */
Date.prototype.format = function(f)
{
	var h = this.toHash(); var s = "";
	for (var i = 0; i < f.length; i++) s += _.V(h,f.charAt(i),f.charAt(i));
	return s;
};

/*----------------------------------------------------------------------------*\
|* COOKIES                                                                    *|
\*----------------------------------------------------------------------------*/
Cookie = {
	/* GET/SET                                                            */
	get : function(k)
	{
		if (k == undefined) return document.cookie.toString();
		return (Cookie.toObject())[k];
	},
	set : function(k,v,expiration)
	{
		var ex = (expiration != undefined)
			? "; expires="+((expiration.toUTCString)
				? expiration.toUTCString()
				: expiration)
			: "";
		document.cookie = escape(k)+"="+escape(v)+ex;
	},
	/* DELETE/CLEAR                                                       */
	del : function(k)
	{
		if (k != undefined) Cookie.set(k,"",Date.now().add(-1));
	},
	clear : function()
	{
		var o = Cookie.toObject();
		for (k in o) Cookie.del(k);
	},
	/* ENCODE/DECODE                                                      */
	encode : function(o,expiration)
	{
		var vars = [], i = 0;
		for (k in o) vars[i++] = escape(k)+"="+escape(o[k]);
		return vars.join("; ");
	},
	decode : function(c)
	{
		var kv = [],o = {};
		if (c == undefined) c = document.cookie.toString();
		var vars = c.split(";");
		for (i in vars) {
			kv = String(vars[i]).trim().split("=");
			if (kv.length == 2) o[unescape(kv[0])] = unescape(kv[1]);
		}
		return o;
	},
	/* TO/FROM OBJECT                                                     */
	toObject : function()
	{
		return (document.cookie)
			? Cookie.decode(document.cookie.toString())
			: {};
	},
	fromObject : function(o,expiration)
	{
		for (k in o) Cookie.set(k,o[k],expiration);
	}
}

/*----------------------------------------------------------------------------*\
|* SERIALIZATION/JSON/TO SOURCE                                               *|
\*----------------------------------------------------------------------------*/
json = {
encode : function(o,encodeFunctions)
{
	var a = [], tmp = "", kstr = "";
	if (encodeFunctions === undefined) encodeFunctions = false;
	if ((o === undefined)||(o === null)) return "null";
	switch(type(o)) {
		case "string":	return '"'+o.addslashes()+'"';
		case "number":	return isFinite(o) ? String(o) : "null";
		case "boolean":	return String(o);
		case "function":return encodeFunctions
					? String(o).addslashes()
					: "";
		case "array":	for (var i in o) if (a[i] == undefined) {
					tmp = json.encode(o[i],encodeFunctions);
					if (tmp) a[i] = tmp;
				}
				return "["+a.join(",")+"]";
		case "object":	var i = 0, _o = {};
				for (var k in o) if (_o[k] == undefined) {
					tmp = json.encode(o[k],encodeFunctions);
					kstr = '"'+String(k).addslashes()+'":';
					if (tmp) a[i++] = kstr+tmp;
				}
				return "{"+a.join(",")+"}";
		default:	return "null";
	}
},
decode : function(s)
{
	//will implement an intelligent decoding function later, eval for now
	var o;
	try { eval("o = "+s); } catch(e) { }
	return o;
}
}
