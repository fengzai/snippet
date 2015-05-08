#!/home/tx/ln/php -q
<?php
/**
 * @author    TX <txthinking@gmail.com>
 * @link    http://blog.txthinking.com
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html     GNU GPL v2
 * @version 1.2
 */
$v = "sudo -S poweroff";
$password = $argv[1];

//start execute
$descriptorspec = array(
    0 => array("pipe", "r"),  // stdin, Process will read from! 'r'
    1 => array("pipe", "w"),  // stdout, Process will write to! 'w'
    2 => array("file", "/tmp/poweroff-error", "a") // stderr is a file to write to
);

$process = proc_open($v, $descriptorspec, $pipes, null, null);
if (is_resource($process)) {
    //write to stdin
    fwrite($pipes[0], "$password\n");
    fclose($pipes[0]);

    //get command out from stdout
    $out = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    printf("$out");

    //close and return status
    $return_value = proc_close($process);
}

