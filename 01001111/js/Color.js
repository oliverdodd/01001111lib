/*	Color.js - Javascript object for for creating and converting colors.
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
Color = function(r,g,b,h,s,v,c,m,y,k,dec,hex) {
	/*--------------------------------------------------------------------*\
	|* VARIABLES                                                          *|
	\*--------------------------------------------------------------------*/
	//rgb
	this.r		= r;	//0-255
	this.g		= g;	//0-255
	this.b		= b;	//0-255
	//hsv
	this.h		= h;	//0-360
	this.s		= s;	//0-100
	this.v		= v;	//0-100
	//cmyk
	this.c		= c;	//0-100
	this.m		= m;	//0-100
	this.y		= y;	//0-100
	this.k		= k;	//0-100
	//dec/hex
	this.dec	= dec;
	this.hex	= hex;
};
/*----------------------------------------------------------------------------*\
|* CONSTANTS                                                                  *|
\*----------------------------------------------------------------------------*/
Color.dWHITE	= 16777215;
Color.dBLACK	= 0;

/*----------------------------------------------------------------------------*\
|* CREATION                                                                   *|
\*----------------------------------------------------------------------------*/
/** RGB() - create a color from decimal rgb values
 *  @param r	= red	(0 - 255)
 *  @param g	= green	(0 - 255)
 *  @param b	= blue	(0 - 255)
 */
Color.RGB = function(r,g,b)
{
	//ensure numbers within bounds
	r = Math.bound(r,0,255);
	g = Math.bound(g,0,255);
	b = Math.bound(b,0,255);
	//use _RGB to get the rest 
	var _c = Color._RGB(r,g,b);
	return new Color(r,g,b,_c.h,_c.s,_c.v,_c.c,_c.m,_c.y,_c.k,_c.dec,_c.hex);
};
Color._RGB = function(r,g,b)
{
	//convert to hsv
	var hsv = Color.rgb2hsv(r,g,b);
	//convert to cmyk
	var cmyk = Color.rgb2cmyk(r,g,b);
	//convert to decimal
	var dec = Color.rgb2dec(r,g,b);
	//convert to hex
	var hex = Color.rgb2hex(r,g,b);
	return {r:r,g:g,b:b,h:hsv.h,s:hsv.s,v:hsv.v,
		c:cmyk.c,m:cmyk.m,y:cmyk.y,k:cmyk.k,dec:dec,hex:hex};
};
/** HSV() - create a color from decimal hsv values
 *  @param h	= hue			(0 - 360)
 *  @param s	= saturation		(0 - 100)
 *  @param v	= value/brightness	(0 - 100)
 */
Color.HSV = function(h,s,v)
{
	//ensure numbers within bounds
	h = Math.bound(h,0,360);
	s = Math.bound(s,0,100);
	v = Math.bound(v,0,100);
	//convert to rgb
	var rgb = Color.hsv2rgb(h,s,v);
	//use _RGB to get the rest 
	var _c = Color._RGB(rgb.r,rgb.g,rgb.b);
	return new Color(_c.r,_c.g,_c.b,h,s,v,_c.c,_c.m,_c.y,_c.k,_c.dec,_c.hex);
};

/** CMYK() - create a color from decimal cmyk values
 *  @param c	= cyan		(0 - 100)
 *  @param m	= magenta	(0 - 100)
 *  @param y	= yellow	(0 - 100)
 *  @param k	= black		(0 - 100)
 */
Color.CMYK = function(c,m,y,k)
{
	//ensure numbers within bounds
	c = Math.bound(c,0,100);
	m = Math.bound(m,0,100);
	y = Math.bound(y,0,100);
	k = Math.bound(k,0,100);
	//convert to rgb
	var rgb = Color.cmyk2rgb(c,m,y,k);
	//use _RGB to get the rest 
	var _c = Color._RGB(rgb.r,rgb.g,rgb.b);
	return new Color(_c.r,_c.g,_c.b,_c.h,_c.s,_c.v,c,m,y,k,_c.dec,_c.hex);
};

/** DEC/HEX() - create a color from decimal/hex rgb values
 *  @param dec	= decimal number	(0 - 16777215)
 *  @param hex	= hexadecimal number	(000000 - FFFFFF)
 */
Color.DEC = function(dec)
{
	//ensure number within bounds
	dec = Math.bound(dec,0,Color.dWHITE);
	//convert to rgb
	var rgb = Color.dec2rgb(dec);
	//use _RGB to get the rest 
	var _c = Color._RGB(rgb.r,rgb.g,rgb.b);
	return new Color(_c.r,_c.g,_c.b,_c.h,_c.s,_c.v,_c.c,_c.m,_c.y,_c.k,dec,_c.hex);
};
Color.HEX = function(hex)
{
	//convert to rgb
	var rgb = Color.hex2rgb(hex);
	//use _RGB to get the rest 
	var _c = Color._RGB(rgb.r,rgb.g,rgb.b);
	return new Color(_c.r,_c.g,_c.b,_c.h,_c.s,_c.v,_c.c,_c.m,_c.y,_c.k,_c.dec,hex);
};

/*----------------------------------------------------------------------------*\
|* STATIC FUNCTIONS                                                           *|
\*----------------------------------------------------------------------------*/
/** rgb2hsv() - convert from rgb to hsv
 *  @param r	= red	(0 - 255)
 *  @param g	= green	(0 - 255)
 *  @param b	= blue	(0 - 255)
 */
Color.rgb2hsv = function(r,g,b)
{
	//ensure numbers within bounds
	r = Math.bound(r,0,255);
	g = Math.bound(g,0,255);
	b = Math.bound(b,0,255);
	//convert
	var min = Math.min(r,g,b);
	var max = Math.max(r,g,b);
	var delta = max - min;
	
	var h = 0; var s = 0; var v = max/255;
	if((max == 0)||(delta == 0)) { s = 0; h = 0; }
	else {	s = delta/max;
		if(r == max)		h = (g - b)/delta;
		else if(g == max )	h = 2 + (b - r)/delta;
		else			h = 4 + (r - g)/delta;
		h *= 60;
		if(h < 0)		h += 360; }
	return { h:Math.round(h),s:Math.round(s*100),v:Math.round(v*100) };
};

/** hsv2rgb() - convert from hsv to rgb
 *  @param h	= hue			(0 - 360)
 *  @param s	= saturation		(0 - 100)
 *  @param v	= value/brightness	(0 - 100)
 */
Color.hsv2rgb = function(h,s,v)
{
	//ensure numbers within bounds
	h = Math.bound(h,0,360);
	s = Math.bound(s,0,100)/100;
	v = Math.bound(v,0,100)/100;
	//convert
	var r = 0; var g = 0; var b = 0;
	if (s == 0) { r = g = b = v * 255; }
	else {	h /= 60;
		var i = Math.floor(h);
		var f = h - i;
		var p = v*(1 - s);
		var q = v*(1 - s*f);
		var t = v*(1 - s*(1 - f));
		
		if	(i==0)	{ r = v; g = t; b = p; }
		else if	(i==1)	{ r = q; g = v; b = p; }
		else if	(i==2)	{ r = p; g = v; b = t; }
		else if	(i==3)	{ r = p; g = q; b = v; }
		else if	(i==4)	{ r = t; g = p; b = v; }
		else		{ r = v; g = p; b = q; }
		r *= 255;	g *= 255;	b *= 255; }
	return { r:Math.round(r),g:Math.round(g),b:Math.round(b) };
};

/** rgb2cmyk() - convert from rgb to cmyk
 *  @param r	= red	(0 - 255)
 *  @param g	= green	(0 - 255)
 *  @param b	= blue	(0 - 255)
 */
Color.rgb2cmyk = function(r,g,b)
{
	//ensure numbers within bounds
	r = Math.bound(r,0,255);
	g = Math.bound(g,0,255);
	b = Math.bound(b,0,255);
	//convert
	r /= 255;
	g /= 255;
	b /= 255;
	var c = 1 - r;
	var m = 1 - g;
	var y = 1 - b;
	var k = 0;
	if (Math.min(c,m,y) == 1) { c = 0; m = 0; y = 0; k = 1; }
	else {	k = Math.min(c,m,y);
		c = (c - k)/(1 - k);
		m = (m - k)/(1 - k);
		y = (y - k)/(1 - k); }
	return { c:Math.round(c*100),m:Math.round(m*100),
		 y:Math.round(y*100),k:Math.round(k*100) };
};

/** cmyk2rgb() - convert from cmyk to rgb
 *  @param c	= cyan		(0 - 100)
 *  @param m	= magenta	(0 - 100)
 *  @param y	= yellow	(0 - 100)
 *  @param k	= black		(0 - 100)
 */
Color.cmyk2rgb = function(c,m,y,k)
{
	//ensure numbers within bounds
	c = Math.bound(c,0,100);
	m = Math.bound(m,0,100);
	y = Math.bound(y,0,100);
	k = Math.bound(k,0,100);
	//convert
	c /= 100;
	m /= 100;
	y /= 100;
	k /= 100;
	var r = 1 - (c * (1 - k)) - k;
	var g = 1 - (m * (1 - k)) - k;
	var b = 1 - (y * (1 - k)) - k;
	return { r:Math.round(r*255),g:Math.round(g*255),b:Math.round(b*255) };
};

/** rgb2dec/hex() - convert from rgb to dec/hex
 *  @param r	= red	(0 - 255)
 *  @param g	= green	(0 - 255)
 *  @param b	= blue	(0 - 255)
 */
Color.rgb2dec = function(r,g,b)
{
	//ensure numbers within bounds
	r = Math.bound(r,0,255);
	g = Math.bound(g,0,255);
	b = Math.bound(b,0,255);
	return (r<<16)+(g<<8)+b;
};
Color.rgb2hex = function(r,g,b)
{
	var hex = (Color.rgb2dec(r,g,b)).toBase(16);
	while(hex.length < 6) hex = "0"+hex;
	return hex;
};

/** dec/hex2rgb() - convert from dec/hex to rgb
 *  @param dec	= decimal number	(0 - 16777215)
 *  @param hex	= hexadecimal number 	(000000 - FFFFFF)
 */
Color.dec2rgb = function(dec)
{
	dec = Math.bound(dec,0,Color.dWHITE);
	var r = (dec&0xFF0000)>>16;
	var g = (dec&0xFF00)>>8;
	var b = dec&0xFF;
	return {r:r,g:g,b:b};
};
Color.hex2rgb = function(hex)
{
	return Color.dec2rgb(hex.toString().toDec(16));
};

//
// COMBINATIONS
//
Color.hsv2cmyk = function(h,s,v)
{
	var rgb = Color.hsv2rgb(h,s,v);
	return Color.rgb2cmyk(rgb.r,rgb.g,rgb.b);
};
Color.cmyk2hsv = function(c,m,y,k)
{
	var rgb = Color.cmyk2rgb(c,m,y,k);
	return Color.rgb2hsv(rgb.r,rgb.g,rgb.b);
};

/*----------------------------------------------------------------------------*\
|* MANIPULATE                                                                 *|
\*----------------------------------------------------------------------------*/
/** invert() - invert the color */
Color.prototype.invert = function() { return Color.DEC(this.dWHITE-this.dec); };

/** desaturate() - desaturate the color */
Color.prototype.desaturate = function()
{
	return Color.HSV(this.h,0,this.v);
};

/** shifthue() - shift the hue by a number of degrees
 *  @param d	= the number of degrees (0 - 360) 
 */
Color.prototype.shifthue = function(d)
{
	if (isUndefined(d)) d = 0;
	return Color.HSV(Math.rollover(this.h+d,360),this.s,this.v);
};

/** complement() - complementary color */
Color.prototype.complement = function() { return this.shifthue(180); };

/** triad()
 *  @param n	= the triad number (0,1,2)
 */
Color.prototype.triad = function(n)
{
	if (isUndefined(n)) n = 1;
	return this.shifthue(n*120);
};

/** square()
 *  @param n	= the square number (0,1,2)
 */
Color.prototype.square = function(n)
{
	if (isUndefined(n)) n = 1;
	return this.shifthue(n*90);
};
