<?php
/** 	Database Explorer - show the contents of a database table in a paged,
 *		sortable (X)HTML table.  Requires 01001111 library
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
/*----------------------------------------------------------------------------*\
|* CONFIGURATION CONSTANTS/OPTIONS                                            *|
\*----------------------------------------------------------------------------*/
def('DBE_ENTRIES_PER_PAGE', 	20);
def('DBE_PAGE_LIMIT',		0);
def('DBE_SORT_ORDER', 		Database::D);
def('DBE_ORDER_ASC_INDICATOR',	'&uarr;');
def('DBE_ORDER_DESC_INDICATOR',	'&darr;');
def('DBE_ORDER_NO_INDICATOR',	'&nbsp;');
def('DBE_DATE_FORMAT',		'Y-m-d H:i:s');
def('DBE_DISPLAY_FORMAT',	'
	[:table:]
	<div class="count">[:count:] Results, </div>
	<div class="showing">Showing: ([:showing:]) </div>
	<div class="pages">[:pages:]</div>');

class DatabaseExplorer
{
	/*--------------------------------------------------------------------*\
	|* CONSTANTS/VARIABLES/CONSTRUCTION                                   *|
	\*--------------------------------------------------------------------*/
	const scID	= 'sortColumn';
	const soID	= 'sortOrder';
	const pgID	= 'page';
	
	/*--------------------------------------------------------------------*\
	|* CONSTANTS/VARIABLES/CONSTRUCTION                                   *|
	\*--------------------------------------------------------------------*/
	public $id;
	//db
	public $database;	//01001111 Database object
	public $table;		//table name
	public $columns;	//columns in the table
	//options
	public $displayColumns;	//columns to display array(field => displayName)
	public $columnFormats;	//format particular columns
	public $rowClasses;	//format particular rows
	public $rowClassesLen;	//number of row classes
	public $columnClasses;	//format particular columns
	public $columnClassesLen; //number of column classes
	public $includeColumnTypes; //include the column types in the header?
	//html
	public $divID;		//table/container id
	public $format;		//format string for output
	//sort
	public $sortColumn;	//sort column
	public $order;		//sort order
	public $dOrder;		//default sort order
	//limit
	public $limit;		//limit (entries per page)
	public $page;		//what page are we on?
	//ids for request parameters
	public $scID;		//sort column id
	public $soID;		//sort order id
	public $pgID;		//page id
	//extra javascript parameters
	public $jsParams;
	
	/** DatabaseExplorer - display/navigate the contents of a database table
	 *  @param $database	- 01001111 database object
	 *  @param $table	- table name
	 *  @param $divID	- id of the div/container, required for updating
	 *  @param $opt		- optional parameter array
	 *	format			- the format string for outputting
	 *	sortColumn		- the default column to sort by
	 *	displayColumns		- columns to display
	 *				  array(field => displayName)
	 *	columnFormats		- associative array of columns needing
	 *				  a particulat format applied before
	 *				  displaying.  columnID => callback
	 *	rowClasses		- a numerically indexed array where the
	 *				values are classes intended to provide
	 *				alternating styles on the rows
	 *	columnClasses		- either numerically indexed or columnID
	 *				based array where the values are classes
	 *				intended to provide styles for the columns
	 *	includeColumnTypes	- include the column types in the header?
	 */
	public function __construct($database,$table,$divID,$opt=array(),$jsParams='')
	{
		/* required params */
		$this->database		= $database;
		$this->table		= $table;
		$this->divID		= $divID;
		$this->id		= "dbe".sha1($divID.$table);
		$this->columns		= $this->database->columnTypes($this->table);
		/* optional params */
		$this->format		= _::A($opt,'format');
		$sortColumn		= _::A($opt,'sortColumn');
		$this->dOrder		= _::A($opt,'sortOrder',DBE_SORT_ORDER);
		$this->displayColumns	= _::A($opt,'displayColumns');
		$this->columnFormats	= _::A($opt,'columnFormats',array());
		$this->rowClasses	= _::A($opt,'rowClasses',array());
		$this->rowClassesLen	= count($this->rowClasses);
		$this->columnClasses	= _::A($opt,'columnClasses',array());
		$this->columnClassesLen	= count($this->columnClasses);
		$this->includeColumnTypes = _::A($opt,'includeColumnTypes',false);
		/* sort */
		$this->sortColumn	= _::REQUEST(self::scID,$sortColumn);
		/* order */
		$this->order		= _::REQUEST(self::soID,$this->dOrder);
		/* page */
		$this->page		= _::REQUEST(self::pgID,1);
		/* jsparams */
		$this->jsParams		= $jsParams;
		/* check for a REQUEST with the id, if so, output the table */
		if (isset($_REQUEST[$this->id])) die($this->display(false));
	}
	
	/*--------------------------------------------------------------------*\
	|* DISPLAY                                                            *|
	\*--------------------------------------------------------------------*/
	public function display($js=true)
	{
		$count = $this->count();
		$pages = $this->pages($count);
		$showing = $this->showing($count);
		$table = $this->table();
		$data = array(
			'table'		=> $table,
			'pages'		=> $pages,
			'count'		=> $count,
			'showing'	=> $showing
		);
		return $this->format($data).(($js) ? $this->javascript() : '');
		//	.'<!--'.executionTime().'-->';
	}
	
	public function format($data)
	{
		$format = ($this->format) ? $this->format : DBE_DISPLAY_FORMAT;
		foreach ($data as $k => $v) 
			$format = str_replace("[:$k:]",$v,$format);
		return $format;
	}
	
	public function formatColumn($data,$callback)
	{
		return is_callable($callback)
			? call_user_func_array($callback,$data)
			: $data;
	}
	
	public static function timestamp($t) { return date(DBE_DATE_FORMAT,$t+0); }
	
	/*--------------------------------------------------------------------*\
	|* TABLE                                                              *|
	\*--------------------------------------------------------------------*/
	public function table()
	{
		$columns = ($this->displayColumns)
			? $this->displayColumns
			: _::AKV($this->columns);
		$entries = $this->database->select($this->table,
			($this->displayColumns)
				? implode(',',array_keys($this->displayColumns))
				: '*',
			'',$this->orderBy($columns),$this->limit());
		return	"<table>".
				$this->colgroup($columns).
				$this->header($columns).
				$this->body($entries).
				$this->footer($columns).
			"</table>";
	}
	
	public function colgroup($columns)
	{
		$c = '';
		$i = 0;
		foreach ($columns as $f => $n) {
			$class = "";
			if ($this->columnClasses) {
				$m = $this->columnClassesLen
					? $i % $this->columnClassesLen
					: 0;
				if (isset($this->columnClasses[$n]))
					$class = " class='{$this->columnClasses[$n]}' ";
				elseif (isset($this->columnClasses[$i]))
					$class = " class='{$this->columnClasses[$i]}' ";
				elseif (isset($this->columnClasses[$m]))
					$class = " class='{$this->columnClasses[$m]}' ";
			}
			$c .= "<col$class></col>";
			$i++;
		}
		return "<colgroup>$c</colgroup>";
		//return $c;
	}
	
	public function header($columns)
	{
		$h = '';
		$h2 = '';
		foreach ($columns as $f => $n) {
			$h .=	"<th{$this->sortOnClick($f)}>".
					"$n&nbsp;".$this->orderIndicator($f).
				"</th>";
			$h2 .= "<th>"._::A($this->columns,$f)."</th>";
		}
		if (!$this->includeColumnTypes) $h2 = "";
		return "<thead><tr>$h</tr>$h2</thead>";
	}
	
	public function body($entries)
	{
		$b = '';
		foreach ($entries as $i => $row) $b .= $this->row($row,$i);
		return "<tbody>$b</tbody>";
	}
	
	public function row($row,$i=0)
	{
		$r = '';
		foreach ($row as $n => $c) {
			if (isset($this->columnFormats[$n]))
				$c = $this->formatColumn($c,
					$this->columnFormats[$n]);
			$r .= "<td>$c</td>";
		}
		$class = $this->rowClasses
			?  " class='".($this->rowClasses[$i%$this->rowClassesLen])."' "
			: "";
		return "<tr$class>$r</tr>";
	}
	
	public function footer($columns)
	{
		$f = '';
		foreach ($columns as $c) $f .= "<td></td>";
		return "<tfoot><tr>$f</tr></tfoot>";
	}
	
	public function pages($count=null)
	{
		if ($count === null) $count = $this->count();
		$pages = ceil($count/DBE_ENTRIES_PER_PAGE);
		
		if ($this->page < 1) $this->page = 1;
		else if ($this->page > $pages) $this->page = $pages;
		
		$p0 = 1;
		$pN = $pages;
		$l0 = $lPrev = $lNext = $lN = "";
		if (DBE_PAGE_LIMIT) {
			$p0 = floor(($this->page-1)/DBE_PAGE_LIMIT)*DBE_PAGE_LIMIT + 1;
			$pN = min($p0 + DBE_PAGE_LIMIT - 1,$pages);
			$l0	= $this->pageLink(1,"&lt;&lt;");
			$lPrev	= $this->pageLink($p0 - 1,"&lt;");
			$lNext	= $this->pageLink($pN + 1,"&gt;");
			$lN	= $this->pageLink($pages,"&gt;&gt;");
		}
		
		$p = $l0.$lPrev;
		for ($i = $p0; $i <= $pN; $i++)
			$p .= ($i == $this->page)
				? "<span class='activepage'>[$i]</span> "
				: $this->pageLink($i,$i);
		return $p.$lNext.$lN;
	}
	public function pageLink($i,$n=null)
	{
		if ($n === null) $n = $i;
		return "<a href='javascript:'".$this->gotoOnClick($i).">$n</a> ";
	}
	
	public function count() { return $this->database->count($this->table); }
	
	public function showing($count=null)
	{
		$p0 = ($this->page - 1) * DBE_ENTRIES_PER_PAGE + 1;
		$pN = $this->page * DBE_ENTRIES_PER_PAGE;
		if ($count !== null) {	$pN = min($count,$pN);
					$p0 = max(min($p0,$pN),0); }
		return "$p0 - $pN";
	}
	
	/*--------------------------------------------------------------------*\
	|* SORTING/ORDERING                                                   *|
	\*--------------------------------------------------------------------*/
	public function orderBy($columns)
	{
		//check validity of variables
		if (!isset($columns[$this->sortColumn]))
			$this->sortColumn = '';
		if (($this->order!==Database::A)&&($this->order!==Database::D))
			$this->order = DBE_SORT_ORDER;
		return ($this->sortColumn&&$this->order)
			? $this->sortColumn.' '.$this->order : '';
	}
	
	public function orderIndicator($column)
	{
		if ($column !== $this->sortColumn) return DBE_ORDER_NO_INDICATOR;
		if ($this->order == Database::A) return DBE_ORDER_ASC_INDICATOR;
		if ($this->order == Database::D) return DBE_ORDER_DESC_INDICATOR;
		return DBE_ORDER_NO_INDICATOR;
	}
	
	public function limit()
	{
		return (DBE_ENTRIES_PER_PAGE)
			? (max(($this->page - 1)*DBE_ENTRIES_PER_PAGE,0)).",".
				DBE_ENTRIES_PER_PAGE : '';
	}
	
	/*--------------------------------------------------------------------*\
	|* JAVASCRIPT                                                         *|
	\*--------------------------------------------------------------------*/
	public function javascript()
	{
		$id	= $this->id;
		$script	= _::URL();
		$js	= $id.' = {
		sortColumn:	"'.$this->sortColumn.'",
		sortOrder:	"'.$this->order.'",
		page:		"'.$this->page.'",
		
		sort: function(col)
		{
			if ('.$id.'.sortColumn === col) '.$id.'.toggleOrder();
			else '.$id.'.sortOrder = "'.$this->dOrder.'";
			'.$id.'.sortColumn = col;
			'.$id.'.update();
		},
		toggleOrder: function()
		{
			'.$id.'.sortOrder = ('.$id.'.sortOrder == "'.Database::A.'")
					? "'.Database::D.'"
					: "'.Database::A.'";
		},
		goto: function(pg)
		{
			'.$id.'.page = pg;
			'.$id.'.update();
		},
		update: function()
		{
			new Ajax.Updater("'.$this->divID.'","'.$script.'",
				{ method:"post",parameters:'.$this->params().'});
		}
		};';
		 return "<script type='text/javascript'>$js</script>";
	}
	
	public function params()
	{
		$vars = array(self::scID,self::soID,self::pgID);
		foreach ($vars as $i => $v) $vars[$i] = "'&$v='+$this->id.$v";
		return "'{$this->id}=$this->id'+".implode('+',$vars).
			($this->jsParams ? "+'&$this->jsParams'" : '');
	}
	
	public function sortOnClick($c)
	{
		return " onclick='$this->id.sort(\"$c\"); return false;' ";
	}
	
	public function gotoOnClick($i)
	{
		return " onclick='$this->id.goto($i); return false;' ";
	}
}
?>