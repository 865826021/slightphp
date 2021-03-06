<?php
/*{{{LICENSE
+-----------------------------------------------------------------------+
| SlightPHP Framework                                                   |
+-----------------------------------------------------------------------+
| This program is free software; you can redistribute it and/or modify  |
| it under the terms of the GNU General Public License as published by  |
| the Free Software Foundation. You should have received a copy of the  |
| GNU General Public License along with this program.  If not, see      |
| http://www.gnu.org/licenses/.                                         |
| Copyright (C) 2008-2009. All Rights Reserved.                         |
+-----------------------------------------------------------------------+
| Supports: http://www.slightphp.com                                    |
+-----------------------------------------------------------------------+
}}}*/
/**
 * @package SlightPHP
 */
class SError{

	/**
	 * 
	 */
	static $CONSOLE=false;
	/**
	 * 
	 */
	static $LOG=true;
	/**
	 * 
	 */
	static $LOGFILE="";


	/**
	 * 
	 */
	static $error_type=array(
		"1"	=>"E_ERROR",
		"2"	=>"E_WARNING",
		"4"	=>"E_PARSE",
		"8"	=>"E_NOTICE",
		"16"=>"E_CORE_ERROR",
		"32"=>"E_CORE_WARNING",
		"64"=>"E_COMPILE_ERROR",
		"128"=>"E_COMPILE_WARNING",
		"256"=>"E_USER_ERROR",
		"512"=>"E_USER_WARNING",
		"1024"=>"E_USER_NOTICE",
		"2047"=>"E_ALL",
		"2048"=>"E_STRICT",
		"4096"=>"E_RECOVERABLE_ERROR",
		"8192"=>"E_DEPRECATED",
		"16384"=>"E_USER_DEPRECATED",
		"30719"=>"E_ALL",
	);

	public static function exception_handler($e){//PHP7 Throwable
		$text= SError::getError($e->getTrace(),$e,$html);
		self::log($text,$html);
	}

	public static function error_handler($errno, $errstr, $errfile, $errline) {
		$text= SError::getError(debug_backtrace(),null,$html);
		self::log($text,$html);
	}

	public static function fatal_handler() {
		$error = error_get_last();
		if($error != NULL){
			$text= SError::getFatal($error,$html);
			self::log($text,$html);
		}
	}

	private static function getFatal($error,&$html=""){
		$errno   = $error["type"];
		$errfile = $error["file"];
		$errline = $error["line"];
		$errstr  = $error["message"];
		$text="\r\nFatal Error(".date("Y-m-d H:i:s").")\r\n".$errfile."\t".$errline."\t".$errstr."\r\n";
		$html="\r\n".'<table border="1" cellpadding="3" style="font-size: 75%;border: 1px solid #000000;border-collapse: collapse;"><tr style="background-color: #ccccff; font-weight: bold; color: #000000;"><th style="padding:4px">#</th><th style="padding:4px">File</th><th style="padding:4px">Line</th><th style="padding:4px">Class::Method(Args)</th><th style="padding:4px">Fatal Error</th></tr>';
		$html.='<tr style="background-color: #cccccc; color: red;font-weight:bold;"><td>0</td><td style="padding:4px">'.
			$errfile.'</td><td style="padding:4px">'.
			$errline.'</td><td style="padding:4px"><td>'.
			$errstr. '</td></tr></table><hr style="background-color: #cccccc; border: 0px; height: 1px;" />'."\r\n\r\n";
		return $text;
	}
	/**
	 * @return string
	 */
	private static function getError($backtrace,$e=null,&$html=""){
		$arrLen=count($backtrace);
		$text="\r\n".(empty($e)?"Error":"Exception")."(".date("Y-m-d H:i:s").")\r\n";
		$html="\r\n".'<table border="1" cellpadding="3" style="font-size: 75%;border: 1px solid #000000;border-collapse: collapse;"><tr style="background-color: #ccccff; font-weight: bold; color: #000000;"><th style="padding:4px">#</th><th style="padding:4px">File</th><th style="padding:4px">Line</th><th style="padding:4px">Class::Method(Args)</th><th style="padding:4px">'.(empty($e)?"Error":"Exception").'</th></tr>';
		$index=0;
		if($arrLen>0){
			for($i=$arrLen-1;$i>0;$i--){
				$line  = isset($backtrace[$i]['line'])?$backtrace[$i]['line']:"";
				$file  = isset($backtrace[$i]['file'])?$backtrace[$i]['file']:"";
				$class = isset($backtrace[$i]['class'])?$backtrace[$i]['class']:"";
				$type = isset($backtrace[$i]['type'])?$backtrace[$i]['type']:"";
				$func = isset($backtrace[$i]['function'])?$backtrace[$i]['function']:"";
				$text.=($index)."\t".$file."($line)\t".(empty($class)?"":$class.'::').$func."(";
				$html.='<tr style="background-color: #cccccc; color: #000000;"><td style="padding:4px">'.($index).'</td><td style="padding:4px">'.$file.'</td><td style="padding:4px">'.$line.'</td><td style="padding:4px">'.(empty($class)?"":$class.$type).$func.'(';
				if(!empty($backtrace[$i]['args'])){
					$text.=self::args2str($backtrace[$i]['args']);
					$html.=self::args2str($backtrace[$i]['args']);
				}
				$text.=")\r\n";
				$html.=')<td></td></tr>';
				$index++;
			}
		}
		$i=0;
		if($e){
			$text.=($index)."\t".$e->getFile()."(".$e->getLine().")\t".$e->getCode().":".$e->getMessage()."\t\r\n";
			$html.='<tr style="background-color: #cccccc; color: #000000;"><td style="padding:4px">'.($index).'</td><td style="padding:4px">'.$e->getFile().'</td><td style="padding:4px">'.$e->getLine().'</td><td></td><td style="padding:4px;font-weight:bold">'.$e->getCode().':'.$e->getMessage().'</td></tr>';
			$index++;
		}else{
			$errorCode = $backtrace[$i]['args'][0];
			$line  = isset($backtrace[$i]['line'])?$backtrace[$i]['line']:"";
			$text.=($index)."\t".
				@$backtrace[$i]['args'][2]."($line)\t".
				SError::$error_type[$errorCode].':'.
				(!empty($backtrace[$i]['args'])?$backtrace[$i]['args'][1]:"")."\r\n";
			$html.='<tr style="background-color: #cccccc; color: #000000;"><td style="padding:4px">'.($index).'</td><td style="padding:4px">'.$backtrace[$i]['args'][2].'</td><td style="padding:4px">'.$line.'</td><td></td><td style="padding:4px;font-weight:bold">'.SError::$error_type[$errorCode].':'.(!empty($backtrace[$i]['args'])?$backtrace[$i]['args'][1]:"").'</td></tr>';
			$index++;
		}
		$html.='</table><hr style="background-color: #cccccc; border: 0px; height: 1px;" />'."\r\n\r\n";
		return $text;
	}
	private static function obj2str(&$args){
		if(is_array($args)){
			foreach($args as &$v){
				self::obj2str($v);
			}
		}elseif(is_object($args)){
			$args=get_class($args)."::";
		}
	}
	private static function args2str($args){
		self::obj2str($args);
		$tmp  = SJson::encode($args);
		$tmp = str_replace("\/","/",$tmp);
		return substr($tmp,1,strlen($tmp)-2);
	}
	private static function log($text,$html){
		if(SError::$CONSOLE){
			if(SError::$CONSOLE){
				if(PHP_SAPI=="cli"){
					echo $text;
				}else{
					echo $html;
				}
			}
		}
		if(SError::$LOG){
			if(!empty(SError::$LOGFILE)){
				error_log($text,3,SError::$LOGFILE);
			}else error_log($text);
		}
	}
}
set_exception_handler(array('SError', 'exception_handler'));
set_error_handler(array('SError', 'error_handler'), E_ALL);
register_shutdown_function(array('SError', 'fatal_handler'));
