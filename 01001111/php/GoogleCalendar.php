<?php
/* 	GoogleCalendar Class - Grab event info from public Google calendars
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
class GoogleCalendar
{
	/*--------------------------------------------------------------------*\
	|* CONSTANTS                                                          *|
	\*--------------------------------------------------------------------*/
	const BASE_URL		= 'http://www.google.com/calendar/feeds/';
	const URL_PUBLIC_PATH	= '/public/full';
	
	/*--------------------------------------------------------------------*\
	|* VARIABLES                                                          *|
	\*--------------------------------------------------------------------*/
	public $user	= "default";
	
	/*--------------------------------------------------------------------*\
	|* CONSTRUCT                                                          *|
	\*--------------------------------------------------------------------*/
	public function __construct($user)
	{
		$this->user 	= $user;
	}
	
	/*--------------------------------------------------------------------*\
	|* URL/GET                                                            *|
	\*--------------------------------------------------------------------*/
	public function url($params)
	{
		return self::BASE_URL.$this->user.self::URL_PUBLIC_PATH.
			($params ? "?$params" : "");
	}
	
	public function get($url)
	{
		return @file_get_contents($url);
	}
	
	/*--------------------------------------------------------------------*\
	|* EVENTS                                                             *|
	\*--------------------------------------------------------------------*/
	public function getEvents($futureOnly=true)
	{
		$xml = $this->get($this->url($futureOnly ? "futureevents=true" : ""));
		$doc = new DOMDocument();
		if (!$xml||!$doc->loadXML($xml)) return array();
		
		$events = array();
		$entries = $doc->getElementsByTagName('entry');
		foreach ($entries as $event) {
			$author = $event->getElementsByTagName('author')->item(0);
			$when = $event->getElementsByTagName('when')->item(0);
			$start = $when->getAttribute('startTime');
			$events[] = array(
				'who'		=> self::tagValue($author,'name'),
				'what'		=> self::tagValue($event,'title'),
				'info'		=> self::tagValue($event,'content'),
				'where'		=> self::attributeValue($event,
							'where','valueString'),
				'when'		=> $start,
				'start'		=> $start,
				'end'		=> $when->getAttribute('endTime'),
				'timestamp'	=> strtotime($start),
				'link'		=> self::attributeValue($event,
							'link','href',
							array('rel'=>'alternate'))
			);
			
		}
		return $events;
	}
	
	/*--------------------------------------------------------------------*\
	|* XML PARSING SPECIFICS                                              *|
	\*--------------------------------------------------------------------*/
	public static function tagValue($node,$tag,$requiredAttributes=array(),$valueIfNoChild=false)
	{
		if (!$requiredAttributes) {
			$children = $node->getElementsByTagName($tag);
			return $children->length
				? $children->item(0)->nodeValue
				: ($valueIfNoChild ? $node->nodeValue : "");
		}
		//get tags
		$tags = $node->getElementsByTagName($tag);
		//check attributes
		$element = false;
		for ($i = 0; $i < $tags->length; $i++) {
			$found = true;
			foreach ($requiredAttributes as $k => $v)
				$found &= strcasecmp($tags->item($i)->getAttribute($k),$v) == 0;
			if ($found) {
				$element = $tags->item($i);
				break;
			}
		}
		return $element ? $element->nodeValue : "";
	}
	
	public static function attributeValue($node,$tag,$attribute,$requiredAttributes=array())
	{
		if (!$requiredAttributes) {
			$children = $node->getElementsByTagName($tag);
			return $children->length
				? $children->item(0)->getAttribute($attribute)
				: "";
		}
		//get tags
		$tags = $node->getElementsByTagName($tag);
		//check attributes
		$element = false;
		for ($i = 0; $i < $tags->length; $i++) {
			$found = true;
			foreach ($requiredAttributes as $k => $v)
				$found &= strcasecmp($tags->item($i)->getAttribute($k),$v) == 0;
			if ($found) {
				$element = $tags->item($i);
				break;
			}
		}
		return $element ? $element->getAttribute($attribute) : "";
	}
}
?>
