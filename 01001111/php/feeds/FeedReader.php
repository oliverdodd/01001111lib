<?php
/* 	FeedReader Class - Read a subset of information from RSS and Atom feeds
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
require_once('FeedEntry.php');
require_once('Feed.php');
require_once('FeedEntryType.php');
require_once('FeedType.php');
class FeedReader
{
	/*--------------------------------------------------------------------*\
	|* FETCH/PARSE/GET FEED                                               *|
	\*--------------------------------------------------------------------*/
	public static function fetch($uri)
	{
		if (function_exists("curl_init") && ($ch = curl_init())) {
			curl_setopt($ch,CURLOPT_URL,$uri);
			curl_setopt($ch,CURLOPT_HEADER,false);
			curl_setopt ($ch,CURLOPT_RETURNTRANSFER,true);
			//set fake user agent in case feedburner is being used
			curl_setopt($ch,CURLOPT_USERAGENT,'FeedBurner/1.0 (http://www.FeedBurner.com)');  
			$xml = curl_exec($ch);
			curl_close($ch);
			return $xml;
		} else {
			return file_get_contents($uri);
		}
		
	}
	
	public static function parse($xml,$type=null)
	{
		$doc = new DOMDocument();
		$doc->loadXML($xml);
		$feed = new Feed();
		$entryFeed = new Feed();
		// detect type
		$feedType = FeedType::detect($doc);
		if (!$feedType) return $feed;
		// feed attributes
		foreach ($feed as $k => $v) {
			// ignore entries for now
			if ($k == 'entries') continue;
			$feed->$k = self::tagValue($doc,$feedType->$k);
			$entryFeed->$k = $feed->$k;
		}
		// entries
		$feed->entries = array();
		foreach ($doc->getElementsByTagName($feedType->entryTag) as $node) {
			$entry = new FeedEntry();
			$entry->feed = $entryFeed;
			foreach ($entry as $k => $v) {
				// ignore feed
				if ($k == 'feed') continue;
				$entry->$k = self::tagValue($node,$feedType->entries->$k);
			}
			$feed->entries[] = $entry;
		}
		return $feed;
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
		return $found
			? (isset($tag['get'])
				? $element->getAttribute($tag['get'])
				: $element->nodeValue)
			: null;
	}
	
	/*--------------------------------------------------------------------*\
	|* LATEST                                                             *|
	\*--------------------------------------------------------------------*/
	public static function latestN($urls,$n=5)
	{
		if (!is_array($urls)) $urls = array($urls);
		$latestEach = self::latestNeach($urls,$n);
		$dateOrdered = array();
		foreach ($latestEach as $feed) {
			foreach ($feed->entries as $e) {
				$t = strtotime($e->date).' '.$e->title;
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
			$latest[$url] = self::get($url);
		}
		return $latest;
	}
}
?>
