<?php
/**
 * zip and unzip operating
 * @author    TX <txthinking@gmail.com>
 * @link    http://blog.txthinking.com
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html     GNU GPL v2
 */
class TX_Zip{
	/**
	 * instance of ZipArchive
	 * @var ZipArchive
	 */
	protected $ZipArchive = null;
	/**
	 * zip file name	
	 * @var String
	 */
	protected $zipName = 'TX.zip';
	/**
	 * files array when zip a directory
	 * @var Array
	 */
	protected $allFiles = array();
	
	/**
	 * construct method get a instance of ZipArchive
	 */
	public function __construct(){
		$this->ZipArchive = new ZipArchive();
	}
	/**
	 * 
	 * set zip file name
	 * @param String $zipName
	 */
	public function setZipName($zipName){
		$this->zipName = $zipName;
	}
	/**
	 * 
	 * zip a directory or some file
	 * @param String/Array $directoryORfilesarray
	 * @return boolean
	 * @throws Exception
	 */
	public function zip($directoryORfilesarray = '.'){
		//backup old zip file
		if (file_exists($this->zipName)){
			if (!rename($this->zipName, $this->zipName . '.old')){
				throw new Exception("Cannot backup $this->zipName File, check permissions!");
			}
		}
		//create zip file
		if ($this->ZipArchive->open($this->zipName, ZIPARCHIVE::CREATE) !== TRUE){
			throw new Exception("Cannot create $this->zipName File, check permissions and directory!");
		}
		//zip a directory
		if (is_string($directoryORfilesarray) && is_dir($directoryORfilesarray)){
			$dir = rtrim($directoryORfilesarray, '/');
			//get all file
			$this->_getAllFiles($dir, $this->allFiles);
			//add file to zip file
			foreach ($this->allFiles as $file){
				if (is_dir($file)){
					if ($dir == '.'){
						$file = substr($file, 2);
					}
					$this->ZipArchive->addEmptyDir($file);
				}
				if (is_file($file)){
					if ($dir == '.'){
						$file = substr($file, 2);
					}
					$this->ZipArchive->addFile($file);
				}
			}
		}
		//zip some files
		if (is_array($directoryORfilesarray)){
			foreach ($directoryORfilesarray as $file){
				if (!file_exists($file)){
					throw new Exception("$file is not exists!");
				}
				if (is_dir($file)){
					$this->ZipArchive->addEmptyDir($file);
				}
				if (is_file($file)){
					$this->ZipArchive->addFile($file);
				}
			}
		}
		return $this->ZipArchive->close();
	}
	/**
	 * 
	 * get all files from a directory
	 * @param String $directory
	 */
	protected function _getAllFiles($directory, &$allFiles){
    	foreach(scandir($directory) as $fileName){
            if($fileName != '.' && $fileName != '..'){
            	$allFiles[] = $directory . '/' . $fileName;
                if(is_dir($directory.'/'.$fileName)){
                    $this->_getAllFiles($directory.'/'.$fileName, $allFiles);
                }
            }
        }
	}
	/**
	 * 
	 * unzip all files or some files from a zip file
	 * @param String $directory
	 * @param array/String $files
	 * @throws Exception
	 */
	public function unzip($directory = '.', $files = null){
		if ($this->ZipArchive->open($this->zipName) !== TRUE){
			throw new Exception("Cannot open $this->zipName File, check permissions and directory!");
		}
		$this->ZipArchive->extractTo($directory, $files);
		return $this->ZipArchive->close();
	}
}