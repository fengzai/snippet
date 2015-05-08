<?php
/**
 *
 * @author    TX <txthinking@gmail.com>
 * @link    http://blog.txthinking.com
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html     GNU GPL v2
 * @version 0.8
 * @TODO Header, SSL
 */
class TX_Http_Proxy{
	/**
	 * listen ip
	 */
	protected $ip = "127.0.0.1";
	/**
	 * listen port
	 */
	protected $port;
	/**
	 * original Socket
	 */
	protected $originalSocket;
	/**
	 * original Socket array
	 */
	protected $originalSockets = array();
	/**
	 * read local sockets pool
	 */
	protected $localReadSockets = array();
	/**
	 * write local sockets pool
	 */
	protected $localWriteSockets = array();
	/**
	 * read remote sockets pool
	 */
	protected $remoteReadSockets = array();
	/**
	 * write remote sockets pool
	 */
	protected $remoteWriteSockets = array();
	/**
	 * local remote socket maps
	 */
	protected $socketIdMaps = array();
	/**
	 * read FDs
	 */
	protected $readFds = array();
	/**
	 * write FDs
	 */
	protected $writeFds = array();
	/**
	 * $except FDs
	 */
	protected $exceptFds = array();
	/**
	 * local data array
	 */
	protected $localData = array();
	/**
	 * remote data array
	 */
	protected $remoteData = array();
	/**
	 * blocking 0:non-blocking mode 1:blocking mode
	 */
	protected $blocking;
	/**
	 * construct function
	 */
	public function __construct(){

	}
	/**
	 * set listen ip and port
	 * @param string $ip
	 * @param int $port
	 */
	public function listen($ip, $port){
		$this->ip = $ip;
		$this->port = $port;
	}
	/**
	 * set blocking mode
	 * 0:non-blocking mode 1:blocking mode
	 * @param int $blocking
	 */
	public function setBlocking($blocking){
		$this->blocking = $blocking;
	}

	/**
	 * start run
	 */
	public function run(){
		if ($this->init()){
			$this->blocking();
		}else {
			$this->nonBlocking();
		}
	}
	/**
	 * init
	 */
	protected function init(){
		error_reporting(E_ALL ^ E_NOTICE);
		set_time_limit(0);
		$this->originalSocket = stream_socket_server("tcp://$this->ip:$this->port", $errno, $errstr);
		if (!$this->originalSocket) {
			exit(0);
		}else {
			$this->originalSockets = array($this->originalSocket);
		}
		if (isset($this->blocking)) {
			stream_set_blocking($this->originalSocket, $this->blocking);
		}else {
			$this->blocking = 1;
		}
		return $this->blocking;
	}
	/**
	 * blocking mode
	 */
	protected function blocking(){
		while (true){
			while ($localSocket = @stream_socket_accept($this->originalSocket, 5)) {
				$localDatas = $this->getAllRequestDatas($localSocket);
				$responseDatas = $this->sendAllRequestDatas($localDatas);
				$this->returnAllResponseDatas($localSocket, $responseDatas);
				stream_socket_shutdown($localSocket, STREAM_SHUT_RDWR);
				fclose($localSocket);
			}
		}
	}
	/**
	 * select non-blocking mode
	 */
	protected function nonBlocking(){
		while (true){
			$this->readFds = array_merge($this->localReadSockets, $this->remoteReadSockets, $this->originalSockets);
			$this->writeFds = array_merge($this->localWriteSockets, $this->remoteWriteSockets);
			$this->exceptFds = array();
			// handle socket
			if (stream_select($this->readFds, $this->writeFds, $this->exceptFds, 0)){
				/**
				 * loop read
				 */
				foreach ($this->readFds as $sock){
					$sockId = (int)$sock;
					if (in_array($sock, $this->originalSockets)){
						/**
						 * if read socket in the $this->originalSockets
						 */
						$localSocket = stream_socket_accept($this->originalSocket, 5);
						stream_set_blocking($localSocket, 0);
						$id = (int)$localSocket;
						$this->localReadSockets[$id] = $localSocket;
						$this->localWriteSockets[$id] = $localSocket;
					}elseif (in_array($sock, $this->localReadSockets)){
						/**
						 * if read socket in the $this->localReadSockets
						 */
						$line = fgets($sock);
						//if POST method then get post data
						if ($this->localData[$sockId]['isHeaderEnd'] && $this->localData[$sockId]['header']['content-length']){
							$this->localData[$sockId]['messageBody'] = $this->localData[$sockId]['messageBody']==null ? $line : $this->localData[$sockId]['messageBody'].$line;
							if(strlen($this->localData[$sockId]['messageBody']) >= $this->localData[$sockId]['content-length']){
								//remove read socket
								stream_socket_shutdown($sock, STREAM_SHUT_RD);
								unset($this->localReadSockets[$sockId]);
								//if this is true then end write to this socket
								$this->localData[$sockId]['isReadEnd'] = true;
							}
						}
						//if \r\n
						if (!$this->localData[$sockId]['isHeaderEnd'] && $line == "\r\n"){
							$line = "Connection: close\r\n\r\n";
							$this->localData[$sockId]['isHeaderEnd'] = true;
							if (!$this->localData[$sockId]['header']['content-length']){
								//remove read socket
								stream_socket_shutdown($sock, STREAM_SHUT_RD);
								unset($this->localReadSockets[$sockId]);
								//if this is true then end write to this socket
								$this->localData[$sockId]['isReadEnd'] = true;
							}
						}
						//get header
						if ($this->localData[$sockId]['isRequestLineEnd'] && !$this->localData[$sockId]['isHeaderEnd']){
							$lineArrayTemp = explode(":", $line, 2);
		    				$this->localData[$sockId]['header'][strtolower($lineArrayTemp[0])] = trim(($lineArrayTemp[1]));
						}
						// read $request line
						if (!$this->localData[$sockId]['isRequestLineEnd']){
				    		$this->localData[$sockId]["requestLine"] = $line;
				    		$this->localData[$sockId]['isRequestLineEnd'] = true;
				    	}
						//create remote socket
						if ($this->localData[$sockId]['header']['host'] && !$this->socketIdMaps[$sockId]){
							$remoteSocket = stream_socket_client("tcp://".$this->localData[$sockId]['header']['host'].":80", $errno, $errstr, 9);
							if (!$remoteSocket){
								continue;
							}
							stream_set_blocking($remoteSocket, 0);
							$id = (int)$remoteSocket;
							$this->remoteReadSockets[$id] = $remoteSocket;
							$this->remoteWriteSockets[$id] = $remoteSocket;
							$this->socketIdMaps[$sockId] = $id;
						}
						// save line data
						$this->localData[$sockId]['data'] = $this->localData[$sockId]['data']==null ? $line : $this->localData[$sockId]['data'].$line;
					}elseif (in_array($sock, $this->remoteReadSockets)){
						/**
						 * if read socket in the $this->remoteReadSockets
						 */
						$line = fgets($sock);
						//get entity data
						if ($this->remoteData[$sockId]['isHeaderEnd']){
							$this->remoteData[$sockId]['messageBody'] = $this->remoteData[$sockId]['messageBody']==null ? $line : $this->remoteData[$sockId]['messageBody'].$line;
							if ($remoteData[$sockId]['header']['transfer-encoding']){
				    			if ($line == "0"){
				    				$remoteData[$sockId]['messageBody'] .= "\r\n\r\n";
				    				//remove read socket
									stream_socket_shutdown($sock, STREAM_SHUT_RD);
									unset($this->remoteReadSockets[$sockId]);
									//if this is true then end write to this socket
									$this->remoteData[$sockId]['isReadEnd'] = true;
				    			}
				    		}elseif ($this->remoteData[$sockId]['header']['content-length']){
					    		if (strlen($this->remoteData[$sockId]['messageBody']) >= $this->remoteData[$sockId]['header']['content-length']){
					    			//remove read socket
									stream_socket_shutdown($sock, STREAM_SHUT_RD);
									unset($this->remoteReadSockets[$sockId]);
									//if this is true then end write to this socket
									$this->remoteData[$sockId]['isReadEnd'] = true;
					    		}
				    		}
						}
						//if \r\n
						if (!$this->remoteData[$sockId]['isHeaderEnd'] && $line == "\r\n"){
							$this->remoteData[$sockId]['isHeaderEnd'] = true;
						}
						//get header
						if ($this->remoteData[$sockId]['isStatusLineEnd'] && !$this->remoteData[$sockId]['isHeaderEnd']){
							$lineArrayTemp = explode(":", $line, 2);
		    				$this->remoteData[$sockId]['header'][strtolower($lineArrayTemp[0])] = trim(($lineArrayTemp[1]));
						}
						// read status line
				    	if (!$this->remoteData[$sockId]["isStatusLineEnd"]){
				    		$this->remoteData[$sockId]["statusLine"] = $line;
				    		$this->remoteData[$sockId]["isStatusLineEnd"] = true;
				    	}
						// save line data
						$this->remoteData[$sockId]['data'] = $this->remoteData[$sockId]['data']==null ? $line : $this->remoteData[$sockId]['data'].$line;
					}
				} //end foreach
				/**
				 * loop write
				 */
				foreach ($this->writeFds as $sock){
					$sockId = (int)$sock;
					if (in_array($sock, $this->localWriteSockets)){
						if ($this->remoteData[$this->socketIdMaps[$sockId]]['data']){
							fwrite($sock, $this->remoteData[$this->socketIdMaps[$sockId]]['data'], strlen($this->remoteData[$this->socketIdMaps[$sockId]]['data']));
							$this->remoteData[$this->socketIdMaps[$sockId]]['data'] = null;
						}
						// end read, remove socket from $this->localWriteSockets
						if ($this->remoteData[$this->socketIdMaps[$sockId]]['isReadEnd']) {
							stream_socket_shutdown($sock, STREAM_SHUT_WR);
							unset($this->localWriteSockets[$sockId]);
						}
					}elseif (in_array($sock, $this->remoteWriteSockets)){
						$key = array_search($sockId, $this->socketIdMaps);
						if ($this->localData[$key]['data']){
							fwrite($sock, $this->localData[$key]['data'], strlen($this->localData[$key]['data']));
							$this->localData[$key]['data'] = null;
						}
						// end read, remove socket from $this->remoteWriteSockets
						if ($this->localData[$key]['isReadEnd']) {
							stream_socket_shutdown($sock, STREAM_SHUT_WR);
							unset($this->remoteWriteSockets[$sockId]);
						}
					}
				} //end foreach
			} //end if stream_select
		} // end while
	}

	/**
	 * get all request data
	 */
	protected function getAllRequestDatas($sock){
		$requestLineData = "";
		$headerData = array();
		$messageBodyData = "";
		$methodData = "";
		$isRequestLineEnd = false;
		$isHeaderEnd = false;

	    while (true){
	    	$temp = fgets($sock);
	    	// read entity body
	    	if ($isHeaderEnd && !empty($headerData['content-length'])){
	    		$messageBodyData .= $temp;
	    		if (strlen($messageBodyData) >= $headerData['content-length']){
	    			break;
	    		}
	    	}
	    	// if it has content-lengh, then continue, otherwise break.
		    if (!$isHeaderEnd && $temp == "\r\n"){
		    	$isHeaderEnd = true;
		    	if (empty($headerData['content-length'])){
		    		break;
		    	}
	    	}
	    	// read header
	    	if ($isRequestLineEnd && !$isHeaderEnd){
	    		$lineArrayTemp = explode(":", $temp, 2);
	    		$headerData[strtolower($lineArrayTemp[0])] = trim(($lineArrayTemp[1]));
	    	}
	    	// read $request line
	    	if (!$isRequestLineEnd){
	    		$requestLineData = $temp;
	    		$isRequestLineEnd = true;
	    		// get method
	    		$methodTemp = explode(" ", $temp, 2);
	    		$methodData = $methodTemp[0];
	    	}
	   	} //end while

	   	$return = array(
	   		"requestLine" => $requestLineData,
	   		"header" => $headerData,
	   		"messageBody" => $messageBodyData,
	   		"method" => $methodData
	   	);
	   	return $return;
	}

	/**
	 * send all request and get all response
	 */
	protected function sendAllRequestDatas($localDatas){
		$requestLineData = $localDatas["requestLine"];
		$headerData = $localDatas["header"];
		$messageBodyData = $localDatas["messageBody"];

	    $remoteSocket = stream_socket_client("tcp://".$headerData['host'].":80", $errno, $errstr, 30);
	    if (!$remoteSocket){
	    	exit(0);
	    }
	    //prepare send data
	    $in = "";
	    $in .= $requestLineData;
	    foreach ($headerData as $k=>$v){
	    	$in .= "$k: $v\r\n";
	    }
	    $in .= "Connection: close\r\n\r\n";
	    if ($headerData['content-length']){
	    	$in .= $messageBodyData;
	    }
	    // send
	    fwrite($remoteSocket, $in, strlen($in));
	    // read response
		$statusLineData = "";
		$headerData = array();
		$messageBodyData = "";
		$isStatusLineEnd = false;
		$isHeaderEnd = false;
		while (true) {
			$temp = fgets($remoteSocket);
	    	// read entity body
	    	if ($isHeaderEnd){
	    		$messageBodyData .= $temp;
	    		if ($headerData['transfer-encoding']){
	    			if ($temp == "0"){
	    				$messageBodyData .= "\r\n\r\n";
	    				break;
	    			}
    			}elseif ($headerData['content-length']){
    				if (strlen($messageBodyData) >= $headerData['content-length']){
    					break;
    				}
	    		}
	    	}
	    	// end header
		    if (!$isHeaderEnd && $temp == "\r\n"){
		    	$isHeaderEnd = true;
	    	}
	    	// read header
	    	if ($isStatusLineEnd && !$isHeaderEnd){
	    		$lineArrayTemp = explode(":", $temp, 2);
	    		$headerData[strtolower($lineArrayTemp[0])] = trim(($lineArrayTemp[1]));
	    	}
	    	// read $request line
	    	if (!$isStatusLineEnd){
	    		$statusLineData = $temp;
	    		$isStatusLineEnd = true;
	    	}
		} // end while
		fclose($remoteSocket);

		$return = array(
			"statusLine" => $statusLineData,
			"header" => $headerData,
			"messageBody" => $messageBodyData
		);
		return $return;
	}

	/**
	 * return all response
	 */
	protected function returnAllResponseDatas($sock, $responseDatas){
		$statusLineData = $responseDatas["statusLine"];
		$headerData = $responseDatas["header"];
		$messageBodyData = $responseDatas["messageBody"];
		//prepare send data
	    $in = "";
	    $in .= $statusLineData;
	    foreach ($headerData as $k=>$v){
	    	$in .= "$k: $v\r\n";
	    }
	    $in .= "\r\n";
	    $in .= $messageBodyData;
		fwrite($sock, $in, strlen($in));
	}
}





















