<?php
/* 	FeedType Class - Define a feed type (Atom, RSS2,..)
 *	Copyright (c) 2010 Oliver C Dodd
 *
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 */
class FeedType extends Feed
{
	public static $types = array();
	public $rootTag;
	public $entryTag;
	
	public function __construct() {}
	public static function initWithArray($a=array()) {
		$o = new self();
		foreach ($a as $k => $v)
			$o->$k = $v;
		return $o;
	}
	
	public static function detect($doc)
	{
		foreach(self::$types as $id => $type) {
			if ($doc->getElementsByTagName($type->rootTag)->length > 0) {
				return $type;
			}
		}
	}
}
/*-TYPES (only Atom and RSS2 currently configured)----------------------------*/
FeedType::$types['atom'] =  FeedType::initWithArray(array(
	'rootTag'	=> 'feed',
	'title'		=> 'title',
	'link'		=> array('tag' => 'link',
				 'attributes' => array('rel' => 'alternate'),
				 'get' => 'href'),
	'entryTag'	=> 'entry',
	'entries'	=> FeedEntryType::initWithArray(array(
		'title'		=> 'title',
		'date'		=> 'published',
		'link'		=> array('tag' => 'link',
					 'attributes' => array('rel' => 'alternate'),
					 'get' => 'href'),
		'content'	=> 'content'
	))
));	
FeedType::$types['rss2'] =  FeedType::initWithArray(array(
	'rootTag'	=> 'channel',
	'title'		=> 'title',
	'link'		=> 'link',
	'entryTag'	=> 'item',
	'entries'	=> FeedEntryType::initWithArray(array(
		'title'		=> 'title',
		'date'		=> 'pubDate',
		'link'		=> 'link',
		'content'	=> 'description'
	))
));
?>
