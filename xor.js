/**
 * xor
 * @author    TX <txthinking@gmail.com>
 * @link    http://blog.txthinking.com
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html     GNU GPL v2
 * @version 1.2
 */

function xor(k, s) {
	var i;
	var j;
	for (i=0; i<s.length; i++) {
		for (j=0; j<k.length; j++) {
			var temp = String.fromCharCode(s.charCodeAt(i)^k.charCodeAt(j));
			s = s.substring(0, i) + temp + s.substring(i+1);
		}
	}
	return s;
}
