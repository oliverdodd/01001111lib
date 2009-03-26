/*	Flipbook+.js - Add a "flipbook" to a web page.
 *		Requires +.js, dom+.js and prototype.js.
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
|* FLIPBOOK                                                                   *|
\*----------------------------------------------------------------------------*/
Flipbook = function(divid,srcs,period)
{
	if (divid !== undefined) this.divid = divid;
	this.add(srcs);
	this.period(period);
};
Flipbook.prototype.divid = "flipbook";
Flipbook.prototype.images = null;
Flipbook.prototype.nImages = 0;
Flipbook.prototype.t = 1;
Flipbook.prototype.interval = 0;
Flipbook.prototype.direction = 1;
Flipbook.prototype.add = function(srcs)
{
	if (srcs == undefined) return;
	if (isArray(srcs)) srcs = Array.toObject(srcs);
	else if (!isObject(srcs)) srcs = {0:images};
	if (this.images == null) this.images = {};
	var n = this.nImages;
	var images = this.createImages(srcs,n);
	for (var i in images) this.images[n++] = images[i];
	this.nImages = Object.count(this.images);
};
Flipbook.prototype.createImages = function(srcs,offset)
{
	if (offset === undefined) offset = 0;
	var images = {};
	var n = Object.count(srcs) + offset;
	for (i in srcs) {
		var src = srcs[i];
		images[i] = document.createElement("img");
		images[i].src = src;
		images[i].flips = 0;
		//images[i].setStyle({
		Element.setStyle(images[i],{
			position:"absolute",
			left:"0",
			top:"0",
			zIndex:n--,
			display:"none"
		});
		$(this.divid).appendChild(images[i]);
	}
	return images;
};
Flipbook.prototype.period = function(period)
{
	if (isNaN(period)) period = 1;
	this.t = period;
};
Flipbook.prototype.play = function()
{
	this.interval = setInterval(this.flip.bind(this),this.t*1000);
};
Flipbook.prototype.flip = function()
{
	var tmp;
	for (i in this.images) {
		if (this.images[i].complete)
			Element.show(this.images[i]);//this.images[i].show();
		else if (this.images[i].flips % 4 == 3) {
			tmp = this.images[i].src;
			this.images[i].src = 'about:blank';
			this.images[i].src = tmp;
		}
		var z = parseInt(this.images[i].style.zIndex) + this.direction;
		if (z > this.nImages) z = 1;
		else if (z <= 0) z = this.nImages;
		this.images[i].style.zIndex = z;
		this.images[i].flips++;
	}
};
Flipbook.prototype.stop = function()
{
	clearInterval(this.interval);
	this.interval = 0;
};
Flipbook.prototype.reverse = function(i)
{
	this.direction = -this.direction;
};
