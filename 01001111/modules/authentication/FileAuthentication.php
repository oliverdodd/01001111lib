<?php
/**	FileAuthentication	- php file based authentication
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
class FileAuthentication extends Authentication
{
	/*--------------------------------------------------------------------*\
	|* CONSTANTS /  VARIABLES                                             *|
	\*--------------------------------------------------------------------*/
	const fPATH		= 'config/users0.php';
	public $f;
	
	/*--------------------------------------------------------------------*\
	|* CONSTRUCTOR                                                        *|
	\*--------------------------------------------------------------------*/
	public function __construct($f=self::fPATH) { $this->f = $f; }
	
	/*--------------------------------------------------------------------*\
	|* IO                                                                 *|
	\*--------------------------------------------------------------------*/
	protected function loadUsers()
	{
		@include $this->f;
		return isset($users) ? $users : array();
	}
	protected function saveUsers($users)
	{
		return Filesystem::save($this->f,
			'<?php $users = '._::export($users).'; ?>');
	}
	/*--------------------------------------------------------------------*\
	|* USER MANAGEMENT                                                    *|
	\*--------------------------------------------------------------------*/
	protected function getUser($u)
	{
		return _::A($this->loadUsers(),$u,array());
	}
	public function getUsers()
	{
		return ($this->authenticated()) ? $this->loadUsers() : array();
	}
	
	protected function _addUser($ua)
	{
		$users = $this->loadUsers();
		$users[$ua[self::cUSERNAME]] = array(
			self::cUSERNAME	=> $ua[self::cUSERNAME],
			self::cPASSWORD	=> $this->encryptPassword(
						$ua[self::cPASSWORD]),
			self::cLEVEL	=> _::A($ua,self::cLEVEL)+0,
			self::cACTIVE	=> _::A($ua,self::cACTIVE,
						$this->c('dACTIVE')),
			self::cCREATED	=> date(self::dateFORMAT),
			self::cMODIFIED	=> date(self::dateFORMAT),
		);
		return $this->saveUsers($users);
	}
	protected function _removeUser($ua)
	{
		$users = $this->loadUsers();
		unset($users[$ua[self::cUSERNAME]]);
		return $this->saveUsers($users);
	}
	protected function _changePassword($ua)
	{
		$users = $this->loadUsers();
		$users[$ua[self::cUSERNAME]][self::cPASSWORD] =
			$this->encryptPassword($ua[self::cNEWPASSWORD]);
		$users[$ua[self::cUSERNAME]][self::cMODIFIED] =
			date(self::dateFORMAT);
		return $this->saveUsers($users);
	}
	protected function _updateUser($ua)
	{
		$users = $this->loadUsers();
		$u = $ua[self::cUSERNAME];
		foreach ($ua as $k => $v)
			$users[$u][$k] = ($k === self::cPASSWORD)
				? $this->encryptPassword($v)
				: $v;
		$users[$u][self::cMODIFIED] = date(self::dateFORMAT);
		return $this->saveUsers($users);
	}
}
?>