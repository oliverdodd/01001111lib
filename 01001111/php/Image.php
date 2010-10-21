<?php
/* 	Image Class - A simple interface for image handling and manipulation.
 *		Static rather than member methods used to maximize utility
 *
 *	REQUIRES gd2 to be enabled
 *
 *	Copyright (c) 2007 Oliver C Dodd
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a 
 *  copy of this software and associated documentation files (the "Software"),
 *  to deal in the Software without restriction, including without limitation
 *  the rights to use, copy, modify, merge, publish, distribute, sublicense,
 *  and/or sell copies of the Software, and to permit persons to whom the 
 *  Software is furnished to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in
 *  all copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL 
 *  THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 *  FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 *  DEALINGS IN THE SOFTWARE.
 */
class Image
{
	/*--------------------------------------------------------------------*\
	|* CONSTANTS			                                      *|
	\*--------------------------------------------------------------------*/
	/** Image types */
	const JPG = IMAGETYPE_JPEG;
	const PNG = IMAGETYPE_PNG;
	const BMP = IMAGETYPE_WBMP;
	const GIF = IMAGETYPE_GIF;
	
	/*--------------------------------------------------------------------*\
	|* CREATE/LOAD/OUTPUT                                                 *|
	\*--------------------------------------------------------------------*/
	/** create() - create an image
	 *  @param $w	= width of image
	 *  @param $h	= height of image
	 */
	public static function create($w,$h) { return imagecreatetruecolor($w,$h); }
	
	/** load() - load an image
	 *  @param $file	= path to the image
	 *  @param $type	= type of image
	 */
	public static function load($file,$type=null)
	{
		if (!$file) return null;
		if (!$type) list(,,$type,) = getimagesize($file);
		if ($type == Image::JPG) return @ImageCreateFromJpeg($file);
		if ($type == Image::PNG) return @ImageCreateFromPng($file);
		if ($type == Image::BMP) return @ImageCreateFromWbmp($file);
		if ($type == Image::GIF) return @ImageCreateFromGif($file);
		return null;
	}
	
	/** output() - output the image to the browser or a file
	 *  @param $file	= the output file, if null send to browser
	 *  @param $type	= type of image to output, if null use JPEG
	 */
	public static function output($img,$file=null,$type=null)
	{
		if (!$img) return null;
		if ($file&&!$type) {
			$ext = Filesystem::extension($file);
			$type = Image::typeFromExtension($ext);
		}
		if (!$type) $type = Image::JPG;
		if ($file) {
			if ($type == Image::JPG) ImageJpeg($img,$file);
			if ($type == Image::PNG) ImagePng($img,$file);
			if ($type == Image::BMP) ImageWbmp($img,$file);
			if ($type == Image::GIF) ImageGif($img,$file); }
		else {	header("Content-type: ".image_type_to_mime_type($type));
			if ($type == Image::JPG) ImageJpeg($img);
			if ($type == Image::PNG) ImagePng($img);
			if ($type == Image::BMP) ImageWbmp($img);
			if ($type == Image::GIF) ImageGif($img); }
	}
	
	/*--------------------------------------------------------------------*\
	|* INFORMATION                                                        *|
	\*--------------------------------------------------------------------*/
	/** dimensions() - get the image dimensions as array [w,h]
	 */
	public static function dimensions($img)
	{
		if (is_file($img)) return getimagesize($img);
		if (is_resource($img)) return array(imagesx($img),imagesy($img));
		return array(0,0);
	}
	
	/** scaledimensions() - determine scale dimensionsions and return array:
	 *	[scaled width,scaled height,original width,original height]
	 *  @param $scale	= scale, total or [x,y] or 'x,y'
	 *  @param $unit	= scale unit, '%'(default) or 'px'
	 */
	public static function scaledimensions($img,$scale=100,$unit='%')
	{
		//full dimensions
		$w = imagesx($img);
		$h = imagesy($img);
		//determine scale
		if (!is_array($scale)) $scale = explode(',',$scale);
		$x = $scale[0];
		$y = (count($scale) > 1) ? $scale[1] : $x;
		if ($unit == 'px')	{ $sw = $x;	   $sh = $y; }
		else			{ $sw = $w*$x/100; $sh = $h*$y/100; }
		return array($sw,$sh,$w,$h);
	}
	
	/*--------------------------------------------------------------------*\
	|* MANIPULATION                                                       *|
	\*--------------------------------------------------------------------*/
	/** copy() - useful for opening up all colors
	 */
	public static function copy($img)
	{
		if (!$img) return null;
		//dimensions
		$w = imagesx($img);
		$h = imagesy($img);
		$new = Image::create($w,$h);
		if (!$new) return null;
		imagecopy($new,$img,0,0,0,0,$w,$h);
		return $new;
	}
	
	/** scale() - scale the image by either one value or an x and y value
	 *  @param $scale	= scale, total or [x,y] or 'x,y'
	 *  @param $unit	= scale unit, '%'(default) or 'px'
	 *  @param $resample	= resample or just resize?
	 */
	public static function scale($img,$scale=100,$unit='%',$resample=true)
	{
		if (!$img) return null;
		//dimensions
		list($sw,$sh,$w,$h) = Image::scaledimensions($img,$scale,$unit);
		//if (($sw === $w)&&($sh === $h)) return $img;
		//generate new image
		$new = Image::create($sw,$sh);
		if (!$new) return null;
		if ($resample)
			imagecopyresampled($new,$img,0,0,0,0,$sw,$sh,$w,$h);
		else	imagecopyresized($new,$img,0,0,0,0,$sw,$sh,$w,$h);
		return $new;
	}
	public static function resize($img,$scale=100,$unit='%')
	{
		return Image::scale($img,$scale,$unit,false);
	}
	public static function resample($img,$scale=100,$unit='%')
	{
		return Image::scale($img,$scale,$unit,true);
	}
	
	/** crop() - crop the image
	 *  @param $x	= new x origin
	 *  @param $y	= new y origin
	 *  @param $w	= width of crop
	 *  @param $h	= height of crop
	 */
	public static function crop($img,$x,$y,$w,$h)
	{
		if (!$img) return null;
		$new = Image::create($w,$h);
		if (!$new) return null;
		imagecopy($new,$img,0,0,$x,$y,$w,$h);
		return $new;
	}
	
	/** rotate() - rotate the image
	 *  @param $a	= angle in degrees
	 *  @param $bg	= background color, default black
	 */
	public static function rotate($img,$a,$bg=0)
	{
		if (!$img) return null;
		return imagerotate($img,$a,$bg);
	}
	
	/** alterpixels() - apply a function to each pixel
	 *  @param $f	= the function
	 */
	public static function alterpixels($img,$f)
	{
		if (!$img) return null;
		@list($w,$h) = self::dimensions($img);
		$alter = Image::copy($img);
		if (!is_callable($f)) return $img;
		$i = max(strrpos($f,'->'),strrpos($f,'::'));
		$func = ($i) ? array(substr($f,0,$i),substr($f,$i+2)) : $f;
		for ($y = 0; $y < $h; $y++) for ($x = 0; $x < $w; $x++) {
			$args = array(imagecolorat($img,$x,$y),$x,$y);
			imagesetpixel($alter,$x,$y,
				call_user_func_array($func,$args));
		}
		return $alter;
	}
	
	/*--------------------------------------------------------------------*\
	|* MISC                                                               *|
	\*--------------------------------------------------------------------*/
	/** isImage() - is the file an image?
	 *  @param $file	= the file in question
	 */
	public static function isImage($file)
	{
		if (!$file) return false;
		$ext = Filesystem::extension($file);
		return (stripos('jpg,jpeg,gif,png,bmp',$ext) !== false);
	}
	
	/** typeFromExtension() - get image type from extension
	 *  @param $ext		= the extension
	 */
	public static function typeFromExtension($ext)
	{
		switch ($ext) {	case('jpg'): case('jpeg'): return Image::JPG;
				case('gif'): return Image::GIF;
				case('png'): return Image::PNG;
				case('bmp'): return Image::BMP; }
		return null;
	}
}
?>