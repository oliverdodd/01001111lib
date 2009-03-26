<?php
/*	Socket Class - encapsulates common socket procedures
 *	ClientSocket Class - client specific socket procedures
 *	ServerSocket Class - server specific (not yet implemented)
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
 *
 */
/** SOCKET PARAMETER CONSTANTS
 *  define these before including the class to bypass sending parameters into
 *  the constructor
 */
define('SOCKET_PROTOCOL_ICMP',	getprotobyname('icmp'));
define('SOCKET_PROTOCOL_TCP',	getprotobyname('tcp'));
define('SOCKET_PROTOCOL_UDP',	getprotobyname('udp'));
if (!defined('SOCKET_CONNECT_TIMEOUT'))	define('SOCKET_CONNECT_TIMEOUT', 30);
if (!defined('SOCKET_EXECUTE_ATTEMPTS'))define('SOCKET_EXECUTE_ATTEMPTS', 3);
if (!defined('SOCKET_EXECUTE_WAIT'))define('SOCKET_EXECUTE_WAIT', 100);
if (!defined('SOCKET_DEBUG'))		define('SOCKET_DEBUG', false);
if (!defined('SOCKET_DEBUG_NL'))	define('SOCKET_DEBUG_NL', "\n");
if (!defined('SOCKET_DEBUG_DATE'))	define('SOCKET_DEBUG_DATE', "Y-m-d H:i:s");
class Socket
{
	/*--------------------------------------------------------------------*\
	|* CONSTANTS                                                          *|
	\*--------------------------------------------------------------------*/
	const ICMP		= SOCKET_PROTOCOL_ICMP;
	const TCP		= SOCKET_PROTOCOL_TCP;
	const UDP		= SOCKET_PROTOCOL_UDP;
	
	/*--------------------------------------------------------------------*\
	|* VARIABLES                                                          *|
	\*--------------------------------------------------------------------*/
	public $server;		//server address
	public $port;		//server port
	protected $file;	//use file based functions (fsockopen,fread,...)
	
	protected $socket;	//socket resource
	protected $connected;	//is the socket connected?
	
	//file mode variables
	public $errno;		//error number from system-level connect()
	public $errstr;		//the error message as a string
	
	/*--------------------------------------------------------------------*\
	|* CONSTRUCTOR/DESTRUCTOR                                             *|
	\*--------------------------------------------------------------------*/
	public function __construct($server,$port,$file=false)
	{
		$this->server	= $server;
		$this->port	= $port;
		$this->file	= $file;
	}
	public function __destruct() { $this->close();	}
	
	/*--------------------------------------------------------------------*\
	|* CREATE/CONNECT                                                     *|
	\*--------------------------------------------------------------------*/
	/** create() - create a new socket, parameters not used in file mode
	 *  @param $domain	- protocol family:
	 *  				AF_INET	- IPv4
	 *  				AF_INET6- IPv6
	 *  				AF_UNIX	- Interprocess
	 *  @param $type	- socket types:
	 *  				SOCK_STREAM
	 *  				SOCK_DGRAM
	 *  				SOCK_SEQPACKET
	 *  				SOCK_RAW
	 *  				SOCK_RDM
	 *  @param $domain	- protocol:
	 *				icmp	- InternetControlMessageProtocol
	 *  				udp	- User Datagram Protocol
	 *  				tcp	- Transmission Control Protocol
	 */
	public function create($domain=AF_INET,$type=SOCK_STREAM,$protocol=self::TCP)
	{
		$this->close();
		$this->socket = ($this->file)
			? null
			: socket_create($domain,$type,$protocol);
		self::debug(__FUNCTION__,'');
		return $this->socket;
	}
	
	/** connect() - connect to the socket
	 */
	public function connect()
	{
		self::debug(__FUNCTION__,"$this->server,$this->port");
		if ($this->file)
			$this->socket = fsockopen($this->server,$this->port,
						  $this->errno,$this->errstr,
						  SOCKET_CONNECT_TIMEOUT);
		else	$this->connected = socket_connect($this->socket,
						  $this->server,$this->port);
		return $this->connected();
	}
	
	/** connected() - is the socket connected?
	 */
	public function connected()
	{
		if ($this->file)
			$this->connected = (($this->socket) ? true : false);
		$c = ($this->socket&&$this->connected);
		self::debug(__FUNCTION__,var_export($c,true));
		return $c;
	}
	
	/** close() - close the socket
	 */
	public function close()
	{
		self::debug(__FUNCTION__,"closing socket $this->socket");
		if ($this->socket) {
			if ($this->file)	fclose($this->socket);
			else			socket_close($this->socket);
		}
		$this->connected = false;
		$this->socket = null;
	}
	
	/*--------------------------------------------------------------------*\
	|* ERRORS                                                             *|
	\*--------------------------------------------------------------------*/
	/** error() - last error as code or string
	 *  @param $asString	=return as string? defaults to true
	 */
	public function error($asString=true)
	{
		return ($asString)
			? (($this->file) ? $this->errstr : socket_strerror())
			: (($this->file) ? $this->errno : socket_last_error());
	}
	
	/*--------------------------------------------------------------------*\
	|* DEBUG                                                              *|
	\*--------------------------------------------------------------------*/
	public function debug($f,$m)
	{
		if(!SOCKET_DEBUG) return;
		echo date(SOCKET_DEBUG_DATE)." $f : $m".SOCKET_DEBUG_NL;
	}
}
class ClientSocket extends Socket
{
	/*--------------------------------------------------------------------*\
	|* SEND                                                               *|
	\*--------------------------------------------------------------------*/
	public function send($s)
	{
		$length	= strlen($s);
		if ($this->file) {
			fflush($this->socket);
			$sent = fwrite($this->socket,$s);
			fflush($this->socket);
		}
		else $sent = socket_send($this->socket,$s,$length,0x100);
		self::debug(__FUNCTION__,"sent $sent bytes");
		return ($sent === $length);
	}
	/*--------------------------------------------------------------------*\
	|* READ                                                               *|
	\*--------------------------------------------------------------------*/
	public function read($end='')
	{
		$s = '';
		if ($this->file)
			while(	(($c = fread($this->socket,1)) !== '')&&
				 ($c !== $end)) $s .= $c;
		else	while(	(($c = socket_read($this->socket,1)) !== '')&&
				 ($c !== $end)) $s .= $c;
		self::debug(__FUNCTION__,"read $s");
		return $s;
	}
	/*--------------------------------------------------------------------*\
	|* EXECUTE                                                            *|
	\*--------------------------------------------------------------------*/
	/** execute() - read write combo with multiple tries on error */
	public function execute($cmd,$end='')
	{
		$s = false;
		for ($i = SOCKET_EXECUTE_ATTEMPTS; $i > 0; $i--) {
			$this->send($cmd);
			$s = $this->read($end);
			if ($s != '') break;
			usleep(SOCKET_EXECUTE_WAIT);
		}
		return $s;
	}
}
?>