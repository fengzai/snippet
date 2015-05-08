<?php
/**
 * Debug class
 * @author    TX <txthinking@gmail.com>
 * @link    http://blog.txthinking.com
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html     GNU GPL v2
 */
class FreeSky_Debug extends FreeSky_Compile{
	public $variable_lines;
	public $switch_lines;
	public $foreach_lines;
	public $include_lines;
	public $annotate_lines;
	public $config_lines;
	public $error_notice;
	
	/**
	 * this function is check the template whether that has syntax error.
	 * @name public function debugs()
	 */
	public function debugs(){
		if(!is_readable($this->template_dir) || !is_readable($this->template_dir.'/'.$this->template_file.$this->template_postfix) || !is_writable($this->compile_dir) || !is_readable($this->config_dir)){
			$debugPage = file_get_contents('debug.tpl');
			$debugPage = preg_replace('/error/', 'Please check the power of file or directory!', $debugPage);
			file_put_contents($this->compile_dir.'/'.md5('debug').'.php', $debugPage);
			require $this->compile_dir.'/'.md5('debug').'.php';
			exit;
		}
		$this->template = file($this->template_dir.'/'.$this->template_file.$this->template_postfix);
		$this->formatVariable();
		if($this->variable_lines){
			$this->showError();
		}
		$this->formatSwitch();
		if($this->switch_lines){
			$this->showError();
		}
		$this->formatForeach();
		if($this->foreach_lines){
			$this->showError();
		}
		$this->formatInclude();
		if($this->include_lines){
			$this->showError();
		}
		$this->formatAnnotate();
		if($this->annotate_lines){
			$this->showError();
		}
		$this->formatConfig();
		if($this->config_lines){
			$this->showError();
		}
	}
	
	/**
	 * this function is for checking variable format.
	 * @name public function formatVariable()
	 */
	public function formatVariable(){
		
		foreach($this->template as $k=>$v){
			if(preg_match('/{\s+\$(\w+)}/', $v, $matches)){
				$this->variable_lines = ($k+1);
				$this->error_notice[($k+1)] = 'Variable format error: The blank is not allowed with \'{\' near '.$matches[1].' in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
			}
			if(preg_match('/{\$(\w+)\s+}/', $v, $matches)){
				$this->variable_lines = ($k+1);
				$this->error_notice[($k+1)] = 'Variable format error: The blank is not allowed with \'}\' near '.$matches[1].' in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
			}
			if(preg_match('/{\s+\$(\w+)\s+}/', $v, $matches)){
				$this->variable_lines = ($k+1);
				$this->error_notice[($k+1)] = 'Variable format error: The blank is not allowed with \'{}\' near '.$matches[1].' in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
			}
		}
	}
	
	/**
	 * this function is for checking switch format.
	 * @name public function formatSwitch()
	 */
	public function formatSwitch(){
		foreach($this->template as $k=>$v){
			if(preg_match('/{\s+switch \$'.'(\w+)'.' case (\w+)}/', $v, $matches)){
				$this->switch_lines = ($k+1);
				$this->error_notice[($k+1)] = 'Switch format error: The blank is not allowed with \'{\' near '.$matches[1].' in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
			}
			if(preg_match('/{switch \$'.'(\w+)'.' case (\w+)\s+}/', $v, $matches)){
				$this->switch_lines = ($k+1);
				$this->error_notice[($k+1)] = 'Switch format error: The blank is not allowed with \'}\' near '.$matches[2].' in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
			}
			if(preg_match('/{\s+switch @key case (\w+)}/', $v, $matches)){
				$this->switch_lines = ($k+1);
				$this->error_notice[($k+1)] = 'Switch format error: The blank is not allowed with \'{\' near @key in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
			}
			if(preg_match('/{switch @key case (\w+)\s+}/', $v, $matches)){
				$this->switch_lines = ($k+1);
				$this->error_notice[($k+1)] = 'Switch format error: The blank is not allowed with \'}\' near @key in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
			}
			if(preg_match('/{\s+switch @value case (\w+)}/', $v, $matches)){
				$this->switch_lines = ($k+1);
				$this->error_notice[($k+1)] = 'Switch format error: The blank is not allowed with \'{\' near @value in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
			}
			
			if(preg_match('/{switch \$key case (\w+)}/', $v, $matches)){
				$this->switch_lines = ($k+1);
				$this->error_notice[($k+1)] = '@key format error: Please use @key instead of $key near $key in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
			}
			if(preg_match('/{switch \$value case (\w+)}/', $v, $matches)){
				$this->switch_lines = ($k+1);
				$this->error_notice[($k+1)] = '@value format error: Please use @value instead of $value near $value in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
			}
			
			if(preg_match('/{switch @value case (\w+)\s+}/', $v, $matches)){
				$this->switch_lines = ($k+1);
				$this->error_notice[($k+1)] = 'Switch format error: The blank is not allowed with \'}\' near @value in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
			}
			if(preg_match('/{\s+case (\w+)}/', $v, $matches)){
				$this->switch_lines = ($k+1);
				$this->error_notice[($k+1)] = 'Switch format error: The blank is not allowed with \'{\' near '.$matches[1].' in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
			}
			if(preg_match('/{case (\w+)\s+}/', $v, $matches)){
				$this->switch_lines = ($k+1);
				$this->error_notice[($k+1)] = 'Switch format error: The blank is not allowed with \'}\' near '.$matches[1].' in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
			}
			if(preg_match('/{switch \$'.'(\w+)'.' case (\w+)}/', $v, $matches)){
				if(!preg_match('/{\/switch}/', implode('\n', $this->template))){
					$this->switch_lines = ($k+1);
					$this->error_notice[($k+1)] = 'Switch format error: The switch tag is not close near '.$matches[1].' in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
				}
			}
		}
	}
	
	/**
	 * this function is for checking foreach format.
	 * @name public function formatForeach()
	 */
	public function formatForeach(){
		foreach($this->template as $k=>$v){
			if(preg_match('/{\s+foreach \$(\w+)}/', $v, $matches)){
				$this->foreach_lines = ($k+1);
				$this->error_notice[($k+1)] = 'Foreach format error: The blank is not allowed with \'{\' near '.$matches[1].' in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
			}
			if(preg_match('/{foreach \$(\w+)\s+}/', $v, $matches)){
				$this->foreach_lines = ($k+1);
				$this->error_notice[($k+1)] = 'Foreach format error: The blank is not allowed with \'}\' near '.$matches[2].' in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
			}
			if(preg_match('/{\s+@key}/', $v, $matches)){
				$this->foreach_lines = ($k+1);
				$this->error_notice[($k+1)] = 'Foreach @key format error: The blank is not allowed with \'{\' near '.$matches[1].' in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
			}
			if(preg_match('/{@key\s+}/', $v, $matches)){
				$this->foreach_lines = ($k+1);
				$this->error_notice[($k+1)] = 'Foreach @key format error: The blank is not allowed with \'}\' near '.$matches[1].' in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
			}
			if(preg_match('/{\s+@value}/', $v, $matches)){
				$this->foreach_lines = ($k+1);
				$this->error_notice[($k+1)] = 'Foreach @value format error: The blank is not allowed with \'{\' near '.$matches[1].' in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
			}
			if(preg_match('/{@value\s+}/', $v, $matches)){
				$this->foreach_lines = ($k+1);
				$this->error_notice[($k+1)] = 'Foreach @value format error: The blank is not allowed with \'}\' near '.$matches[1].' in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
			}
			if(preg_match('/{foreach \$(\w+)}/', $v, $matches)){
				if(!preg_match('/{\/foreach}/', implode('\n', $this->template))){
					$this->foreach_lines = ($k+1);
					$this->error_notice[($k+1)] = 'Foreach format error: The foreach tag is not close near '.$matches[1].' in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
				}
			}
		}
	}
	
	/**
	 * this function is for checking include format
	 * @name public function formatInclude()
	 */
	public function formatInclude(){
		foreach($this->template as $k=>$v){
			if(preg_match('/{\s+include ([\w\.]+)}/', $v, $matches)){
				$this->include_lines = ($k+1);
				$this->error_notice[($k+1)] = 'Include format error: The blank is not allowed with \'{\' near '.$matches[1].' in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
			}
			if(preg_match('/{include ([\w\.]+)\s+}/', $v, $matches)){
				$this->include_lines = ($k+1);
				$this->error_notice[($k+1)] = 'Include format error: The blank is not allowed with \'}\' near '.$matches[1].' in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
			}
			if(preg_match('/{include ([\w\.]+)}/', $v, $matches)){
				if(!file_exists($this->template_dir.'/'."$matches[1]")){
					$debugPage = file_get_contents('debug.tpl');
					$debugPage = preg_replace('/error/', "$matches[1]".' is not exist!', $debugPage);
					file_put_contents($this->compile_dir.'/'.md5('debug').'.php', $debugPage);
					require $this->compile_dir.'/'.md5('debug').'.php';
					exit;
				}
			}
		}
	}
	
	/**
	 * this function is for checking annotate format
	 * @name public function formatAnnotate()
	 */
	public function formatAnnotate(){
		foreach($this->template as $k=>$v){
			if(preg_match('/{\s+\*/', $v, $matches)){
				$this->include_lines = ($k+1);
				$this->error_notice[($k+1)] = 'Annotate format error: The blank is not allowed with \'{\' near '.$matches[0].' in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
			}
			if(preg_match('/\*\s+}/', $v, $matches)){
				$this->include_lines = ($k+1);
				$this->error_notice[($k+1)] = 'Annotate format error: The blank is not allowed with \'}\' near '.$matches[0].' in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
			}
		}
	}
	
	/**
	 * this function is for checking config format.
	 * @name public function formatConfig()
	 */
	public function formatConfig(){
		foreach($this->template as $k=>$v){
			if(preg_match('/{\s+\#/', $v, $matches)){
				$this->config_lines = ($k+1);
				$this->error_notice[($k+1)] = 'Config format error: The blank is not allowed with \'{\' near '.$matches[0].' in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
			}
			if(preg_match('/\#\s+}/', $v, $matches)){
				$this->config_lines = ($k+1);
				$this->error_notice[($k+1)] = 'Config format error: The blank is not allowed with \'}\' near '.$matches[0].' in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
			}
			if(preg_match('/{#\s+(\w+)#}/', $v, $matches)){
				$this->config_lines = ($k+1);
				$this->error_notice[($k+1)] = 'Config format error: The blank is not allowed with \'{#\' near '.$matches[0].' in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
			}
			if(preg_match('/{#(\w+)\s+#}/', $v, $matches)){
				$this->config_lines = ($k+1);
				$this->error_notice[($k+1)] = 'Config format error: The blank is not allowed with \'#}\' near '.$matches[0].' in '.$this->template_file.$this->template_postfix.' on line '.($k+1);
			}
		}
	}
	
	/**
	 * this function is for showing the error.
	 * @name public function showError()
	 */
	public function showError(){
		if(is_array($this->error_notice)){
			ksort($this->error_notice);
			foreach($this->error_notice as $k=>$v){
				$debugPage = file_get_contents('debug.tpl');
				$debugPage = preg_replace('/error/', $v, $debugPage);
				file_put_contents($this->compile_dir.'/'.md5('debug').'.php', $debugPage);
				require $this->compile_dir.'/'.md5('debug').'.php';
				exit;
			}
		}
	}
}
































