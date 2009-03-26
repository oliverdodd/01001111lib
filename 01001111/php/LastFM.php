<?php
/* 	LastFM Class - Grab certain info from LastFM
 *	Copyright (c) 2009 Oliver C Dodd
 *		* NOTE: You must supply your own Last.FM API key
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
def("lfmIMG_SIZE",	"medium");
class LastFM
{
	/*--------------------------------------------------------------------*\
	|* CONSTANTS                                                          *|
	\*--------------------------------------------------------------------*/
	const BASE_URL	= 'http://ws.audioscrobbler.com/2.0/';
	
	/*--------------------------------------------------------------------*\
	|* VARIABLES                                                          *|
	\*--------------------------------------------------------------------*/
	public $apiKey;
	public $imageSize	= lfmIMG_SIZE;
	
	/*--------------------------------------------------------------------*\
	|* CONSTRUCT                                                          *|
	\*--------------------------------------------------------------------*/
	public function __construct($apiKey)
	{
		$this->apiKey 	= $apiKey;
	}
	
	/*--------------------------------------------------------------------*\
	|* URL/GET                                                            *|
	\*--------------------------------------------------------------------*/
	public function url($method,$args=array())
	{
		$args = is_array($args) ? http_build_query($args) : $args;
		if ($args) $args = "&".$args;
		return self::BASE_URL."?method=$method&api_key=$this->apiKey$args";
	}
	
	public function get($url)
	{
		return @file_get_contents($url);
	}
	
	/*--------------------------------------------------------------------*\
	|* GET/PARSE                                                          *|
	\*--------------------------------------------------------------------*/
	public function recentTracks($user,$limit=50)
	{
		$recentTracksXML = $this->get(
			$this->url("user.getrecenttracks","user=$user&limit=$limit"));
		return $this->parseRecentTracks($recentTracksXML);
	}
	//----------------------------------------------------------------------
	public function topTracks($user,$period="overall")
	{
		$tracksXML = $this->get(
			$this->url("user.getTopTracks","user=$user&period=$period"));
		return $this->parseTracks($tracksXML);
	}
	
	public function topAlbums($user,$period="overall")
	{
		$albumsXML = $this->get(
			$this->url("user.getTopAlbums","user=$user&period=$period"));
		return $this->parseAlbums($albumsXML);
	}
	
	public function topArtists($user,$period="overall")
	{
		$artistsXML = $this->get(
			$this->url("user.getTopArtists","user=$user&period=$period"));
		return $this->parseArtists($artistsXML);
	}
	//----------------------------------------------------------------------
	public function weeklyTrackChart($user)
	{
		$tracksXML = $this->get(
			$this->url("user.getWeeklyTrackChart","user=$user"));
		return $this->parseTracks($tracksXML);
	}
	
	public function weeklyAlbumChart($user)
	{
		$albumsXML = $this->get(
			$this->url("user.getWeeklyAlbumChart","user=$user"));
		return $this->parseAlbums($albumsXML);
	}
	
	public function weeklyArtistChart($user)
	{
		$artistsXML = $this->get(
			$this->url("user.getWeeklyArtistChart","user=$user"));
		return $this->parseArtists($artistsXML);
	}
	
	//----------------------------------------------------------------------
	
	/*--------------------------------------------------------------------*\
	|* PARSE TYPES                                                        *|
	\*--------------------------------------------------------------------*/
	public function parseRecentTracks($xml)
	{
		$doc = new DOMDocument();
		if (!$xml||!$doc->loadXML($xml)) return array();
		
		$tracks = array();
		foreach ($doc->getElementsByTagName("track") as $node) {
			$tracks[] = array(
				'track'		=> self::tagValue($node,'name'),
				'artist'	=> self::tagValue($node,'artist'),
				'album'		=> self::tagValue($node,'album'),
				'image'		=> self::tagValue($node,'image',
							array("size"=>$this->imageSize))
			);
		}
		return $tracks;
	}
	
	public function parseTracks($xml)
	{
		$doc = new DOMDocument();
		if (!$xml||!$doc->loadXML($xml)) return array();
		
		$tracks = array();
		foreach ($doc->getElementsByTagName("track") as $node) {
			$artist = @$node->getElementsByTagName('artist')->item(0);
			$tracks[] = array(
				'rank'		=> $node->getAttribute('rank'),
				'playcount'	=> self::tagValue($node,'playcount'),
				'track'		=> self::tagValue($node,'name'),
				'trackURL'	=> self::tagValue($node,'url'),
				'artist'	=> self::tagValue($artist,'name',false,true),
				'artistURL'	=> self::tagValue($artist,'url'),
				'image'		=> self::tagValue($node,'image',
							array("size"=>$this->imageSize))
			);
		}
		return $tracks;
	}
	
	public function parseAlbums($xml)
	{
		$doc = new DOMDocument();
		if (!$xml||!$doc->loadXML($xml)) return array();
		
		$albums = array();
		foreach ($doc->getElementsByTagName("album") as $node) {
			$artist = @$node->getElementsByTagName('artist')->item(0);
			$albums[] = array(
				'rank'		=> $node->getAttribute('rank'),
				'playcount'	=> self::tagValue($node,'playcount'),
				'artist'	=> self::tagValue($artist,'name',false,true),
				'artistURL'	=> self::tagValue($artist,'url'),
				'album'		=> self::tagValue($node,'name'),
				'albumURL'	=> self::tagValue($node,'url'),
				'image'		=> self::tagValue($node,'image',
							array("size"=>$this->imageSize))
			);
		}
		return $albums;
	}
	
	public function parseArtists($xml)
	{
		$doc = new DOMDocument();
		if (!$xml||!$doc->loadXML($xml)) return array();
		
		$artists = array();
		foreach ($doc->getElementsByTagName("artist") as $node) {
			$artists[] = array(
				'rank'		=> $node->getAttribute('rank'),
				'playcount'	=> self::tagValue($node,'playcount'),
				'artist'	=> self::tagValue($node,'name'),
				'artistURL'	=> self::tagValue($node,'url'),
				'image'		=> self::tagValue($node,'image',
							array("size"=>$this->imageSize))
			);
		}
		return $artists;
	}
	
	/*--------------------------------------------------------------------*\
	|* XML PARSING SPECIFICS                                              *|
	\*--------------------------------------------------------------------*/
	public static function tagValue($node,$tag,$attributes=array(),$valueIfNoChild=false)
	{
		if (!$attributes) {
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
			foreach ($attributes as $k => $v)
				$found &= strcasecmp($tags->item($i)->getAttribute($k),$v) == 0;
			if ($found) {
				$element = $tags->item($i);
				break;
			}
		}
		return $element ? $element->nodeValue : "";
	}
	
	/*--------------------------------------------------------------------*\
	|* DEBUG                                                              *|
	\*--------------------------------------------------------------------*/
	public static function printXML($xml,$toWeb=true)
	{
		echo $toWeb ? nl2br(htmlentities($xml)) : $xml;
	}
}
?>
