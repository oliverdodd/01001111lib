<?php
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
class Tweets
{
	/*--------------------------------------------------------------------*\
	|* CONSTANTS                                                          *|
	\*--------------------------------------------------------------------*/
	const REST_URL		= "http://twitter.com/statuses/";
	const SEARCH_URL	= "http://search.twitter.com/search";
	
	const PUBLIC_TIMELINE	= "public_timeline";
	const USER_TIMELINE	= "user_timeline";
	
	const ATOM		= 'atom';
	const JSON		= 'json';
	const RSS		= 'rss';
	const XML		= 'xml';
	
	/*--------------------------------------------------------------------*\
	|* URLS                                                               *|
	\*--------------------------------------------------------------------*/
	/** userURL() - url for requesting tweets from a particular user
	 *  @param $user	- the user
	 *  @param $format	- the request format
	 *  @param $params	- other parameters
	 */
	public static function userURL($user,$format=self::JSON,$params=array())
	{
		return self::REST_URL.self::USER_TIMELINE.
			"/$user.$format".
			($params ? "?".self::queryString($params) : "");
	}
	
	/** publicURL() - url for requesting tweets from the public timeline
	 *  @param $format	- the request format
	 *  @param $params	- other parameters
	 */
	public static function publicURL($format=self::JSON,$params=array())
	{
		return self::REST_URL.self::PUBLIC_TIMELINE.".$format".
			($params ? "?".self::queryString($params) : "");
	}
	
	public static function queryString($args=array())
	{
		return is_array($args) ? http_build_query($args) : $args;
	}
	
	/*--------------------------------------------------------------------*\
	|* GET DATA                                                           *|
	\*--------------------------------------------------------------------*/
	public static function get($url,$format=null)
	{
		$data = @file_get_contents($url);
		switch ($format) {
			case self::JSON:	return json_decode($data);
			default:		return $data;
		}
	}
	
	/*--------------------------------------------------------------------*\
	|* USER SPECIFIC                                                      *|
	\*--------------------------------------------------------------------*/
	/** latestNFor() - get the latest N tweets for a user
	 *  @param $user		- the user
	 *  @param $n			- the count
	 *  @param $excludeReplies	- exclude the @user replies?
	 */
	public static function latestNFor($user,$n=1,$excludeReplies=true)
	{
		$tweets = array();
		$remaining = $n;
		$page = 1;
		while ($remaining) {
			$url = self::userURL($user,self::JSON,
				array('page' => $page));
			$entries = self::get($url,self::JSON);
			if (!is_array($entries)||!count($entries)) break;
			foreach ($entries as $e) {
				if ($excludeReplies && _::O($e,'in_reply_to_user_id'))
					continue;
				$tweets[] = $e;
				$remaining--;
				if (!$remaining) break;
			}
			$page++;
		}
		return $tweets;
	}
	
	/*--------------------------------------------------------------------*\
	|* PUBLIC TIMELINE                                                    *|
	\*--------------------------------------------------------------------*/
	/** buzzWords() - hot words
	 */
	public static function buzzWords()
	{
		$url = self::publicURL(self::JSON);
		$tweets = self::get($url,self::JSON);
		return self::wordCounts($tweets);
	}
	public static function wordCounts($tweets,$ignoreUsers=true,$ignoreUrls=true)
	{
		$text = "";
		foreach ($tweets as $tweet)
			$text .= _::O($tweet,'text')." ";
		
		$text = strtolower($text);
		
		if ($ignoreUrls) {
			$urls = self::getURLs($text);
			$text = str_replace($urls," ",$text);
		}
		if ($ignoreUsers) {
			$users = self::getAtUsers($text);
			$text = str_replace($users," ",$text);
		}
		$words = self::match('|[\w-\']+|',$text);
		
		return array_count_values($words);
	}
	
	/*--------------------------------------------------------------------*\
	|* TEXT UTILITIES                                                     *|
	\*--------------------------------------------------------------------*/
	/** match() - match certain patterns in a string
	 */
	public static function match($regEx,$s="")
	{
		$matches = array();
		preg_match_all($regEx,$s,$matches);
		return isset($matches[0]) ? $matches[0] : array();
	}
	
	/** getURLs() - get urls from a string
	 */
	public static function getURLs($s)
	{
		return self::match('|http:\/\/[\S]+|i',$s); 
	}
	
	/** getAtUsers() - get @user replies
	 */
	public static function getAtUsers($s)
	{
		return self::match('|@[\S]+|',$s); 
	}
}
?>
