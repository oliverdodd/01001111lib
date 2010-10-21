/* 	Tweets - some Twitter API tools
 *	Copyright (c) 2009 Oliver C Dodd
 *
 *  Permission is hereby granted,free of charge,to any person obtaining a 
 *  copy of this software and associated documentation files (the "Software"),
 *  to deal in the Software without restriction,including without limitation
 *  the rights to use,copy,modify,merge,publish,distribute,sublicense,
 *  and/or sell copies of the Software,and to permit persons to whom the 
 *  Software is furnished to do so,subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in
 *  all copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS",WITHOUT WARRANTY OF ANY KIND,EXPRESS OR
 *  IMPLIED,INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL 
 *  THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,DAMAGES OR OTHER
 *  LIABILITY,WHETHER IN AN ACTION OF CONTRACT,TORT OR OTHERWISE,ARISING
 *  FROM,OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 *  DEALINGS IN THE SOFTWARE.
 */

/*-CONSTRUCTOR----------------------------------------------------------------*/
//not currently supporting authentication for security reasons
Tweets = function(user)
{
	this.user = user;
	this.tweets = [];
	this.words = [];
	this.wordsCounted = {};
	this.callback = function() {};
};
Tweets.prototype = {
/*-CONSTANTS------------------------------------------------------------------*/
REST_URL	: "http://twitter.com/statuses/",
SEARCH_URL	: "http://search.twitter.com/search",

PUBLIC_TIMELINE	: "public_timeline",
USER_TIMELINE	: "user_timeline",

ATOM		: 'atom',
JSON		: 'json',
RSS		: 'rss',
XML		: 'xml',

urlRegEx	: /http:\/\/[\S]+/ig,
userReplyRegEx	: /@[\S]+/g,

/*-VARIABLES------------------------------------------------------------------*/
user		: 'twitterapi',
count		: 20,
grabCount	: 20,
grabbed		: 0,
page		: 1,
excludeReplies	: true,
callback	: null,

tweets		: null,
words		: null,
wordsCounted	: null,

/*-URLS-----------------------------------------------------------------------*/
/** userURL() - url for requesting tweets from a particular user
 *  @param user		- the user
 *  @param format	- the request format
 *  @param params	- other parameters
 */
userURL: function(user,format,params)
{
	if (format == undefined) format = this.json;
	if (params == undefined) params = false;
	return this.REST_URL+this.USER_TIMELINE+"/"+
		user+"."+format+(params ? "?"+this.queryString(params) : "");
},

/** publicURL() - url for requesting tweets from the public timeline
 *  @param format	- the request format
 *  @param params	- other parameters
 */
publicURL: function(format,params)
{
	if (format == undefined) format = this.json;
	if (params == undefined) params = false;
	return this.REST_URL+this.PUBLIC_TIMELINE+"."+format+
		(params ? "?"+this.queryString(params) : "");
},

queryString: function(args)
{
	if (!Tweets.isOfType(args,"Object")) return args;
	var q = [];
	for (var k in args)
		q.push( encodeURIComponent(k)+"="+encodeURIComponent(args[k]));
	return q.join('&');
},

/*-REQUEST--------------------------------------------------------------------*/
request: function(url,callback)
{
	var callbackName = "twitterCallback"+Math.floor(1000000000*Math.random());
	window[callbackName] = function(response) { callback(response); };
	url += ((url.indexOf('?') == -1) ? "?" : "&")+
		"callback="+callbackName;
	var scr = document.createElement("script");
	scr.setAttribute("type","text/javascript",0);
	scr.setAttribute("src",url,0);
	document.body.appendChild(scr);
},
processParams: function(params)
{
	for (var k in params)
		this[k] = params[k];
},

/*-USER SPECIFIC--------------------------------------------------------------*/
/** latestNFor() - get the latest N tweets for a user
 *  @param callback	- the callback function
 *  @param n		- the count
 *  @param params	- any parameters
 */
latestNFor: function(callback,n,params)
{
	this.callback = callback;
	this.count = (n != undefined) ? n : 1;
	if (params != undefined)
		this.processParams(params);
	var url = this.userURL(this.user,this.JSON,{page:this.page});
	this.request(url,this.bind(this.processLatestNFor));
},
processLatestNFor: function(entries)
{
	if (!Tweets.isOfType(entries,"Array")||!entries.length)
		return this.callback(this.tweets);
	for (var i = 0; i< entries.length; i++) {
		if (this.excludeReplies && entries[i]['in_reply_to_user_id'])
			continue;
		this.tweets[this.tweets.length] = entries[i];
		if (this.tweets.length >= this.count)
			return this.callback(this.tweets);
	}
	this.page++;
	this.latestNFor(this.callback,this.count);
},

/** wordCount() - count the occurence of all words in the user's stream or for
 *	the past n tweets.  (automatically filters out URLs and @user reply taqs).
 *  @param callback		- the callback function
 *  @param n			- the count
 */
wordCount: function(callback,n)
{
	this.callback = callback;
	this.count = n;
	this.grabCount = this.count == undefined ? 200 : this.count;
	var url = this.userURL(this.user,this.JSON,{page:this.page,count:this.grabCount});
	this.request(url,this.bind(this.processWordCount));
},
processWordCount: function(entries)
{
	var tmpWords = [], i = 0, j = 0;
	if (!Tweets.isOfType(entries,"Array")||!entries.length)
		return this.countWords(this.callback);
	for (var i = 0; i < entries.length; i++) {
		tmpWords = this.processWords(entries[i].text);
		if (!tmpWords) return this.countWords(this.callback);
		for (j = 0; j < tmpWords.length; j++)
			this.words.push(tmpWords[j]);
		this.grabbed++;
		if ((this.count != undefined)&&(this.grabbed >= this.count))
			return this.countWords(this.callback);
	}
	if ((this.count == undefined)&&(entries.length < this.grabCount))
		return this.countWords(this.callback);
	this.page++;
	this.wordCount(this.callback,this.count);
},
processWords: function(text)
{
	if (text == undefined) return [];
	text = text.replace(this.urlRegEx,"");
	text = text.replace(this.userReplyRegEx,"");
	return text.toLowerCase().match(/[\w-']+/g);
},
countWords: function(callback)
{
	this.words.sort();
	for (var j = 0; j < this.words.length; j++) {
		if (this.wordsCounted[this.words[j]] != undefined)
			this.wordsCounted[this.words[j]]++;
		else this.wordsCounted[this.words[j]] = 1;
	}
	this.callback(this.wordsCounted);
},

/*-PUBLIC TIMELINE------------------------------------------------------------*/
/** buzzWords() - hot words */
buzzWords: function(callback,searchTerm)//implement search term
{
	this.callback = callback;
	this.count = 20;
	this.grabCount = 20;
	var url = this.publicURL(this.JSON);
	this.request(url,this.bind(this.processWordCount));
},
bind: function(f)
{
	var o = this;
	return function()
	{
		return f.apply(o,arguments);
	};
}
};
/*-MISC-----------------------------------------------------------------------*/
Tweets.isOfType = function(o,t)
{
	return Object.prototype.toString.call(o) == "[object "+t+"]";
};

