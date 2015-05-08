<?php
/**
 * TX_Imap
 * @author    TX <txthinking@gmail.com>
 * @link    http://blog.txthinking.com
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html     GNU GPL v2
 * @version 0.9
 */
class TX_Imap{
	/**
	 * imap server
	 */
	protected $server;
	/**
	 * imap server port
	 */
	protected $port;
	/**
	 * smtp secure ssl tls
	 */
	protected $secure;
	/**
	 * user name
	 */
	protected $username;
	/**
	 * password
	 */
	protected $password;
	/**
	 * socket
	 */
	protected $imap;
	/**
	 * tag
	 */
	protected $tag;
	/**
	 * tag int
	 */
	protected $tagInt;
	/**
	 * response message
	 */
	protected $message;
	/**
	 * $this->CRLF
	 */
	protected $CRLF;
	/**
	 * Any State: 0
	 * Authenticated State: 1 
	 * Not Authenticated State: 2
	 * Selected State: 3
	 */
	protected $state;
	/**
	 * const
	 */
	const ANY_STATE = 0;
	const AUTHENTICATED_STATE = 1;
	const NOT_AUTHENTICATED_STATE = 2;
	const SELECTED_STATE = 3;
	
	/**
	 * construct function
	 */
	public function __construct(){
		$this->tag = 'TX';
		$this->tagInt = 0;
		$this->message = array();
		$this->message['all'] = '';
		$this->message['now'] = '';
		$this->CRLF = "\r\n";
		$this->state = self::ANY_STATE;
	}
	/**
	 * 
	 * set imap server and port
	 * @param unknown_type $server
	 * @param unknown_type $port
	 */
	public function setServer($server, $port, $secure=null){
		$this->server = $server;
		$this->port = $port;
		$this->secure = $secure;
	}
	/**
	 * set user name and password
	 * @param unknown_type $username
	 * @param unknown_type $password
	 */
	public function setAuth($username, $password){
		$this->username = $username;
		$this->password = $password;
	}
	/**
	 * 
	 * get Mail Count
	 * @param $mailBox
	 * @param $return value: all, unseen
	 */
	public function getMailCount($mailBox='inbox', $return='unseen'){
		if ($this->state != self::AUTHENTICATED_STATE){
			if(!$this->toAuthenticatedState()){
				return false;
			}
		}
		if (!$this->status($mailBox, '(MESSAGES UNSEEN)')){
			return false;
		}else{
			$statusMessage = explode($this->CRLF, $this->message['now']);
			$temp0 = strpos($statusMessage[0], '(');
			$temp1 = strpos($statusMessage[0], ')');
			$temp2 = substr($statusMessage[0], $temp0, $temp1-$temp0);
			$temp3 = explode(' ', $temp2);
			$result['messages'] = $temp3[1];
			$result['unseen'] = $temp3[3];
			if ($return == 'all'){
				return $result;
			}
			return $result['unseen'];
		}
		return false;
	}
	/**
	 * get new mail contents
	 */
	public function getNewMail($mailBox = 'inbox'){
		$mailCount = $this->getMailCount($mailBox, 'all');
		if ($this->state != self::SELECTED_STATE){
			if(!$this->toSelectedState($mailBox)){
				return false;
			}
		}
		$start = $mailCount['messages']-$mailCount['unseen']+1;
		$end = $mailCount['messages'];
		$mailContent = array();
		for (;$start<=$end;$start++){
			if (!$this->fetch($start, "BODY[]")){
				return false;
			}else{
				$mailContent[$start] = $this->message['now'];
			}
		}
		return $mailContent;
	}
	/**
	 * change to AUTHENTICATED_STATE
	 */
	protected function toAuthenticatedState(){
		if (!$this->imap){
			if (!$this->connect()){
				return false;
			}
		}
		if (!$this->capability()){
			return false;
		}
		if ($this->secure == 'tls'){
			if (!$this->starttls()){
				return false;
			}
		}
		if (!$this->login($this->username, $this->password)){
			return false;
		}
		$this->state = self::AUTHENTICATED_STATE;
		return true;
	}
	/**
	 * change to SELECTED_STATE
	 */
	protected function toSelectedState($mailBox = 'inbox'){
		if (!$this->select($mailBox)){
			return false;
		}
		$this->state = self::SELECTED_STATE;
		return true;
	}
	/**
	 * connect
	 */
	protected function connect(){
		$host = ($this->secure == 'ssl') ? 'ssl://' . $this->server : $this->server;
		$this->imap = fsockopen($host, $this->port);
		if (!$this->imap){
			return false;
		}
		return true;
	}
	/**
	 * CAPABILITY Any State
	 */
	protected function capability(){
		$tag = $this->tag . $this->tagInt++;
		$in = "$tag CAPABILITY" . $this->CRLF;
		fwrite($this->imap, $in, strlen($in));
		return $this->getResponse($tag);
	}
	/**
	 * NOOP Any State
	 */
	protected function noop(){
		$tag = $this->tag . $this->tagInt++;
		$in = "$tag NOOP" . $this->CRLF;
		fwrite($this->imap, $in, strlen($in));
		return $this->getResponse($tag);
	}
	/**
	 * LOGOUT Any State
	 */
	protected function logout(){
		$tag = $this->tag . $this->tagInt++;
		$in = "$tag LOGOUT" . $this->CRLF;
		fwrite($this->imap, $in, strlen($in));
		return $this->getResponse($tag);
	}
	/**
	 * AUTHENTICATE Not Authenticated State
	 */
	protected function authenticate($mechanismName){
		$tag = $this->tag . $this->tagInt++;
		$in = "$tag AUTHENTICATE $mechanismName" . $this->CRLF;
		fwrite($this->imap, $in, strlen($in));
		return $this->getResponse($tag);
	}
	/**
	 * STARTTLS Not Authenticated State
	 */
	protected function starttls(){
		$tag = $this->tag . $this->tagInt++;
		$in = "$tag STARTTLS" . $this->CRLF;
		fwrite($this->imap, $in, strlen($in));
		return $this->getResponse($tag);
	}
	/**
	 * LOGIN Not Authenticated State
	 */
	protected function login($username, $password){
		$tag = $this->tag . $this->tagInt++;
		$in = "$tag LOGIN $username $password" . $this->CRLF;
		fwrite($this->imap, $in, strlen($in));
		return $this->getResponse($tag);
	}
	/**
	 * SELECT Authenticated State
	 */
	protected function select($mailBox){
		$tag = $this->tag . $this->tagInt++;
		$in = "$tag SELECT $mailBox" . $this->CRLF;
		fwrite($this->imap, $in, strlen($in));
		return $this->getResponse($tag);
	}
	/**
	 * EXAMINE Authenticated State
	 * same SELECT, but read only
	 */
	protected function examine($mailBox){
		$tag = $this->tag . $this->tagInt++;
		$in = "$tag EXAMINE $mailBox" . $this->CRLF;
		fwrite($this->imap, $in, strlen($in));
		return $this->getResponse($tag);
	}
	/**
	 * LIST Authenticated State
	 */
	protected function listX($reference, $mailBox){
		$tag = $this->tag . $this->tagInt++;
		$in = "$tag LIST $reference $mailBox" . $this->CRLF;
		fwrite($this->imap, $in, strlen($in));
		return $this->getResponse($tag);
	}
	/**
	 * CREATE Authenticated State
	 */
	protected function create($mailBox){
		$tag = $this->tag . $this->tagInt++;
		$in = "$tag CREATE $mailBox" . $this->CRLF;
		fwrite($this->imap, $in, strlen($in));
		return $this->getResponse($tag);
	}
	/**
	 * DELETE Authenticated State
	 */
	protected function delete($mailBox){
		$tag = $this->tag . $this->tagInt++;
		$in = "$tag DELETE $mailBox" . $this->CRLF;
		fwrite($this->imap, $in, strlen($in));
		return $this->getResponse($tag);
	}
	/**
	 * RENAME Authenticated State
	 */
	protected function rename($mailBox, $newMailBox){
		$tag = $this->tag . $this->tagInt++;
		$in = "$tag RENAME $mailBox $newMailBox" . $this->CRLF;
		fwrite($this->imap, $in, strlen($in));
		return $this->getResponse($tag);
	}
	/**
	 * LSUB Authenticated State
	 */
	protected function lsub($reference, $mailBox){
		$tag = $this->tag . $this->tagInt++;
		$in = "$tag LSUB $reference $mailBox" . $this->CRLF;
		fwrite($this->imap, $in, strlen($in));
		return $this->getResponse($tag);
	}
	/**
	 * SUBSCRIBE Authenticated State
	 */
	protected function subscribe($mailBox){
		$tag = $this->tag . $this->tagInt++;
		$in = "$tag SUBSCRIBE $mailBox" . $this->CRLF;
		fwrite($this->imap, $in, strlen($in));
		return $this->getResponse($tag);
	}
	/**
	 * UNSUBSCRIBE Authenticated State
	 */
	protected function unsubscribe($mailBox){
		$tag = $this->tag . $this->tagInt++;
		$in = "$tag UNSUBSCRIBE $mailBox" . $this->CRLF;
		fwrite($this->imap, $in, strlen($in));
		return $this->getResponse($tag);
	}
	/**
	 * STATUS Authenticated State
	 */
	protected function status($mailBox, $dataItemNames){
		$tag = $this->tag . $this->tagInt++;
		$in = "$tag STATUS $mailBox $dataItemNames" . $this->CRLF;
		fwrite($this->imap, $in, strlen($in));
		return $this->getResponse($tag);
	}
	/**
	 * APPEND Authenticated State
	 */
	protected function append($mailBox, $flag, $date, $message){
		$tag = $this->tag . $this->tagInt++;
		$in = "$tag APPEND $mailBox $flag $date" . $this->CRLF;
		fwrite($this->imap, $in, strlen($in));
		fwrite($this->imap, $message, strlen($message));
		return $this->getResponse($tag);
	}
	/**
	 * CHECK Selected State
	 */
	protected function check(){
		$tag = $this->tag . $this->tagInt++;
		$in = "$tag CHECK" . $this->CRLF;
		fwrite($this->imap, $in, strlen($in));
		return $this->getResponse($tag);
	}
	/**
	 * EXPUNGE Selected State
	 */
	protected function expunge(){
		$tag = $this->tag . $this->tagInt++;
		$in = "$tag EXPUNGE" . $this->CRLF;
		fwrite($this->imap, $in, strlen($in));
		return $this->getResponse($tag);
	}
	/**
	 * SEARCH Selected State
	 */
	protected function search($charset, $search){
		$tag = $this->tag . $this->tagInt++;
		$in = "$tag SEARCH CHARSET $charset $search" . $this->CRLF;
		fwrite($this->imap, $in, strlen($in));
		return $this->getResponse($tag);
	}
	/**
	 * FETCH Selected State
	 */
	protected function fetch($sequenceSet, $dataItemName){
		$tag = $this->tag . $this->tagInt++;
		$in = "$tag FETCH $sequenceSet $dataItemName" . $this->CRLF;
		fwrite($this->imap, $in, strlen($in));
		return $this->getResponse($tag);
	}
	/**
	 * STORE Selected State
	 */
	protected function store($sequenceSet, $dataItemName, $dataItemNameValue){
		$tag = $this->tag . $this->tagInt++;
		$in = "$tag STORE $sequenceSet $dataItemName $dataItemNameValue" . $this->CRLF;
		fwrite($this->imap, $in, strlen($in));
		return $this->getResponse($tag);
	}
	/**
	 * COPY Selected State
	 */
	protected function copy($sequenceSet, $dataItemName){
		$tag = $this->tag . $this->tagInt++;
		$in = "$tag COPY $sequenceSet $dataItemName" . $this->CRLF;
		fwrite($this->imap, $in, strlen($in));
		return $this->getResponse($tag);
	}
	/**
	 * UID Selected State
	 */
	protected function uid($sequenceSet, $command, $arguments){
		$tag = $this->tag . $this->tagInt++;
		$in = "$tag UID $sequenceSet $command $arguments" . $this->CRLF;
		fwrite($this->imap, $in, strlen($in));
		return $this->getResponse($tag);
	}
	/**
	 * CLOSE Selected State
	 */
	protected function close(){
		$tag = $this->tag . $this->tagInt++;
		$in = "$tag CLOSE" . $this->CRLF;
		fwrite($this->imap, $in, strlen($in));
		return $this->getResponse($tag);
	}
	/**
	 * XCOMMAND
	 */
	protected function xcommand($xcommand){
		$tag = $this->tag . $this->tagInt++;
		$in = "$tag $xcommand" . $this->CRLF;
		fwrite($this->imap, $in, strlen($in));
		return $this->getResponse($tag);
	}
	
	/**
	 * get server response
	 */
	protected function getResponse($tag){
		$this->message['now'] = '';
		while($str = @fgets($this->imap,1024)) {
			$this->message['all'] .= $str;
	        $this->message['now'] .= $str;
	        if(strpos($str, $tag.' OK') !== false) { 
	      	    return true;
	        }elseif(strpos($str, $tag.' NO') !== false) { 
	      	    return false;
	        }elseif(strpos($str, $tag.' BAD') !== false){
	        	return false;
	        }
	    }
	    return false;
	}
	/**
	 * destruct
	 */
	public function __destruct(){
		if ($this->state == self::SELECTED_STATE){
			$this->close();
			$this->state = self::AUTHENTICATED_STATE;
		}
		if ($this->state == self::AUTHENTICATED_STATE){
			$this->logout();
			$this->state = self::ANY_STATE;
		}
		if ($this->imap) {
			fclose($this->imap);
		}
	}
}

