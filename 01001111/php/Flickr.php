<?php
/* 	Flickr Class - Grab photos and info from Flickr
 *	Copyright (c) 2009 Oliver C Dodd
 *		* NOTE: You must supply your own Flickr API key
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
class Flickr
{
	/*--------------------------------------------------------------------*\
	|* CONSTANTS                                                          *|
	\*--------------------------------------------------------------------*/
	const REST_URL	= 'http://api.flickr.com/services/rest/';
	
	//sizes
	const SQUARE	= "s";		//75 x 75
	const THUMBNAIL	= "t";		//100 on longest side
	const SMALL	= "m";		//240 on longest side
	const MEDIUM	= false;	//500 on longest side
	const LARGE	= "b";		//1024 on longest side
	
	/*--------------------------------------------------------------------*\
	|* VARIABLES                                                          *|
	\*--------------------------------------------------------------------*/
	public $apiKey;
	
	/*--------------------------------------------------------------------*\
	|* CONSTRUCT                                                          *|
	\*--------------------------------------------------------------------*/
	public function __construct($apiKey)
	{
		$this->apiKey 	= $apiKey;
	}
	
	/*--------------------------------------------------------------------*\
	|* URLS/GET                                                            *|
	\*--------------------------------------------------------------------*/
	public function url($method,$args=array())
	{
		$args = is_array($args) ? http_build_query($args) : $args;
		if ($args) $args = "&".$args;
		return self::REST_URL.	"?api_key=$this->apiKey".
					"&method=$method".
					"&format=php_serial".
					$args;
	}
	
	public static function photoSrc($farm,$server,$photo,$secret,$size=self::SMALL)
	{
		if ($size) $size = "_$size";
		return "http://farm$farm.static.flickr.com/$server/{$photo}_{$secret}$size.jpg";
		/*
		[id] => 2230206383
		[secret] => 76de3fec4f
		[server] => 2196
		[farm] => 3
		[title] => trickling through
		[isprimary] => 0
		*/
	}
	
	public function get($url,$key=false)
	{
		$data = @unserialize(@file_get_contents($url));
		return $key === false
			? $data
			: $this->getIndex($data,$key);
	}
	public function getIndex($array,$i)
	{
		if (!is_array($array)) return null;
		if (is_array($i)) {
			while(count($i)) {
				$key = array_shift($i);
				if (($key === null)||(!isset($array[$key])))
					return null;
				else $array = $array[$key];
			}
			return $array;
		}
		else return isset($array[$i]) ? $array[$i] : null;
	}
	
	public function execute($method,$args,$key=false)
	{
		return $this->get($this->url($method,$args),$key);
	}
	
	/*--------------------------------------------------------------------*\
	|* GET                                                                *|
	\*--------------------------------------------------------------------*/
	/*-SETS---------------------------------------------------------------*/
	public function photoSet($setID)
	{
		return $this->execute(
			'flickr.photosets.getPhotos',
			"photoset_id=$setID",
			'photoset');
		
		/*	[photos] = array()
			[page] => 1
			[per_page] => 500
			[perpage] => 500
			[pages] => 1
			[total] => 170
		*/
	}
	/*-USER---------------------------------------------------------------*/
	public function photosFromUser($userID,$page=1,$isUsername=false)
	{
		if ($isUsername) $userID = $this->userIDFromUserName($userID);
		return $this->execute(
			'flickr.people.getPublicPhotos',
			"user_id=$userID&page=$page");//,'photoset');
	}
	public function userIDFromUserName($username)
	{
		return $this->execute(
			'flickr.people.findByUsername',
			"username=$username",array('user','nsid'));
	}
	
	/*-PHOTO--------------------------------------------------------------*/
	public function photoInfo($photoID)
	{
		return $this->execute(
			'flickr.photos.getInfo',
			"photo_id=$photoID",
			'photo');
	}
	
	public function photoSizes($photoID)
	{
		return $this->execute(
			'flickr.photos.getSizes',
			"photo_id=$photoID",
			array('sizes','size'));
	}
	
	/*--------------------------------------------------------------------*\
	|* PARSE                                                              *|
	\*--------------------------------------------------------------------*/
	public static function parsePhotoUrl($url)
	{
		//http://www.flickr.com/photos/[user]/[photo_id]/
		$n = sscanf($url,"http://www.flickr.com/photos/%[^/]/%[^/]",$user,$id);
		return array('user'=>$user,'photo_id'=>$id);
	}
}
?>
