<?php
/**
 * Http Server (Mode: non-blocking, SELECT)
 * @author    TX <txthinking@gmail.com>
 * @link    http://blog.txthinking.com
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html     GNU GPL v2
 * @version 0.0
 * @TODO Header, SSL
 */
class TX_Http_Server{
	/**
	 * http server ip or domain
	 */
	protected $server;
	/**
	 * http server port
	 */
	protected $port;
	/**
	 * www document root
	 */
	protected $documentRoot;
	/**
	 * default page
	 */
	protected $defaultPage;
	/**
	 * original socket
	 */
	protected $originalSocket;
	/**
	 * original socket array
	 */
	protected $originalSockets;
	/**
	 * request read socket array
	 */
	protected $requestReadSockets;
	/**
	 * request write socket array
	 */
	protected $requestWriteSockets;
	/**
	 * request data array
	 */
	protected $requestData;
	/**
	 * construct function
	 */
	public function __construct(){
		$this->originalSockets = array();
		$this->requestReadSockets = array();
		$this->requestWriteSockets = array();
		$this->requestData = array();
		$this->defaultPage = 'index.html';
	}
	/**
	 * set server and port
	 * @param unknown_type $server
	 * @param unknown_type $port
	 */
	public function setServer($server, $port){
		$this->server = $server;
		$this->port = $port;
	}
	/**
	 * set document root
	 * @param unknown_type $documentRoot
	 */
	public function setDocRoot($documentRoot){
		$this->documentRoot = $documentRoot;
	}
	/**
	 * set default page
	 * @param unknown_type $documentRoot
	 */
	public function setDefaultPage($defaultPage){
		$this->defaultPage = $defaultPage;
	}
	/**
	 * start run server
	 */
	public function run(){
		error_reporting(E_ALL ^ E_NOTICE);
		set_time_limit(0);
		$this->createOriginalSocket();
		$this->main();
	}
	/**
	 * create orginal socket
	 */
	protected function createOriginalSocket(){
		$this->originalSocket = stream_socket_server("tcp://$this->server:$this->port", $errno, $errstr);
		stream_set_blocking($this->originalSocket, 0);
		$this->originalSockets = array($this->originalSocket);
	}
	/**
	 * main method
	 */
	protected function main(){
		while (true){
			$readFds = array_merge($this->requestReadSockets, $this->originalSockets);
			$writeFds = $this->requestWriteSockets;
			$exceptFds = array();
			// handle socket
			if (stream_select($readFds, $writeFds, $exceptFds, 0)){
				/**
				 * loop read
				 */
				foreach ($readFds as $sock){
					$sockId = (int)$sock;
					if (in_array($sock, $this->originalSockets)){
						/**
						 * if read socket in the $originalSockets
						 */
						$requestSocket = stream_socket_accept($sock, 5);
						stream_set_blocking($requestSocket, 0);
						$id = (int)$requestSocket;
						$this->requestReadSockets[$id] = $requestSocket;
						$this->requestWriteSockets[$id] = $requestSocket;
					}elseif (in_array($sock, $this->requestReadSockets)){
						/**
						 * if read socket in the $requestReadSockets
						 */
						$line = fgets($sock);
						print $line;
						//if POST method then get post data
						if ($this->requestData[$sockId]['isHeaderEnd'] && $this->requestData[$sockId]['header']['content-length']){
							$this->requestData[$sockId]['messageBody'] = $this->requestData[$sockId]['messageBody']==null ? $line : $this->requestData[$sockId]['messageBody'].$line;
							if(strlen($this->requestData[$sockId]['messageBody']) >= $this->requestData[$sockId]['header']['content-length']){
								//remove read socket
								stream_socket_shutdown($sock, STREAM_SHUT_RD);
								unset($this->requestReadSockets[$sockId]);
								//if this is true then end write to this socket
								$this->requestData[$sockId]['isReadEnd'] = true;
							}
						}
						//if \r\n
						if (!$this->requestData[$sockId]['isHeaderEnd'] && $line == "\r\n"){
//							$line = "Connection: close\r\n\r\n";
							$this->requestData[$sockId]['isHeaderEnd'] = true;
							if (!$this->requestData[$sockId]['header']['content-length']){
								//remove read socket
								stream_socket_shutdown($sock, STREAM_SHUT_RD);
								unset($this->requestReadSockets[$sockId]);
								//if this is true then end write to this socket
								$this->requestData[$sockId]['isReadEnd'] = true;
							}
						}
						//get header
						if ($this->requestData[$sockId]['isRequestLineEnd'] && !$this->requestData[$sockId]['isHeaderEnd']){
							$lineArrayTemp = explode(":", $line, 2);
		    				$this->requestData[$sockId]['header'][strtolower($lineArrayTemp[0])] = trim(($lineArrayTemp[1]));
						}
						// read $request line
						if (!$this->requestData[$sockId]['isRequestLineEnd']){
				    		$this->requestData[$sockId]["requestLine"] = $line;
				    		$this->requestData[$sockId]['isRequestLineEnd'] = true;
				    	}
					}
				} //end foreach
				/**
				 * loop write
				 */
				foreach ($writeFds as $sock){
					$sockId = (int)$sock;
					if ($this->requestData[$sockId]['isReadEnd']){
						//StatusLine
						$responseStatusLine = "HTTP/1.1 200 OK\r\n";
						//get request path
						$requestLineArray = explode(" ", $this->requestData[$sockId]['requestLine']);
						$url = parse_url($requestLineArray[1]);
						if ($url['path'] == "/"){
							$filePath = rtrim($this->documentRoot, "/")."/$this->defaultPage";
						}else {
							$filePath = rtrim($this->documentRoot, "/").$url['path'];
						}
						print $urlPath;
						$fileData = @file_get_contents($filePath);
						//Header
						$responseHeader['date'] = date('r');
						$responseHeader['expires'] = "-1";
						$responseHeader['cache-control'] = "private, max-age=0";
						$responseHeader['content-length'] = strlen($fileData);
						$responseHeader['content-type'] = "text/html; charset=UTF-8";
						$responseHeader['server'] = "TXWS";
						$responseHeader['connection'] = "close";
						//MessageBody
						$responseMessageBody = $fileData;
						
						//return data
					    $response = "";
					    $response .= $responseStatusLine;
					    foreach ($responseHeader as $k=>$v){
					    	$response .= "$k: $v\r\n";
					    }
					    $response .= "\r\n";
					    $response .= $responseMessageBody;
						fwrite($sock, $response, strlen($response));
						
						//remove write socket
						stream_socket_shutdown($sock, STREAM_SHUT_WR);
						unset($this->requestWriteSockets[$sockId]);
					}
				} //end foreach
			} //end if stream_select
		} // end while
	} //end method start
}


