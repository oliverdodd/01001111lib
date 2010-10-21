<?php
/* 	Mandlebrot - Create a mandlebrot set fractal image
 *		requires the 01001111 Color Class
 *
 *	Copyright (c) 2008 Oliver C Dodd
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
class Mandlebrot
{
	/*--------------------------------------------------------------------*\
	|* CONSTANTS                                                          *|
	\*--------------------------------------------------------------------*/
	/*-dimensions---------------------------------------------------------*/
	const dWIDTH		= 256;
	const dHEIGHT		= 256;
	/*-bounds-------------------------------------------------------------*/
	const dX1 = -1.5;
	const dY1 = -1;
	const dX2 =  0.5;
	const dY2 =  1;
	/*-depth---------------------------------------------------------*/
	const dMAX_DEPTH	= 270;
	/*-colors-------------------------------------------------------------*/
	const dCOLOR0		=        0;//black
	const dCOLOR1		= 16711680;//red
	const dCOLOR2		=      255;//blue
	const dSATURATION1	= 100;
	const dSATURATION2	= 100;
	const dBRIGHTNESS1	= 100;
	const dBRIGHTNESS2	= 100;
	
	/*--------------------------------------------------------------------*\
	|* VARIABLES                                                          *|
	\*--------------------------------------------------------------------*/
	/*-dimensions---------------------------------------------------------*/
	public $width		= self::dWIDTH;	//image width
	public $height		= self::dHEIGHT;//image height
	/*-bounds-------------------------------------------------------------*/
	public $x1		= self::dX1;	//"minimum" x bound
	public $y1		= self::dY1;	//"minimum" y bound
	public $x2		= self::dX2;	//"maximum" x bound
	public $y2		= self::dY2;	//"maximum" y bound
	/*-depth--------------------------------------------------------------*/
	public $depth		= self::dMAX_DEPTH;
	/*-colors-------------------------------------------------------------*/
	public $color0		= self::dCOLOR0;//color inside the set (z = infinity)
	public $color1		= self::dCOLOR1;//color at minimum z (z = 0)
	public $color2		= self::dCOLOR2;//color at maximum z (|z| > 2)
	
	/*--------------------------------------------------------------------*\
	|* CONSTRUCT                                                          *|
	\*--------------------------------------------------------------------*/
	public function __construct(){}
	
	/*--------------------------------------------------------------------*\
	|* COLORS/PIXELS                                                      *|
	\*--------------------------------------------------------------------*/
	/** colors() - generate an array of the color values of the pixels
	 *  @return	- array of colors for the i(y1 => x1,x2,x4),(y2 => x1,x2,x4)....
	 */
	public function colors()
	{
		if (!$this->depth)	return array();
		$c1 = Color::DEC($this->color1);
		$c2 = Color::DEC($this->color2);
		
		$dHue		= ($c2->h - $c1->h)/$this->depth;
		$dSaturation	= ($c2->s - $c1->s)/$this->depth;
		$dBrightness	= ($c2->v - $c1->v)/$this->depth;
		$colors = array();
		for ($i = 0; $i < $this->depth; $i++) {
			$cI = Color::HSV(	$c1->h + $i*$dHue,
						$c1->s + $i*$dSaturation,
						$c1->v + $i*$dBrightness);
			$colors[$i] = $cI->dec;
		}
		return $colors;
	}
	
	/** pixels() - generate an array of the color values of the pixels
	 *  @return	- array of pixels (y1 => x1,x2,x4),(y2 => x1,x2,x4)....
	 */
	public function pixels()
	{
		if (	($this->x1 === $this->x2)||
			($this->y1 === $this->y2)||
			(!$this->depth))	return array();
		$dX = ($this->x2 - $this->x1)/$this->width;
		$dY = ($this->y2 - $this->y1)/$this->height;
		$colors = $this->colors();
		$pixels = array();
		for ($j = 0; $j < $this->height; $j++) {
			$pixels[$j] = array();
			for ($i = 0; $i < $this->width; $i++) {
				//x,y
				$x = $this->x1+$i*(($this->x2 - $this->x1)/($this->width-1));
				$y = $this->y1+$j*(($this->y2 - $this->y1)/($this->height-1));
				//C = x+yi : z = 0+0i
				$z = array(0,0);
				$x_squared = 0;
				$y_squared = 0;
				$d = 0;
				while (($d <= $this->depth)&&
					($x_squared+$y_squared <= 4)) {
					
					$x_squared = pow($z[0],2);
					$y_squared = pow($z[1],2);
					//z(n+1) = z(n)^2+c
					$zn = $z;
					//(x+yi)^2 =	real: x^2-y^2 
					//		imaginary: 2*real*imaginary
					$z[0] = $x_squared - $y_squared;
					$z[1] = 2 * $zn[0]*$zn[1];
					// Add Complex Number
					$z[0] = $z[0] + $x;
					$z[1] = $z[1] + $y;
					
					$d++;
				}
				$pixels[$j][$i] = ($d >= $this->depth)
					? $this->color0
					: $colors[$d];
			}
		}
		return $pixels;
	}
	
	/*--------------------------------------------------------------------*\
	|* GENERATE/DISPLAY                                                   *|
	\*--------------------------------------------------------------------*/
	/** generate() - generate the Mandlebrot fractal based on the current
	 *		parameters
	 */
	public function generate()
	{
		$image = imagecreatetruecolor($this->width,$this->height);
		$pixels = $this->pixels();
		foreach ($pixels as $y => $xs)
			foreach ($xs as $x => $c)
				imagesetpixel($image,$x,$y,$c);
		return $image;
	}
	
	/** display() - output the mandlebrot fractal to a browser
	 */
	public function display()
	{
		//allow ouput or save with optional image format
		
		
		$image = $this->generate();
		header("Content-type: image/png");
		imagepng($image);
	}
}
?>