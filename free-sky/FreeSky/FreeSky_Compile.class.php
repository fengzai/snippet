<?php
/**
 * File name: FreeSky_Compile.php
 * this file can compile the templates for TX-Free.
 * @author    TX <txthinking@gmail.com>
 * @link    http://blog.txthinking.com
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html     GNU GPL v2
 */
class FreeSky_Compile extends FreeSky{
	public $template;
	/**
	 * construct function for class FreeSky_Compile.
	 */
	public function __construct(){
		parent::__construct();
	}
	/**
	 * this function let the compile start
	 * @name public function start()
	 * @param string template file name $_templateFile
	 */
	public function start(){
		$this->template = file_get_contents($this->template_dir.'/'.$this->template_file.$this->template_postfix);
		$this->parseVariable();
		$this->parseSwitch();
		$this->parseForeach();
		$this->parseInclude();
		$this->parseAnnotate();
		$this->parseConfig();
		$compiledFile = md5($this->template_file).'.php';
		if(!file_exists($this->compile_dir.'/'.$compiledFile)){
			if(!file_put_contents($this->compile_dir.'/'.$compiledFile, $this->template)){
				$debugPage = file_get_contents('debug.tpl');
				$debugPage = preg_replace('/error/', 'Please check the power for operating of file or directory!', $debugPage);
				file_put_contents($this->compile_dir.'/'.md5('debug').'.php', $debugPage);
				require $this->compile_dir.'/'.md5('debug').'.php';
				exit;
			}
		}else{
			$compiledFileTime = filemtime($this->compile_dir.'/'.$compiledFile);
			$templateFileTime = filemtime($this->template_dir.'/'.$this->template_file.$this->template_postfix);
			if(!$this->caching){
				if(!file_put_contents($this->compile_dir.'/'.$compiledFile, $this->template)){
					$debugPage = file_get_contents('debug.tpl');
					$debugPage = preg_replace('/error/', 'Please check the power for operating of file or directory!', $debugPage);
					file_put_contents($this->compile_dir.'/'.md5('debug').'.php', $debugPage);
					require $this->compile_dir.'/'.md5('debug').'.php';
					exit;
				}
			}else{
				if(($compiledFileTime+0)<($templateFileTime+0)){
					if(!file_put_contents($this->compile_dir.'/'.$compiledFile, $this->template)){
						$debugPage = file_get_contents('debug.tpl');
						$debugPage = preg_replace('/error/', 'Please check the power for operating of file or directory!', $debugPage);
						file_put_contents($this->compile_dir.'/'.md5('debug').'.php', $debugPage);
						require $this->compile_dir.'/'.md5('debug').'.php';
						exit;
					}
				}elseif($compiledFileTime+$this->cache_time<time()){
					if(!file_put_contents($this->compile_dir.'/'.$compiledFile, $this->template)){
						$debugPage = file_get_contents('debug.tpl');
						$debugPage = preg_replace('/error/', 'Please check the power for operating of file or directory!', $debugPage);
						file_put_contents($this->compile_dir.'/'.md5('debug').'.php', $debugPage);
						require $this->compile_dir.'/'.md5('debug').'.php';
						exit;
					}
				}
				
			}
			
		}
		return $compiledFile;
	}
	
	/**
	 * this function compile variables
	 * @name public function parseVariable()
	 */
	public function parseVariable(){
		if(is_array($this->variables)){
			foreach($this->variables as $k=>$v){
			$pattern[] = '/{\$'.$k.'}/';
			$replacement[] = '<?php echo \$this->variables["'.$k.'"];?>';
			}
			ksort($pattern);
			ksort($replacement);
			$this->template = preg_replace($pattern, $replacement, $this->template);
		}
		
	}
	
	/**
	 * this function compile switch sentence.
	 * @name public function parseSwitch()
	 */
	public function parseSwitch(){
		/*
		 * {switch $x case 123}...{case 456}...{default}...{/switch}
		 * <?php switch($this->variables[$k]){case 123:?>...<?php } ?>
		 */
		$pattern[] = '/{switch \$'.'(\w+)'.' case (\w+)}/';
		$replacement[] = '<?php switch(\$this->variables["'.'$1'.'"]){ case \'$2\':?>';
		
		$pattern[] = '/{case (\w+)}/';
		$replacement[] = '<?php break;case \'$1\': ?>';
		
		$pattern[] = '/{default}/';
		$replacement[] = '<?php break;default: ?>';
		
		$pattern[] = '/{\/switch}/';
		$replacement[] = '<?php } ?>';
		
		ksort($pattern);
		ksort($replacement);
		$this->template = preg_replace($pattern, $replacement, $this->template);
	}
	
	/**
	 * this function compile foreach sentence.
	 * @name public function parseForeach()
	 */
	public function parseForeach(){
		/*
		 * {foreach $x}{@key}--{@value}{/foreach}
		 */
		$pattern[] = '/{foreach \$(\w+)}/';
		$replacement[] = '<?php if(is_array(\$this->variables["$1"])){foreach(\$this->variables["$1"] as \$key=>\$value){ ?>';
		
		$pattern[] = '/{switch @key case (\w+)}/';
		$replacement[] = '<?php switch("\$key"){ case \'$1\':?>';
		
		$pattern[] = '/{switch @value case (\w+)}/';
		$replacement[] = '<?php switch(\$value){ case \'$1\':?>';
		
		$pattern[] = '/{@key}/';
		$replacement[] = '<?php echo $key; ?>';
		
		$pattern[] = '/{@value}/';
		$replacement[] = '<?php echo $value; ?>';
		
		$pattern[] = '/{\/foreach}/';
		$replacement[] = '<?php }} ?>';
		
		ksort($pattern);
		ksort($replacement);
		
		$this->template = preg_replace($pattern, $replacement, $this->template);
	}
	
	/**
	 * this function compile include sentence.
	 * @name public function parseInclude()
	 */
	public function parseInclude(){
		/*
		 * {include test.html}
		 * <?php include 'test.html';?>
		 */
		$pattern = '/{include ([\w\.]+)}/';
		$replacement = '<?php include "'.$this->template_dir.'/$1"; ?>';
		$this->template = preg_replace($pattern, $replacement, $this->template);
	}
	
	/**
	 * this function compile annotate sentence.
	 * @name public function parseAnnotate()
	 */
	public function parseAnnotate(){
		/*
		 * {*here is annotate*}
		 * <?php /\*here is annotate*\/ ?>
		 */
		$pattern[] = '/{\*/';
		$replacement[] = '<?php /*';
		$pattern[] = '/\*}/';
		$replacement[] = ' */?>';
		ksort($pattern);
		ksort($replacement);
		$this->template = preg_replace($pattern, $replacement, $this->template);
	}
	
	/**
	 * this function compile config value.
	 * public function parseConfig()
	 */
	public function parseConfig(){
		/*
		 * {#abc#}
		 * <?php echo $this->config_value['abc']; ?>
		 */
		$pattern = '/{\#(\w+)\#}/';
		$replacement = '<?php echo \$this->config_value["$1"]; ?>';
		$this->template = preg_replace($pattern, $replacement, $this->template);
	}
}



























