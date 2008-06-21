<?php
// $Id: preload.php,v 1.1 2008/06/21 12:22:22 ohwada Exp $

//=========================================================
// webphoto module
// 2008-04-02 K.OHWADA
//=========================================================

if( ! defined( 'XOOPS_TRUST_PATH' ) ) die( 'not permit' ) ;

//=========================================================
// class webphoto_d3_preload
//=========================================================
class webphoto_d3_preload
{
	var $_DIRNAME;
	var $_MODULE_DIR;
	var $_MODULE_URL;

	var $_dh;
	var $_dir_name;

	var $_errors = array();

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function webphoto_d3_preload()
{
	// dummy
}

function &getInstance()
{
	static $instance;
	if (!isset($instance)) {
		$instance = new webphoto_d3_preload();
	}
	return $instance;
}

function init( $dirname , $trust_dirname )
{
	$this->_DIRNAME       = $dirname;
	$this->_TRUST_DIRNAME = $trust_dirname;

	$this->_MODULE_DIR = XOOPS_ROOT_PATH .'/modules/'. $dirname;
	$this->_MODULE_URL = XOOPS_URL       .'/modules/'. $dirname;
}

//---------------------------------------------------------
// public
//---------------------------------------------------------
function include_once_preload_files()
{
	$dirname = 'modules/'. $this->_DIRNAME .'/preload';
	$ext     = 'php';

	return $this->include_once_files_in_dir( $dirname, $ext );
}

function &get_class_instance( $name )
{
	$name_extend = $this->build_class_name( $name );
	$name_basic  = strtolower( $this->_TRUST_DIRNAME .'_'. $name );
	if ( class_exists( $name_extend ) ) {
		$ret = new $name_extend( $this->_DIRNAME , $this->_TRUST_DIRNAME );
		return $ret;

	} elseif ( class_exists( $name_basic ) ) {
		$ret = new $name_basic( $this->_DIRNAME , $this->_TRUST_DIRNAME );
		return $ret;
	}

	$false = false;
	return $false;
}

function exists_class( $name )
{
	if ( class_exists( $this->build_class_name( $name ) ) ) {
		return true;
	}
	return false;
}

function exists_function( $name )
{
	if ( function_exists( $this->build_function_name( $name ) ) ) {
		return true;
	}
	return false;
}

function exec_class_method( $name, $method, $options=null )
{
	$class_name = $this->build_class_name( $name );
	if ( class_exists($class_name) ) {
		$class = new $class_name( $this->_DIRNAME , $this->_TRUST_DIRNAME );

		if ( method_exists( $class, $method ) ) {
			return $class->$method( $options );
		}
	}
	return false;
}

function exec_function( $name, $options=null )
{
	$func = $this->build_function_name( $name );
	if ( function_exists($func) ) {
		return $func( $options );
	}
	return false;
}

function build_class_name( $name )
{
	return $this->build_name( $name );
}

function build_function_name( $name )
{
	return $this->build_name( $name );
}

function build_name( $name )
{
	return strtolower( $this->_TRUST_DIRNAME .'_'. $this->_DIRNAME .'_'. $name );
}

//---------------------------------------------------------
// preload contstant
//---------------------------------------------------------
function get_preload_const_array()
{
	$arr = array();

	$needle = strtoupper( '_P_'. $this->_DIRNAME . '_' );
	$constant_arr = get_defined_constants();

	foreach( $constant_arr as $k => $v )
	{
		if ( strpos( $k, $needle ) !== 0 ) { continue; }

		$key = strtolower( str_replace( $needle, '', $k ) );
		$arr[ $key ] = $v;
	}

	return $arr;
}

//---------------------------------------------------------
// dir handler
//---------------------------------------------------------
function include_once_files_in_dir( $dirname, $ext )
{
	$files = $this->get_files_in_dir( $dirname, $ext );
	if ( !is_array($files) || !count($files) ) { return false; } 

	foreach ( $files as $file ) 
	{
		if ( preg_match( "/^_/", $file) ) { continue; }
		include_once XOOPS_ROOT_PATH. '/'. $dirname .'/'. $file;
	}
	return true;
}

function get_files_in_dir( $dirname, $ext=null, $flag_dir=false, $flag_sort=false, $id_as_key=false  )
{
	$arr   = array();
	$false = false;

	$dirname = $this->strip_slash_from_tail( $dirname );

	$ret = $this->opendir($dirname);
	if ( !$ret ) { return $false; }

	$pattern = "/\.". preg_quote($ext) ."$/";

	foreach ( $this->readdir_array() as $file ) 
	{
		$xoops_file = XOOPS_ROOT_PATH .'/'. $dirname .'/'. $file;

		if ( !is_dir($xoops_file) && is_file($xoops_file) ) {
			if (( $ext && preg_match($pattern, $file) )||( $ext === '' )) {
				$file_out = $file;
				if ( $flag_dir ) {
					$file_out = $dirname .'/'. $file;
				}
				if ( $id_as_key ) {
					$arr[ $file ] = $file_out;
				} else {
					$arr[] = $file_out;
				}
			}
		}
	}

	$this->closedir();

	if ( $flag_sort ) {
		asort($arr);
		reset($arr);
	}

	return $arr;
}

//---------------------------------------------------------
// basic function
//---------------------------------------------------------
function opendir( $dirname=null )
{
	$this->_dh = null;

	if ( empty($dirname) ) {
		$dirname = $this->_dir_name;
	}

	if ( !$this->check_dirname( $dirname ) ){
		return false;
	}

	$xoops_dir = $this->add_slash_to_tail( XOOPS_ROOT_PATH.'/'.$dirname );

	if ( !is_dir($xoops_dir) ) {
		$this->set_error( "not directory: ".$xoops_dir );
		return false;
	}

	$dh = opendir($xoops_dir);
	if ( !$dh ) {
		$this->set_error( "cannot open directory: ".$xoops_dir );
		return false;
	}

	$this->_dh =& $dh;
	$this->_dir_name = $dirname;
	return true;
}

function closedir()
{
	if ( $this->_dh ) {
		$ret = closedir($this->_dh);
		if ( !$ret ) {
			$this->set_error( "cannot close directory: ".$this->_dir_name );
			return false;	// NG
		}
	}
	return true;
}

function &readdir_array()
{
	$arr = array();
	while ( false !== ($file = readdir($this->_dh)) ) {
		$arr[] = $file;
	}
	return $arr;
}

//---------------------------------------------------------
// utlity
//---------------------------------------------------------
function check_dirname( $dirname )
{
// check directory travers
	if ( preg_match("|\.\./|", $dirname) ) {
		$this->set_error( "illegal directory name: ".$dirname );
		return false;
	}
	return true;
}

function add_slash_to_tail( $dir )
{
	if ( substr($dir, -1, 1) != '/' ) {
		$dir .= '/';
	}
	return $dir;
}

function strip_slash_from_tail( $dir )
{
	if ( substr($dir, -1, 1) == '/' ) {
		$dir = substr($dir, 0, -1);
	}
	return $dir;
}

//---------------------------------------------------------
// error
//---------------------------------------------------------
function get_errors()
{
	return $this->_errors;
}

function set_error( $msg )
{
// array type
	if ( is_array($msg) ) {
		foreach ( $msg as $m ) {
			$this->_errors[] = $m;
		}

// string type
	} else {
		$arr = explode("\n", $msg);
		foreach ( $arr as $m ) {
			$this->_errors[] = $m;
		}
	}
}

//----- class end -----
}

?>