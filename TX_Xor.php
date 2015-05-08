<?php
/**
 * xor
 * @author    TX <txthinking@gmail.com>
 * @link    http://blog.txthinking.com
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html     GNU GPL v2
 * @version 1.3 beta
 */
class TX_Xor{

	protected $key;

	public function __construct($key){
		$this->key = $key;
	}
	//加密 $s为待加密字符串
	public function go($s)  {
		for ($i=0; $i<strlen($s); $i++) {
			for ($j=0; $j<strlen($this->key); $j++) {
				$s[$i] = $s[$i]^$this->key[$j];
			}
		}
		return $s;
	}
}
