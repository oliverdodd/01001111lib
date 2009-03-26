<?php
/* 	Graphics Class - a collection of graphics creation and image manipulation
 *		functions.  Uses the Color and Image classes.
 *	
 *	REQUIRES gd2 to be enabled
 	
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
class Graphics
{
	/*--------------------------------------------------------------------*\
	|* CONSTANTS                                                          *|
	\*--------------------------------------------------------------------*/
	//text to ascii div text size constants, requires 01001111.css classes
	const TXT_S = 50;
	const TXT_M = 20;
	const TXT_L = 10;
	
	
	/*--------------------------------------------------------------------*\
	|* FUNCTIONS                                                          *|
	\*--------------------------------------------------------------------*/
	/** GLOBAL PARAMETERS
	 *  @param $img	= pass in an image
	 */
	
	/** fill() - fill an image with a color, within a rectangle if specified
	 *  @param $c	= fill color, if null, default to black
	 *  @param $ox	= x origin of fill, default to 0
	 *  @param $oy	= y origin of fill, default to 0
	 *  @param $fw	= width of fill, null resolves to image width - $ox
	 *  @param $fh	= height of fill, null resolves to image height - $oy
	 */
	public static function fill(&$img,$c=null,$ox=0,$oy=0,$fw=null,$fh=null)
	{
		//parameters
		if (!$img) return null;
		$w = imagesx($img);
		$h = imagesy($img);
		if ($c === null) $c = Color::RGB(0,0,0);
		if ($fw === null) $fw = $w - $ox;
		if ($fh === null) $fh = $h - $oy;
		imagefilledrectangle($img,$ox,$oy,$ox+$fw,$oy+$fh,$c->dec);
	}
	
	/** grid() - draw a grid on the image
	 *  @param $nx	= number of x lines
	 *  @param $ny	= number of y lines, if null, set to nx
	 *  @param $c	= line color, if null, default to black
	 */
	public static function grid(&$img,$nx=0,$ny=null,$c=null)
	{
		//parameters
		if (!$img) return null;
		if ($ny === null) $ny = $nx;
		if ($c === null) $c = Color::RGB(0,0,0);
		$w = imagesx($img);
		$h = imagesy($img);
		//horizontal
		$dx = round($w/$nx);
		$ox = round(($w%$nx)/$dx);
		for ($x=$ox;$x<$w;$x+=$dx) imageline($img,$x,0,$x,$h,$c->dec);
		//vertical
		$dy = round($h/$ny);
		$oy = round(($h%$ny)/$dy);
		for ($y=$oy;$y<$h;$y+=$dy) imageline($img,0,$y,$w,$y,$c->dec);
	}
	
	/** web() - draw a random web on the image
	 *  @param $nx	= number of x lines
	 *  @param $ny	= number of y lines, if null, set to nx
	 *  @param $c	= line color, if null, default to black
	 *  @param $symx= make it symmetrical about the x axis?
	 *  @param $symy= make it symmetrical about the y axis?
	 */
	public static function web(&$img,$nx=0,$ny=null,$c=null,$symx=false,$symy=false)
	{
		//parameters
		if (!$img) return null;
		if ($ny === null) $ny = $nx;
		if ($c === null) $c = Color::RGB(0,0,0);
		$w = imagesx($img);
		$h = imagesy($img);
		if ($symx&&$symy)	{ $nx/=4; $ny/=4; }
		elseif ($symx)		{ $nx/=2; $ny/=2; }
		elseif ($symy)		{ $nx/=2; $ny/=2; }
		//horizontal
		for ($x = 0; $x < $nx; $x++) {
			$rx1 = rand(0,$w); $rx2 = rand(0,$w);
			imageline($img,$rx1,0,$rx2,$h,$c->dec);
			if ($symx) imageline($img,$rx2,0,$rx1,$h,$c->dec);
			if ($symy) imageline($img,$w-$rx1,0,$w-$rx2,$h,$c->dec);
			if ($symx&&$symy)
				imageline($img,$w-$rx2,0,$w-$rx1,$h,$c->dec);
		}
		//vertical
		for ($y = 0; $y < $ny; $y++) {
			$ry1 = rand(0,$h); $ry2 = rand(0,$h);
			imageline($img,0,$ry1,$w,$ry2,$c->dec);
			if ($symx) imageline($img,0,$h-$ry1,$w,$h-$ry2,$c->dec);
			if ($symy) imageline($img,0,$ry2,$w,$ry1,$c->dec);
			if ($symx&&$symy)
				imageline($img,0,$h-$ry2,$w,$h-$ry1,$c->dec);
		}
	}
	
	/** img2ascii() - convert an image to ascii text
	 *  @param $sym	= the symbols to use
	 */
	public static function img2ascii($img,$sym=array())
	{
		//parameters
		$txt = '';
		if (!$img) return $txt;
		if (!$sym) $sym = '$@#%8&s0x67toi;=+-!:,.   ';
				//'   .,:!-+=;iot76x0s&8%#@$';
		if (!is_array($sym)) $sym = str_split($sym);
		$n = count($sym) - 1;
		//dimensions
		$w = imagesx($img);
		$h = imagesy($img);
		//loop
		for ($y = 0; $y < $h; $y++) {
			for ($x = 0; $x < $w; $x++) {
				$rgb = imagecolorat($img,$x,$y);
				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;
				$v = max($r,$g,$b);
				$txt .= $sym[floor($v/255*$n)]; }
		$txt .= "\n"; }
		return $txt;
	}
	public static function img2asciidiv($img,$sym=array(),$tsize=self::TXT_M,$id='')
	{
		$simg = Image::scale($img,$tsize);
		$txt = Graphics::img2ascii($simg,$sym);
		if ($id) $id = 'id="'.$id.'"';
		return '<div '.$id.' class="img2ascii'.$tsize.'">'.
			nl2br(str_replace(' ','&nbsp;',$txt)).'</div>';
	}
	
	///** alterpixelcolor() - apply a color function to each pixel
	// *  @param $f	= the function
	// */
	//static function alterpixelcolors($img,$f)
	//{
	//	if (!$img) return null;
	//	@list($w,$h) = self::dimensions($img);
	//	$alter = new Image($w,$h);
	//	if (!is_callable($f)) return $img;
	//	$i = max(strrpos($f,'->'),strrpos($f,'::'));
	//	$func = ($i) ? array(substr($f,0,$i),substr($f,$i+2)) : $f;
	//	for ($y = 0; $y < $h; $y++) for ($x = 0; $x < $w; $x++) {
	//		$args = array(imagecolorat($img,$x,$y),$x,$y);
	//		imagesetpixel($alter,$x,$y,@call_user_func_array($f,$args));
	//	}
	//	return $alter;
	//}
}
?>