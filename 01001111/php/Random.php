<?php
/** 	Random - Generate a variety of random things
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
if (!defined('RAND_MAX')) define('RAND_MAX', mt_getrandmax());
class Random
{
	/*--------------------------------------------------------------------*\
	|* CONSTANTS                                                          *|
	\*--------------------------------------------------------------------*/
	const MALE		= 1;
	const FEMALE		= 2;
	
	const ENGLISH		= 0;
	
	/*--------------------------------------------------------------------*\
	|* CALL PLURAL                                                        *|
	\*--------------------------------------------------------------------*/
	public static function __mcall($m,$args)
	{
		$f = array(__CLASS__,$m);
		if (!is_callable($f)||!is_array($args)) return 'a';
		$n = array_shift($args);
		if (!is_numeric($n)) return 'b';
		$s = '';
		while($n--) $s .= call_user_func_array($f,$args);
		return $s;
	}
	
	/*--------------------------------------------------------------------*\
	|* NUMERICAL                                                          *|
	\*--------------------------------------------------------------------*/
	public static function number($min=0,$max=RAND_MAX)
	{
		return mt_rand($min,$max);
	}
	
	public static function digit($min=0,$max=9)
	{
		return mt_rand($min,$max);
	}
	public static function digits()
	{
		$a = func_get_args(); return self::__mcall('digit',$a);
	}
	
	public static function binary($n=1) { return Random::digits($n,0,1); }
	
	public static function telephoneNumber()
	{
		return	Random::number(200,999).'-'.
			Random::number(200,999).'-'.
			Random::digits(4);
	}
	
	public static function zipCode($digits=5)
	{
		return Random::digits($digits);
	}
	
	/*--------------------------------------------------------------------*\
	|* CHARACTERS/WORDS                                                   *|
	\*--------------------------------------------------------------------*/
	public static function letter() { return chr(Random::number(97,122)); }
	public static function letters($n=1)
	{
		$a = func_get_args();
		return self::__mcall('letter',$a);
	}
	
	public static function character($html=false)//,$charset=Random::ENGLISH)
	{
		$c = chr(Random::number(33,126));
		return $html ? htmlentities($c) : $c;
	}
	public static function characters($n=1)
	{
		$a = func_get_args();
		return self::__mcall('character',$a);
	}
	
	/*--------------------------------------------------------------------*\
	|* NAMES                                                              *|
	\*--------------------------------------------------------------------*/
	public static function name($flags=0)
	{
		return Random::firstName($flags).' '.Random::lastName();
	}
	
	public static function lastName()
	{
		$a = array(
		"Smith",	"Johnson",	"Williams",	"Jones",
		"Brown",	"Davis",	"Miller",	"Wilson",
		"Moore",	"Taylor",	"Anderson",	"Thomas",
		"Jackson",	"White",	"Harris",	"Martin",
		"Thompson",	"Garcia",	"Martinez",	"Robinson",
		"Clark",	"Rodriguez",	"Lewis",	"Lee",
		"Walker",	"Hall",		"Allen",	"Young",
		"Hernandez",	"King",		"Wright",	"Lopez",
		"Hill",		"Scott",	"Green",	"Adams",
		"Baker",	"Gonzalez",	"Nelson",	"Carter",
		"Mitchell",	"Perez",	"Roberts",	"Turner",
		"Phillips",	"Campbell",	"Parker",	"Evans",
		"Edwards",	"Collins",	"Stewart",	"Sanchez",
		"Morris",	"Rogers",	"Reed",		"Cook",
		"Morgan",	"Bell",		"Murphy",	"Bailey",
		"Rivera",	"Cooper",	"Richardson",	"Cox",
		"Howard",	"Ward",		"Torres",	"Peterson",
		"Gray",		"Ramirez",	"James",	"Watson",
		"Brooks",	"Kelly",	"Sanders",	"Price",
		"Bennett",	"Wood",		"Barnes",	"Ross",
		"Henderson",	"Coleman",	"Jenkins",	"Perry",
		"Powell",	"Long",		"Patterson",	"Hughes",
		"Flores",	"Washington",	"Butler",	"Simmons",
		"Foster",	"Gonzales",	"Bryant",	"Alexander",
		"Russell",	"Griffin",	"Diaz",		"Hayes");
		return $a[rand(0,count($a)-1)];
	}
	
	public static function firstName($flags=0)
	{
		$m = (($flags == 0)||($flags == Random::MALE)) ? array(
		"James",	"John",		"Robert",	"Michael",
		"William",	"David",	"Richard",	"Charles",
		"Joseph",	"Thomas",	"Christopher",	"Daniel",
		"Paul",		"Mark",		"Donald",	"George",
		"Kenneth",	"Steven",	"Edward",	"Brian",
		"Ronald",	"Anthony",	"Kevin",	"Jason",
		"Matthew",	"Gary",		"Timothy",	"Jose",
		"Larry",	"Jeffrey",	"Frank",	"Scott",
		"Eric",		"Stephen",	"Andrew",	"Raymond",
		"Gregory",	"Joshua",	"Jerry",	"Dennis",
		"Walter",	"Patrick",	"Peter",	"Harold",
		"Douglas",	"Henry",	"Carl",		"Arthur",
		"Ryan",		"Roger",	"Joe",		"Juan",
		"Jack",		"Albert",	"Jonathan",	"Justin",
		"Terry",	"Gerald",	"Keith",	"Samuel",
		"Willie",	"Ralph",	"Lawrence",	"Nicholas",
		"Roy",		"Benjamin",	"Bruce",	"Brandon",
		"Adam",		"Harry",	"Fred",		"Wayne",
		"Billy",	"Steve",	"Louis",	"Jeremy",
		"Aaron",	"Randy",	"Howard",	"Eugene",
		"Carlos",	"Russell",	"Bobby",	"Victor",
		"Martin",	"Ernest",	"Phillip",	"Todd",
		"Jesse",	"Craig",	"Alan",		"Shawn",
		"Clarence",	"Sean",		"Philip",	"Chris",
		"Johnny",	"Earl",		"Jimmy",	"Antonio")
		: array();
		$f = (($flags == 0)||($flags == Random::FEMALE)) ? array(
		"Mary",		"Patricia",	"Linda",	"Barbara",
		"Elizabeth",	"Jennifer",	"Maria",	"Susan",
		"Margaret",	"Dorothy",	"Lisa",		"Nancy",
		"Karen",	"Betty",	"Helen",	"Sandra",
		"Donna",	"Carol",	"Ruth",		"Sharon",
		"Michelle",	"Laura",	"Sarah",	"Kimberly",
		"Deborah",	"Jessica",	"Shirley",	"Cynthia",
		"Angela",	"Melissa",	"Brenda",	"Amy",
		"Anna",		"Rebecca",	"Virginia",	"Kathleen",
		"Pamela",	"Martha",	"Debra",	"Amanda",
		"Stephanie",	"Carolyn",	"Christine",	"Marie",
		"Janet",	"Catherine",	"Frances",	"Ann",
		"Joyce",	"Diane",	"Alice",	"Julie",
		"Heather",	"Teresa",	"Doris",	"Gloria",
		"Evelyn",	"Jean",		"Cheryl",	"Mildred",
		"Katherine",	"Joan",		"Ashley",	"Judith",
		"Rose",		"Janice",	"Kelly",	"Nicole",
		"Judy",		"Christina",	"Kathy",	"Theresa",
		"Beverly",	"Denise",	"Tammy",	"Irene",
		"Jane",		"Lori",		"Rachel",	"Marilyn",
		"Andrea",	"Kathryn",	"Louise",	"Sara",
		"Anne",		"Jacqueline",	"Wanda",	"Bonnie",
		"Julia",	"Ruby",		"Lois",		"Tina",
		"Phyllis",	"Norma",	"Paula",	"Diana",
		"Annie",	"Lillian",	"Emily",	"Robin")
		: array();
		$a = array_merge($m,$f);
		return $a[rand(0,count($a)-1)];
	}
}
?>