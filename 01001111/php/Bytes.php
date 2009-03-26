<?php
/* 	Bytes - simplified Byte manipulation functions
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
/** pack_array() - run the pack function on the values in an array
 */
function pack_array($format,$array)
{
	return call_user_func_array("pack",
		array_merge(array($format),(array)$array));
}

/*----------------------------------------------------------------------------*\
|* ? -> BYTE STRING                                                           *|
\*----------------------------------------------------------------------------*/
/** hex2bytestring() - convert a hexidecimal string to a byte string
 *  @param $hex	= the hex string or array of hex byte pairs
 *	NOTE: make sure the hex string has an even number of characters
 */
function hex2bytestring($hex) { return pack('H*',$hex); }

/** bytearray2bytestring() - convert a an array of byte integers to a byte string
 *  @param $array = the byte array
 */
function bytearray2bytestring($array) { return pack_array('C*',$array); }

/*----------------------------------------------------------------------------*\
|* BYTE STRING -> ?                                                           *|
\*----------------------------------------------------------------------------*/
/** bytestring2hex() - convert a byte string to a hexidecimal string
 *  @param $bytestring	= the byte string
 */
function bytestring2hex($bytestring)
{
	$bytes = unpack('H*',$bytestring);
	return isset($bytes[1]) ? $bytes[1] : "";
}

/** bytestring2bytearray() - convert a byte string to an array of byte integers
 *  @param $bytestring = the byte string
 */
function bytestring2bytearray($bytestring) { return unpack('C*',$bytestring); }

/*----------------------------------------------------------------------------*\
|* BINARY <-> HEX                                                             *|
\*----------------------------------------------------------------------------*/
/** binhex() - convert a binary string to a hexidecmial string
 *  @param $bin = the string of 1's and 0's
 */
function binhex($bin)
{
	$hex = "";
	foreach (str_split(str_replace(" ","",$bin),4) as $byte)
	$hex .= dechex(bindec($byte));
	return $hex;
}
/** hexbin() - convert a hexadecimal string to a binary string
 *  @param $hex		= the hexadecimal string
 *  @param $groupIn4	= seperate every 4 characters with a space?
 */
function hexbin($hex,$groupIn4s=false)
{
	$bin = array();
	foreach (str_split(str_replace(" ","",$hex)) as $c) {
		$b = decbin(hexdec($c));
		while (strlen($b) < 4) $b = "0$b";
		$bin[] = $b;
	}
	return implode($groupIn4s ? " " : "",$bin);
}


/*----------------------------------------------------------------------------*\
|* CHECKSUM                                                                   *|
\*----------------------------------------------------------------------------*/
/** checksum16() - run a 16 bit checksum on a series of bytes
 *  @param $bytes = the bytes as an array of integers or a byte string
 *  @param $asByteString = return the checksum as a byteString? default is integer
 */
function checksum16($bytes,$asByteString=false)
{
	$checksum = 0;
	if (is_string($bytes)) $bytes = str_split($bytes,1);
	foreach ((array)$bytes as $byte) {
		if (is_string($byte)) $byte = ord($byte);
		$checksum = 	(($checksum >> 1)
					+ ($checksum & 1 ? 0x8000 : 0)
					+ ($byte & 0xff)
				) & 0xffff;
	}
	return $asByteString ? pack("n",$checksum) : $checksum;
}
?>
