<?php
/* 	BlogRSS Class - Grab blog entries and pertinent info from RSS feeds
 *	Copyright (c) 2008 Oliver C Dodd
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
class BlogRSS
{
	/*--------------------------------------------------------------------*\
	|* BLOG TYPES (only Blogger and Wordpress currently supported         *|
	\*--------------------------------------------------------------------*/
	const BLOGGER	= 'Blogger';
	const WORDPRESS	= 'wordpress';
	
	/*--------------------------------------------------------------------*\
	|* RSS FEED ATTRIBUTES PER BLOG TYPE                                  *|
	\*--------------------------------------------------------------------*/
	protected static $attributes = array(
		self::BLOGGER	=> array(
			'entry'		=> 'entry',
			'title'		=> 'title',
			'date'		=> 'published',
			'link'		=> array('tag' => 'link',
						 'attributes' => array('rel' => 'alternate'),
						 'get' => 'href'),
			'content'	=> 'content',
		),
		self::WORDPRESS	=> array(
			'entry'		=> 'item',
			'title'		=> 'title',
			'date'		=> 'pubDate',
			'link'		=> 'link',
			'content'	=> 'description',
		)
	);
	
	/*--------------------------------------------------------------------*\
	|* FETCH/PARSE/GET FEED                                               *|
	\*--------------------------------------------------------------------*/
	public static function fetch($uri)
	{
		$ch = curl_init();
		if (!$ch) return "";
		curl_setopt($ch,CURLOPT_URL,$uri);
		curl_setopt($ch,CURLOPT_HEADER,false);
		curl_setopt ($ch,CURLOPT_RETURNTRANSFER,true);
		//set fake user agent in case feedburner is being used
		curl_setopt($ch,CURLOPT_USERAGENT,'FeedBurner/1.0 (http://www.FeedBurner.com)');  
		$xml = curl_exec($ch);
		curl_close($ch);
		return $xml;
	}
	
	public static function parse($xml,$type=null)
	{
		$doc = new DOMDocument();
		$doc->loadXML($xml);
		
		$blog = array();
		$blog['title'] = $doc->getElementsByTagName('title')->item(0)->nodeValue;
		$blog['generator'] = $doc->getElementsByTagName('generator')->item(0)->nodeValue;
		
		if ($type === null)
			$type = self::detectType($blog['generator']);
		if (!$type) return $blog;
		
		$attributes = self::$attributes[$type];
		$entryTag = array_pluck($attributes,'entry');
		$blog['entries'] = array();
		foreach ($doc->getElementsByTagName($entryTag) as $node) {
			$entry = array();
			foreach ($attributes as $k => $v)
				$entry[$k] = self::tagValue($node,$v);
			$entry['blogTitle'] = $blog['title'];
			array_push($blog['entries'],$entry);
		}
		return $blog;
	}
	
	public static function get($uri,$type=null)
	{
		return self::parse(self::fetch($uri),$type);
	}
	
	/*--------------------------------------------------------------------*\
	|* DETERMINE VALUES                                                   *|
	\*--------------------------------------------------------------------*/
	public static function tagValue($node,$tag)
	{
		if (!is_array($tag))
			return @$node->getElementsByTagName($tag)->item(0)->nodeValue;
		//get tag info
		$tagName = $tag['tag'];
		$attributes = isset($tag['attributes']) ? $tag['attributes'] : array();
		//get tags
		$tags = $node->getElementsByTagName($tagName);
		//check attributes
		for ($i = 0; $i < $tags->length; $i++) {
			$found = true;
			foreach ($attributes as $k => $v)
				$found &= strcasecmp($tags->item($i)->getAttribute($k),$v) == 0;
			if ($found) {
				$element = $tags->item($i);
				break;
			}
		}
		return isset($tag['get'])
			? $element->getAttribute($tag['get'])
			: $element->nodeValue;
	}
	
	/*--------------------------------------------------------------------*\
	|* DETECT/BUILD                                                       *|
	\*--------------------------------------------------------------------*/
	public static function detectType($generator)
	{
		if (stripos($generator,self::BLOGGER) !== false)
			return self::BLOGGER;
		if (stripos($generator,self::WORDPRESS) !== false)
			return self::WORDPRESS;
		return "";
	}
	
	/*--------------------------------------------------------------------*\
	|* LATEST                                                             *|
	\*--------------------------------------------------------------------*/
	public static function latestN($urls,$n=5)
	{
		if (!is_array($urls)) $urls = array($urls);
		$latestEach = self::latestNeach($urls,$n);
		$dateOrdered = array();
		foreach ($latestEach as $blog => $entries) {
			foreach ($entries as $e) {
				$t = strtotime($e['date']).' '.$e['title'];
				$dateOrdered[$t] = $e;
			}
		}
		krsort($dateOrdered);
		$latest = array();
		$i = $n;
		while ($i-- && current($dateOrdered)) {
			$latest[] = current($dateOrdered);
			next($dateOrdered);
		}
		return $latest;
	}
	
	public static function latestNeach($urls=array(),$n=1)
	{
		if (!is_array($urls)) $urls = array($urls);
		$latest = array();
		foreach ($urls as $url) {
			$blog = self::get($url);
			$title = $blog['title'];
			$entries = $blog['entries'];
			$latest[$title] = array();
			for ($i = 0; ($i < count($entries))&&($i < $n); $i++)
				$latest[$title][$i] = $entries[$i];
		}
		return $latest;
	}
}
?>
