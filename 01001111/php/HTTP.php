<?php
/* 	HTTP Class - A simple interface for performing HTTP queries.
 *	Copyright (c) 2006 Oliver C Dodd
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
class HTTP
{
	/*--------------------------------------------------------------------*\
	|* HTTP STATUS CODES                                                  *|
	\*--------------------------------------------------------------------*/
	public static $codes = array(
		100 => "HTTP/1.1 100 Continue",
		101 => "HTTP/1.1 101 Switching Protocols",
		200 => "HTTP/1.1 200 OK",
		201 => "HTTP/1.1 201 Created",
		202 => "HTTP/1.1 202 Accepted",
		203 => "HTTP/1.1 203 Non-Authoritative Information",
		204 => "HTTP/1.1 204 No Content",
		205 => "HTTP/1.1 205 Reset Content",
		206 => "HTTP/1.1 206 Partial Content",
		207 => "HTTP/1.1 207 Multi-Status",
		300 => "HTTP/1.1 300 Multiple Choices",
		301 => "HTTP/1.1 301 Moved Permanently",
		302 => "HTTP/1.1 302 Found",
		303 => "HTTP/1.1 303 See Other",
		304 => "HTTP/1.1 304 Not Modified",
		305 => "HTTP/1.1 305 Use Proxy",
		307 => "HTTP/1.1 307 Temporary Redirect",
		400 => "HTTP/1.1 400 Bad Request",
		401 => "HTTP/1.1 401 Unauthorized",
		402 => "HTTP/1.1 402 Payment Required",
		403 => "HTTP/1.1 403 Forbidden",
		404 => "HTTP/1.1 404 Not Found",
		405 => "HTTP/1.1 405 Method Not Allowed",
		406 => "HTTP/1.1 406 Not Acceptable",
		407 => "HTTP/1.1 407 Proxy Authentication Required",
		408 => "HTTP/1.1 408 Request Time-out",
		409 => "HTTP/1.1 409 Conflict",
		410 => "HTTP/1.1 410 Gone",
		411 => "HTTP/1.1 411 Length Required",
		412 => "HTTP/1.1 412 Precondition Failed",
		413 => "HTTP/1.1 413 Request Entity Too Large",
		414 => "HTTP/1.1 414 Request-URI Too Large",
		415 => "HTTP/1.1 415 Unsupported Media Type",
		416 => "HTTP/1.1 416 Requested range not satisfiable",
		417 => "HTTP/1.1 417 Expectation Failed",
		500 => "HTTP/1.1 500 Internal Server Error",
		501 => "HTTP/1.1 501 Not Implemented",
		502 => "HTTP/1.1 502 Bad Gateway",
		503 => "HTTP/1.1 503 Service Unavailable",
		504 => "HTTP/1.1 504 Gateway Time-out",
		505 => "HTTP/1.1 505 HTTP Version Not Supported",
		509 => "HTTP/1.1 509 Bandwidth Limit Exceeded",
	);
	
	/*--------------------------------------------------------------------*\
	|* MIME TYPES                                                         *|
	\*--------------------------------------------------------------------*/
	public static $mimetypes = array(
		"aif"		=> "audio/x-aiff",
		"aiff"		=> "audio/x-aiff",
		"aifc"		=> "audio/x-aiff",
		"au"		=> "audio/basic",
		"snd"		=> "audio/basic",
		"avi"		=> "video/x-msvideo",
		"bas"		=> "text/plain",
		"bat"		=> "text/plain",
		"bin"		=> "application/octet-stream",
		"bmp"		=> "image/bmp",
		"cer"		=> "application/x-x509-ca-cert",
		"class"		=> "application/java-class",
		"cmd"		=> "text/plain",
		"com"		=> "application/octet-stream",
		"css"		=> "text/css",
		"doc"		=> "application/msword",
		"eml"		=> "message/rfc822",
		"etx"		=> "text/x-setext",
		"evy"		=> "application/envoy",
		"exe"		=> "application/x-msdownload",
		"gif"		=> "image/gif",
		"gz"		=> "application/x-gzip",
		"htm"		=> "text/html",
		"html"		=> "text/html",
		"ief"		=> "image/ief",
		"jar"		=> "application/java-archive",
		"jardiff"	=> "application/x-java-archive-diff",
		"java"		=> "text/x-java-source",
		"jpg"		=> "image/jpeg",
		"jpeg"		=> "image/jpeg",
		"jpe"		=> "image/jpeg",
		"jnlp"		=> "application/x-java-jnlp-file",
		"js"		=> "application/x-javascript",
		"mid"		=> "audio/midi",
		"midi"		=> "audio/midi",
		"mov"		=> "video/quicktime",
		"qt"		=> "video/quicktime",
		"movie"		=> "video/x-sgi-movie",
		"mp3"		=> "audio/mpeg",
		"mpg"		=> "video/mpeg",
		"mpeg"		=> "video/mpeg",
		"mpe"		=> "video/mpeg",
		"oda"		=> "application/oda",
		"ogg"		=> "application/x-ogg",
		"pdm"		=> "image/x-portable-bitmap",
		"pdf"		=> "application/pdf",
		"pgm"		=> "image/x-portable-graymap",
		"png"		=> "image/png",
		"ppm"		=> "image/x-portable-pixmap",
		"ps"		=> "application/postscript",
		"eps"		=> "application/postscript",
		"ai"		=> "application/postscript",
		"ra"		=> "audio/x-pn-realaudio",
		"rm"		=> "audio/x-pn-realaudio",
		"ram"		=> "audio/x-pn-realaudio",
		"rgb"		=> "image/x-rgb",
		"rtf"		=> "application/rtf",
		"rtx"		=> "text/richtext",
		"ser"		=> "application/x-java-serialized-object",
		"ssi"		=> "text/x-server-parsed-html",
		"shtml"		=> "text/x-server-parsed-html",
		"swf"		=> "application/x-shockwave-flash",
		"tar"		=> "application/x-tar",
		"tif"		=> "image/tiff",
		"tiff"		=> "image/tiff",
		"tsv"		=> "text/tab-separated-values",
		"txt"		=> "text/plain",
		"text"		=> "text/plain",
		"asc"		=> "text/plain",
		"usr"		=> "application/x-x509-user-cert",
		"vcf"		=> "text/x-vcard",
		"vew"		=> "application/groupwise",
		"wav"		=> "audio/wav",
		"w61"		=> "application/wordperfect6.1",
		"wml"		=> "x-world/x-vrml",
		"wp"		=> "application/wordperfect",
		"wpd"		=> "application/wordperfect",
		"wp5"		=> "application/wordperfect",
		"w60"		=> "application/wordperfect6.0",
		"xml"		=> "application/xml",
		"zip"		=> "application/zip",
	);
	
	/*--------------------------------------------------------------------*\
	|* GET/POST                                                           *|
	\*--------------------------------------------------------------------*/
	const GET	= "GET";
	const POST	= "POST";
	
	public static function query($uri,$args='',$method=HTTP::POST)
	{
		return (strtoupper($method) === HTTP::POST)
			? HTTP::post($uri,$args)
			: HTTP::get($uri,$args);
	}
	public static function get($uri,$args='')
	{
		if (strpos($uri,'?') === false) $uri .= "?";
		return @file_get_contents($uri.HTTP::queryString($args));
	}
	//post requires the inclusion of the curl extension
	public static function post($uri,$args='')
	{
		if (function_exists('curl_init')) {
			$ch = curl_init();
			if (!$ch) return;
			curl_setopt($ch,CURLOPT_URL,$uri);
			curl_setopt($ch,CURLOPT_HEADER,false);
			curl_setopt ($ch,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$args);
			$r = curl_exec($ch);
			curl_close($ch);
			return $r;
		}
		else {	$p = array('http' => array(
					'method' => 'POST',
					'content' => $args ) );
			$s = stream_context_create($p);
			$f = @fopen($uri,'rb',false,$s);
			if (!$f) return "";
			return @stream_get_contents($f);
		}
	}
	
	public static function queryString($args=array())
	{
		if (!is_array($args)) return $args;
		$s = '';
		foreach($args as $k => $v) $s .= "&$k=".rawurlencode($v);
		return $s;
	}
	
	/*--------------------------------------------------------------------*\
	|* OUPUT HEADERS (REDIRECTS/ERRORS/STATUS)                            *|
	\*--------------------------------------------------------------------*/
	public static function redirect($url='',$msg='',$delay=0)
	{
		if (!$delay)	header("Location: $url");
		else		header("Refresh: $delay; URL=$url");
		//echo $msg;
		die($msg);
	}
	public static function boot($url='')
	{
		HTTP::redirect($url,
			"<script type='text/javascript'>".
				"window.location.href = '$url'".
			"</script>",1);
	}
	
	public static function status($code)
	{
		if (isset(self::$codes[$code])) header(self::$codes[$code]);
	}
	public static function deny() { die(self::status(401)); }
	public static function deceive() { die(self::status(404)); }
	public static function forbid() { die(self::status(403)); }
	
	public static function forceDownload($file,$name=null,$mimetype=null)
	{
		if ($name === null) $name = basename($file);
		//if no mimetype,try for the extension
		if ($mimetype === null) $mimetype = self::mimetype($file);
		if ($mimetype) header('Content-type: '.$mimetype);
		header('Content-Disposition: attachment; filename="'.$name.'"');
		readfile($file);
	}
	
	/*--------------------------------------------------------------------*\
	|* SET HEADERS                                                        *|
	\*--------------------------------------------------------------------*/
	public static function type($mimetype) { header("Content-type: $mimetype"); }
	public static function javascript() { self::type(self::mimetype("js")); }
	public static function css() { self::type(self::mimetype("css")); }
	
	/*--------------------------------------------------------------------*\
	|* GET HEADERS                                                        *|
	\*--------------------------------------------------------------------*/
	public static function headers($url)
	{
		$headers = array();
		$raw_headers = @get_headers($url);
		if (!$raw_headers) return $headers;
		foreach ($raw_headers as $h) {
			@list($k,$v) = explode(':',$h,2);
			//status has no key
			if (!$v)	$headers[0] = $k;
			else		$headers[$k] = $v;
		}
		return $headers;
	}
	public static function modified($url)
	{
		$headers = self::headers($url);
		return isset($headers['Last-Modified'])
			? $headers['Last-Modified']
			: 0;
	}
	public static function requestHeaders()
	{
		$headers = array();
		if (!isset($_SERVER)) return $headers;
		foreach($_SERVER as $k=>$v)
			if (strpos($k,'HTTP_') === 0) {
				$n = str_replace(array('HTTP_','_'),
						 array('','-'),$k);
				$headers[$n] = $v;
			}
		return $headers;
	}
	
	/*--------------------------------------------------------------------*\
	|* META                                                               *|
	\*--------------------------------------------------------------------*/
	public static function meta($url)
	{
		$metaTags = @get_meta_tags($url);
		return ($metaTags) ? $metaTags : array();
	}
	
	/*--------------------------------------------------------------------*\
	|* MIMETYPE                                                           *|
	\*--------------------------------------------------------------------*/
	function mimetype($file)
	{
		$ext = strtolower(ltrim(strrchr($file,'.'),'.'));
		return (isset(self::$mimetypes[$ext]))
			? self::$mimetypes[$ext]
			: $ext;
	}
}
?>