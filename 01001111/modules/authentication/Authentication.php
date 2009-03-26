<?php
/**	Authentication		- extendable multi-mode authentication
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
 //	TODO: Malicious IP ban list
/*----------------------------------------------------------------------------*\
|* CONSTANTS                                                                  *|
\*----------------------------------------------------------------------------*/
def('AUTHENTICATION_AUTORUN',		false);
def('OBFUSCATE_PASSWORD',		true);
def('ENCODE_DATA',			true);
def('OBFUSCATE_INPUTS',			true);
def('FAILED_LOGIN_LIMIT',		5);
def('MALICIOUS_INTENT_LIMIT',		10);
def('INACTIVE_SESSION_EXPIRATION',	60*5);

/*-AUTO RUN?------------------------------------------------------------------*/
if (AUTHENTICATION_AUTORUN) Authentication::auto()->run();
/*----------------------------------------------------------------------------*/

/*----------------------------------------------------------------------------*\
|* GENERIC AUTHENTICATION CLASS                                               *|
\*----------------------------------------------------------------------------*/
abstract class Authentication
{
	/*--------------------------------------------------------------------*\
	|* CONSTANTS                                                          *|
	\*--------------------------------------------------------------------*/
	/* log file */
	const fLOG		= 'authentication';
	/* default keys/columns */
	const cUSERNAME		= 'username';
	const cPASSWORD		= 'password';
	const cLEVEL		= 'level';
	const cCREATED		= 'created';
	const cMODIFIED		= 'modified';
	const cNEWPASSWORD	= 'newpassword';
	const cACTIVE		= 'active';
	
	/* timestamp */
	const dateFORMAT	= 'Y-m-d H:i:s';
	
	/* defaults */
	const ADMIN_LEVEL	= 2;
	const dACTIVE		= 1;
	
	/*--------------------------------------------------------------------*\
	|* VARIABLES                                                          *|
	\*--------------------------------------------------------------------*/
	public $levels	= array('admin'	=> 2,
				'user'	=> 1,
				'guest'	=> 0);
	
	/*--------------------------------------------------------------------*\
	|* CONSTRUCTOR(S)                                                     *|
	\*--------------------------------------------------------------------*/
	protected function __construct() { }
	public static function auto()
	{
		$mode = _::C('AUTHENTICATION_MODE','FileAuthentication');
		return new $mode();
	}
	
	/*--------------------------------------------------------------------*\
	|* MAIN/RUN                                                           *|
	\*--------------------------------------------------------------------*/
	public function run($requireAuthentication=false,$requireLevel=0)
	{
		/* initialize and check session */
		$this->initializeSession();
		$this->checkSession();
		/* check access permisions */
		$this->authorize($requireAuthentication,$requireLevel);
		/* check for action requests */
		$f = _::REQUEST(__CLASS__);
		$r = $this->request($this->attributes());
		if (!($f&&is_callable(array($this,$f)))) return;
		$c = call_user_func_array(array($this,$f),array($r));
		return _::AJAX() ? die($c) : $c;
	}
	
	/*--------------------------------------------------------------------*\
	|* AUTHORIZE                                                          *|
	\*--------------------------------------------------------------------*/
	public function authorize($requireAuthentication=false,
		$requireLevel=0)
	{
		if (!$this->authorized($requireAuthentication,$requireLevel)) {
			$this->log('Unauthorized Access Attempt');
			$_SESSION['malicious_intent'] =
				_::SESSION('malicious_intent')+1;
			HTTP::boot();
		}
		return true;
	}
	public function authorized($requireAuthentication=false,
		$requireLevel=0)
	{
		return  !(($requireAuthentication&&!self::authenticated()) ||
			($requireLevel > $this->userlevel()));
	}
	public static function authenticated()
	{
		return _::SESSION('authenticated',false);
	}
	public static function user()
	{
		return _::SESSION('user',array());
	}
	public function username()
	{
		return _::A(self::user(),$this->c('cUSERNAME'),
			_::SERVER('REMOTE_ADDR'));
	}
	public function userlevel()
	{
		return _::A(self::user(),$this->c('cLEVEL'),0);
	}
	public function isadmin()
	{
		return $this->authorized(true,$this->c('ADMIN_LEVEL'));
	}
	
	public function levelName($n) { return _::K($this->levels,$n+0); }
	
	/*--------------------------------------------------------------------*\
	|* SESSION                                                            *|
	\*--------------------------------------------------------------------*/
	protected function initializeSession()
	{
		/* initialize session variables if they don't already exist */
		_::SESSION('authenticated',	false,			true);
		_::SESSION('created',		microtime(true),	true);
		_::SESSION('ip',		_::SERVER('REMOTE_ADDR'),true);
		_::SESSION('browser',		_::SERVER('HTTP_USER_AGENT'),true);
		_::SESSION('user',		'',			true);
		_::SESSION('key',		$this->sessionKey(),	true);
		_::SESSION('failed_logins',	0,			true);
		_::SESSION('malicious_intent',	0,			true);
		_::SESSION('malicious',		false,			true);
		_::SESSION('expire',		0,			true);
	}
	public /*protected*/ function updateSession($authenticated,$user)
	{
		$_SESSION['authenticated'] = $authenticated;
		if ($authenticated) {
			$_SESSION['user'] = $user;
			$_SESSION['failed_logins'] = 0;
			$_SESSION['malicious_intent'] = 0;
			$_SESSION['expire'] = time() + INACTIVE_SESSION_EXPIRATION;
		}
		else {	$_SESSION['user'] = '';
			$_SESSION['failed_logins'] = _::SESSION('failed_logins')+1;
		}
	}
	protected function checkSession()
	{
		/*-AUTHENTICATED----------------------------------------------*/
		if (_::SESSION('authenticated')) {
			/* key mismatch */
			if ((_::SESSION('key') != $this->sessionKey()) ||
			    (Cookie::get('key') != $this->sessionKey()))
				return $this->destroySession( "INVALID KEY");
			/* expired */
			elseif (_::SESSION('expire') < time())
				return $this->destroySession("SESSION EXPIRED");
			else $_SESSION['expire'] = time() +
				INACTIVE_SESSION_EXPIRATION;
		}
		/*-UNAUTHENTICATED--------------------------------------------*/
		else {	if (_::SESSION('malicious')) {
				$this->log('MALICIOUS');
				HTTP::deceive();
			}
			if ((_::SESSION('failed_logins') >= FAILED_LOGIN_LIMIT) ||
			    (_::SESSION('malicious_intent') >= MALICIOUS_INTENT_LIMIT)) {
				$_SESSION['malicious'] = true;
				HTTP::boot();
			}
		}
	}
	public /*protected*/ function destroySession($msg='',$boot=true)
	{
		session_unset();
		session_destroy();
		if ($msg) $this->log($msg);
		if ($boot) HTTP::boot();
		return false;
	}
	protected function sessionKey($setCookie=true)
	{
		$ip = _::SESSION('ip',_::SERVER('REMOTE_ADDR'));
		$t = _::SESSION('created',microtime(true));
		$b = _::SESSION('browser',_::SERVER('HTTP_USER_AGENT'));
		$key =  sha1($ip.$t.$b);
		if ($setCookie) Cookie::set('key',$key,0);
		return $key;
	}
	
	/*--------------------------------------------------------------------*\
	|* GENERIC AUTHENTICATION                                             *|
	\*--------------------------------------------------------------------*/
	public /*protected*/ function authenticate($ua,$obfuscated=true)
	{
		$cu = $this->c('cUSERNAME');
		$cp = $this->c('cPASSWORD');
		$authenticated = false;
		if ($this->validate($ua,array($cu,$cp))) {
			$ua = $this->verify($ua,$obfuscated);
			$authenticated = ($ua !== false);
		}
		$this->updateSession($authenticated,$ua);
		$this->logAction($authenticated,__FUNCTION__,$ua);
		return $authenticated;
	}
	protected function verify($ua,$obfuscated=true)
	{
		$cu = $this->c('cUSERNAME');
		$cp = $this->c('cPASSWORD');
		list($u,$p) = _::Aa($ua,array($cu,$cp));
		$user = $this->getUser($u);
		$verified = $this->checkPassword($p,_::A($user,$cp),
			$this->sessionKey(false),$obfuscated);
		unset($user[$cp]);
		return ($verified) ? $user : false;
	}
	public function deauthenticate($boot=true)
	{
		$this->destroySession('deauthenticating',$boot=true);
	}
	
	/*--------------------------------------------------------------------*\
	|* USER MANAGEMENT                                                    *|
	\*--------------------------------------------------------------------*/
	public function userExists($u)
	{
		if (is_array($u)) $u = _::A($u,$this->c('cUSERNAME'));
		return $this->getUser($u) != false;
	}
	
	public /*protected*/ function addUser($ua)
	{
		$u = _::A($ua,$this->c('cUSERNAME'));
		$ua[$this->c('cPASSWORD')] =
			_::A($ua,$this->c('cNEWPASSWORD'),
				_::A($ua,$this->c('cPASSWORD')));
		return $this->logAction((
			$this->authorize(true,$this->c('ADMIN_LEVEL'))&&
			($ua = $this->validate($ua,
				array(	$this->c('cUSERNAME'),
					$this->c('cPASSWORD'))))&&
			!$this->userExists($u)
				? $this->_addUser($ua)
				: false
			),__FUNCTION__,$ua);
	}
	public /*protected*/ function removeUser($ua)
	{
		return $this->logAction((
			$this->authorize(true,$this->c('ADMIN_LEVEL'))&&
			($ua = $this->validate($ua,
				$this->c('cUSERNAME')))&&
			$this->userExists($ua)
				? $this->_removeUser($ua)
				: false
			),__FUNCTION__,$ua);
	}
	public /*protected*/ function changePassword($ua)
	{
		return $this->logAction((
			$this->authorize(true,$this->c('ADMIN_LEVEL'))&&
			($ua = $this->validate($ua,
				array(	$this->c('cUSERNAME'),
					$this->c('cPASSWORD'),
					$this->c('cNEWPASSWORD'))))&&
			$this->userExists($ua)&&
			$this->verify($ua)
				? $this->_changePassword($ua)
				: false
			),__FUNCTION__,$ua);
	}
	public /*protected*/ function updateUser($ua)
	{
		$fields = array_keys($ua);
		return $this->logAction((
			$this->authorize(true,$this->c('ADMIN_LEVEL'))&&
			isset($ua[$this->c('cUSERNAME')])&&
			($ua = $this->validate($ua,$fields))&&
			$this->userExists($ua)
				? $this->_updateUser($ua)
				: false
			),__FUNCTION__,$ua);
	}
	
	/*--------------------------------------------------------------------*\
	|* ABSTRACT USER MANAGEMENT                                           *|
	\*--------------------------------------------------------------------*/
	abstract protected function getUser($u);
	abstract public function getUsers();
	
	/** _addUser/_removeUser/_changePassword
	 *	Since these functions are essential to any authentication
	 *	scheme and most will have similar requirements, the specific
	 *	processing will occur in these functions while the common
	 *	functionality can be taken care of by the above methods in
	 *	this abstract class.  Simply override the above methods if they
	 *	do not suffice.
	 */
	abstract protected function _addUser($ua);
	abstract protected function _removeUser($ua);
	abstract protected function _changePassword($ua);
	abstract protected function _updateUser($ua);
	
	/*--------------------------------------------------------------------*\
	|* USER ATTRIBUTES                                                    *|
	\*--------------------------------------------------------------------*/
	protected function attributes($ua=false)
	{
		$u = $this->c('cUSERNAME');
		$p = $this->c('cPASSWORD');
		$l = $this->c('cLEVEL');
		$a = $this->c('cACTIVE');
		$n = $this->c('cNEWPASSWORD');
		return array(
			$u	=> $this->attribute(Validate::SAFE,
				XHTML::text($this->obfuscateInput($u),
					_::A($ua,$u),
					array('requires'=>'[Validate.SAFE]'))
				),
			$p	=> $this->attribute(Validate::SAFE,
				XHTML::password($this->obfuscateInput($p),
					_::A($ua,$p),
					array('requires'=>'[Validate.SAFE]'))
				),
			$l	=> $this->attribute(Validate::NUMBER,
				XHTML::select($this->obfuscateInput($l),
					XHTML::options($this->levels,true,
						_::A($ua,$l,0)))
				),
			$a	=> $this->attribute(Validate::NUMBER,
				XHTML::dcheckbox($this->obfuscateInput($a),
					1,0,'',true)
				),
			$n	=> $this->attribute(Validate::SAFE,
				XHTML::password($this->obfuscateInput($n),
					_::A($ua,$n),
					array('requires'=>'[Validate.SAFE]'))
				),
		);
	}
	protected function attribute($validator,$input='')
	{
		return array('validator' => $validator,'input' => $input);
	}
	
	/*--------------------------------------------------------------------*\
	|* VALIDATE / SANITIZE / ENCRYPT / OBFUSCATE                          *|
	\*--------------------------------------------------------------------*/
	protected function validate($userArray,$require=array())
	{
		$user = array();
		$attributes = $this->attributes();
		if (!is_array($require)) $require = array($require);
		foreach ($require as $r)
			if (!isset($userArray[$r])) return false;
		foreach ($userArray as $k => $v) {
			if (!isset($attributes[$k])) continue;
			if (!Validate::v($v,$attributes[$k]['validator'])) {
				$this->log("INVALID $k : $v");
				return false;
			}
			$user[$k] = $v;
		}
		return $user;
	}
	public function checkPassword($clientP,$serverP,$k=null,$obfuscated=true)
	{
		if ($k == null) $k = $this->sessionKey(false);
		return $obfuscated
			? ($this->obfuscatePassword($serverP,$k) === $clientP)
			: ($serverP === $clientP);
	}
	public function obfuscatePassword($p,$k=null)
	{
		if ($k == null) $k = $this->sessionKey(false);
		return (OBFUSCATE_PASSWORD) ? sha1($p.$k) : $p;
	}
	public function obfuscateInput($n,$k=null)
	{
		if ($k == null) $k = $this->sessionKey(false);
		return (OBFUSCATE_INPUTS) ? sha1($n.$k) : $n;
	}
	public function obfuscateInputs($a,$k=null)
	{
		$o = array();
		foreach ($a as $i) $o[$i] = $this->obfuscateInput($i,$k);
		return $o;
	}
	public static function encryptPassword($p) { return sha1($p); }
	public static function encodeData($d)
	{
		return ENCODE_DATA ? base64_encode($d) : $d;
	}
	public static function decodeData($d)
	{
		if ($d === null) return null;
		return ENCODE_DATA ? base64_decode($d) : $d;
	}
	
	/*--------------------------------------------------------------------*\
	|* REQUEST PARAMETERS                                                 *|
	\*--------------------------------------------------------------------*/
	public function request($vars=array())
	{
		if (!is_array($vars)) return array();
		$r = _::cleanREQUEST();
		$values = array();
		foreach ($vars as $k => $v) {
			$rv = $this->decodeData(
				_::A($r,$this->obfuscateInput($k),
				_::A($r,$k,null)));
			if ($rv !== null) $values[$k] = $rv;
		}
		return $values;
	}
	public function userArray()
	{
		$vars = func_get_args();
		$attrs = array_keys($this->attributes());
		$userArray = array();
		foreach ($vars as $i => $v)
			if (isset($attrs[$i])) $userArray[$attrs[$i]] = $v;
		return $userArray;
	}
	
	/*--------------------------------------------------------------------*\
	|* CONSTANT ACCESSOR                                                  *|
	\*--------------------------------------------------------------------*/
	protected function c/*onstant*/($c)
	{
		return @constant(get_class($this)."::$c");
	}
	
	/*--------------------------------------------------------------------*\
	|* LOG                                                                *|
	\*--------------------------------------------------------------------*/
	protected function log($msg='')
	{
		$msg .=	"\n\tIP:\t"._::SERVER('REMOTE_ADDR').
			"\n\tPAGE:\t"._::SERVER('SCRIPT_NAME').
			"\n\tSESSION:\t"._::export($_SESSION).
			"\n\tCOOKIE:\t"._::export($_COOKIE);
		Logger::log(self::fLOG,$msg);
	}
	protected function logAction($success=false,$action='',$ua=array())
	{
		$this->log("$action ".($success ? "SUCCEEDED" : "FAILED")." : ".
			_::export($ua));
		return $success;
	}
	
	/*--------------------------------------------------------------------*\
	|* FORM INPUTS                                                        *|
	\*--------------------------------------------------------------------*/
	public function inputs($ua=array())
	{
		$attributes = $this->attributes($ua);
		$inputs = array();
		foreach ($attributes as $k => $v) $inputs[$k] = $v['input'];
		return $inputs;
	}
	
	/*--------------------------------------------------------------------*\
	|* JAVASCRIPT                                                         *|
	\*--------------------------------------------------------------------*/
	/*<script type="text/javascript" src="?Authentication=js"></script>*/
	protected function js()
	{
		$p = $this->obfuscateInput($this->c('cPASSWORD'));
		HTTP::javascript();
		?>
/*<script type="text/javascript">*/
Authentication = {
	request: function(action,form,callbackOrElement,validateCallback)
	{
		if (validateCallback !== undefined) {
			if (!Validate.run(validateCallback,Form.getInputs(form)))
				return false;
		}
		var data = Authentication.obfuscate(Form.data(form));
		Authentication.ajax("Authentication="+action+"&"+
			$H(data).toQueryString(),callbackOrElement);
		return false;
	},
	/*-REQUEST------------------------------------------------------------*/
	r: null,
	ajax: function(args,callbackOrElement)
	{
		var url = "<?php echo _::URL(); ?>";
		var method = "POST";
		try { if (Authentication.r) Authentication.r.abort(); }
		catch(e){}
		if (isFunction(callbackOrElement))
			Authentication.r = new Ajax.Request(url,{
					method:method,
					parameters:args,
					onSuccess:callbackOrElement});
		else if ($(callbackOrElement))
			Authentication.r = new Ajax.Updater(callbackOrElement,
				url,{	method:method,
					evalScripts:true,
					parameters:args});
		else	Authentication.r = new Ajax.Request(url,{
					method:method,
					parameters:args});
	},
	/*-ENCRYPTION/OBFUSCATION---------------------------------------------*/
	key: "<?php echo $this->sessionKey(false) ?>",
	obfuscate: function(data)
	{
		if (data === undefined) return {};
		for (k in data) {
			if (k == "<?php echo $p ?>")
				data[k] = Authentication.encrypt(data[k]);
			data[k] = Authentication.encode(data[k]);
		}
		return data;
	},
	encrypt: function(p)
	{
		p = hex_sha1(p);
		return <?php echo (OBFUSCATE_PASSWORD
			? "hex_sha1(p+Authentication.key)" : "p") ?>;
	},
	encode: function(d)
	{
		return <?php echo (ENCODE_DATA ? "Base64.encode(d)" : "d") ?>;
	},
	/*-ACTIONS------------------------------------------------------------*/
	authenticate: function(f,cOe,v)
	{
		return Authentication.request("authenticate",f,cOe,v);
	},
	add: function(f,cOe,v)
	{
		return Authentication.request("addUser",f,cOe,v);
	},
	remove: function(f,cOe,v)
	{
		return Authentication.request("removeUser",f,cOe,v);
	},
	update: function(f,cOe,v)
	{
		return Authentication.request("updateUser",f,cOe,v);
	},
	changePassword: function(f,cOe,v)
	{
		return Authentication.request("changePassword",f,cOe,v);
	},
	deauthenticate: function(cOe)
	{
		return Authentication.request("deauthenticate",null,cOe);
	}
};
/*</script>*/
		<?php die();
	}
}
?>