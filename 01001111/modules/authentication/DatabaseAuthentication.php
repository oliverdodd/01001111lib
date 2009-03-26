<?php
/**	DatabaseAuthentication	- database based authentication
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
class DatabaseAuthentication extends Authentication
{
	/*--------------------------------------------------------------------*\
	|* CONSTANTS / VARIABLES                                              *|
	\*--------------------------------------------------------------------*/
	const dTABLE		= 'users';
	protected $db;
	protected $table;
	
	/*--------------------------------------------------------------------*\
	|* CONSTRUCTOR                                                        *|
	\*--------------------------------------------------------------------*/
	public function __construct($db=null,$table=self::dTABLE)
	{
		$this->db = ($db === null) ? new Database() : $db;
		$this->table = $table;
		$this->create();
	}
	
	/*--------------------------------------------------------------------*\
	|* DATABASE                                                           *|
	\*--------------------------------------------------------------------*/
	protected function create()
	{
		$this->db->set(
			'CREATE TABLE IF NOT EXISTS '.$this->table.' (
				'.self::cUSERNAME.' VARCHAR(32) NOT NULL,
				'.self::cPASSWORD.' VARCHAR(256) NOT NULL,
				'.self::cLEVEL.' INT DEFAULT 0,
				'.self::cACTIVE.' TINYINT DEFAULT 0,
				'.self::cCREATED.' TIMESTAMP DEFAULT 0,
				'.self::cMODIFIED.' TIMESTAMP
					DEFAULT CURRENT_TIMESTAMP 
					ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY ('.self::cUSERNAME.'));');
	}
	
	/*--------------------------------------------------------------------*\
	|* USER MANAGEMENT                                                    *|
	\*--------------------------------------------------------------------*/
	protected function getUser($u)
	{
		return $this->db->selectOne($this->table,'*',
			self::cUSERNAME."='$u'");
	}
	public function getUsers()
	{
		return ($this->authenticated())
			? $this->db->select($this->table)
			: array();
	}
	protected function _addUser($ua)
	{
		$this->create();
		$t = date(self::dateFORMAT);
		$u = $ua[self::cUSERNAME];
		$p = $this->encryptPassword($ua[self::cPASSWORD]);
		$l = _::A($ua,self::cLEVEL)+0;
		$a = _::A($ua,self::cACTIVE,$this->c('dACTIVE'));
		return $this->db->insert($this->table,"'$u','$p',$l,$a,'$t'",
			self::cUSERNAME.','.self::cPASSWORD.','.
			self::cLEVEL.','.self::cACTIVE.','.self::cCREATED);
	}
	protected function _removeUser($ua)
	{
		return $this->db->delete($this->table,
			self::cUSERNAME."='{$ua[self::cUSERNAME]}'");
	}
	protected function _changePassword($ua)
	{
		$u = $ua[self::cUSERNAME];
		$p = $this->encryptPassword($ua[self::cNEWPASSWORD]);
		return $this->db->update($this->table,self::cPASSWORD."='$p'",
			self::cUSERNAME."='$u'");
	}
	protected function _updateUser($ua)
	{
		$u = $ua[self::cUSERNAME];
		$set = array();
		foreach ($ua as $k => $v)
			$set[$k] = "$k='".(($k === self::cPASSWORD)
				? $this->encryptPassword($v)
				: $v)."'";
		return $this->db->update($this->table,implode(',',$set),
			self::cUSERNAME."='$u'");
	}
}
?>