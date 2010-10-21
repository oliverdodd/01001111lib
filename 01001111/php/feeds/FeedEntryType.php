<?php
/* 	FeedType Class - Define a feed type (Atom, RSS2,..)
 *	FeedEntryType Class - Define a feed entry
 *	Copyright (c) 2010 Oliver C Dodd
 *
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 */
class FeedEntryType extends FeedEntry {
	public function __construct() {}
	public static function initWithArray($a=array()) {
		$o = new self();
		foreach ($a as $k => $v)
			$o->$k = $v;
		return $o;
	}
}
?>
