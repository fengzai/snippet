<?php
/**
 * File name: FreeSky.class.php
 * this file is main file for TX-Free.
 * @author    TX <txthinking@gmail.com>
 * @link    http://blog.txthinking.com
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html     GNU GPL v2
 */
class FreeSky{
	public $template_dir = 'templates';
	public $compile_dir = 'compiles';
	public $config_dir = 'configs';
	public $template_postfix = '.html';
	
	public $template_file;
	public $variables;
	public $config_value;
	public $caching = FALSE;
	public $debuging = TRUE;
	public $FSCompiler;
	public $FSDebuger;
	public $cache_time ='3000';
	
	/**
	 * construct function for class FreeSky.
	 * $param no parameter
	 */
	public function __construct(){
		if(file_exists($this->config_dir.'/config.php')){
			$this->config_value = include $this->config_dir.'/config.php';
		}
	}
	
	/**
	 * this function set variable for template.
	 * @name public function assign()
	 * @param string $_variableName
	 * @param variable $_variables
	 */
	public function assign($_variableName, $_values=''){
		if(!trim($_variableName)){
			exit('The first parameter of function assign() can not empty!');
		}
		$this->variables["$_variableName"] = $_values;
	}
	
	/**
	 * this function compile templates and display it.
	 * @name public function display
	 * @param string file name $_templateFile
	 */
	public function display($_templateFile){
		$this->template_file = $_templateFile;
		if(!file_exists($this->template_dir.'/'.$this->template_file.$this->template_postfix)){
			exit('Template file '.$_templateFile.$this->template_postfix.' is not found!');
		}
		include_once 'FreeSky_Compile.class.php';
		$this->FSCompiler = new FreeSky_Compile();
		$this->FSCompiler->template_dir = $this->template_dir;
		$this->FSCompiler->compile_dir = $this->compile_dir;
		$this->FSCompiler->config_dir = $this->config_dir;
		$this->FSCompiler->template_postfix = $this->template_postfix;
		$this->FSCompiler->template_file = $this->template_file;
		$this->FSCompiler->variables = $this->variables;
		$this->FSCompiler->caching = $this->caching;
		$this->FSCompiler->config_value = $this->config_value;
		$this->FSCompiler->cache_time = $this->cache_time;
		if($this->debuging){
			$this->debug();
		}
		require $this->compile_dir.'/'.$this->FSCompiler->start();
	}
	
	/**
	 * this function debug for template.
	 * @name function debug()
	 */
	public function debug(){
		include_once 'FreeSky_Debug.class.php';
		$this->FSDebuger = new FreeSky_Debug();
		$this->FSDebuger->template_dir = $this->template_dir;
		$this->FSDebuger->template_file = $this->template_file;
		$this->FSDebuger->template_postfix = $this->template_postfix;
		$this->FSDebuger->compile_dir = $this->compile_dir;
		$this->FSDebuger->config_dir = $this->config_dir;
		$this->FSDebuger->template = file_get_contents($this->template_dir.'/'.$this->template_file.$this->template_postfix);
		$this->FSDebuger->debugs();
	}
	
	/**
	 * this is the destruct function for destrory some variable.
	 * @name function __destruct()
	 */
	public function __destruct(){
		unset($this->FSCompiler);
		unset($this->FSDebuger);
	}
}














