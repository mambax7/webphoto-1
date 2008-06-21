<?php
// $Id: pathinfo.php,v 1.1 2008/06/21 12:22:26 ohwada Exp $

//=========================================================
// webphoto module
// 2008-04-02 K.OHWADA
//=========================================================

if( ! defined( 'XOOPS_TRUST_PATH' ) ) die( 'not permit' ) ;

//=========================================================
// class webphoto_lib_pathinfo
//=========================================================
class webphoto_lib_pathinfo
{
	var $_get_param      = null;
	var $_pathinfo_array = null;

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function webphoto_lib_pathinfo()
{
	$this->_init();
}

function &getInstance()
{
	static $instance;
	if (!isset($instance)) {
		$instance = new webphoto_lib_pathinfo();
	}
	return $instance;
}

//---------------------------------------------------------
// init
//---------------------------------------------------------
function _init()
{
	$get = $_GET;

	$path_info = $this->get_server_path_info();

	if ( $path_info ) {
		$this->_pathinfo_array = $this->str_to_array( $path_info, '/' );
		foreach ( $this->_pathinfo_array as $path )
		{
			$qs = $this->str_to_array( $path, '=' );
			if ( isset( $qs[0] ) && isset( $qs[1] ) ) {
				$get[ $qs[0] ] = $qs[1] ;
			}
		}
	}

	$this->_get_param = $get;
}

//---------------------------------------------------------
// function
//---------------------------------------------------------
function get( $key, $default=null )
{
	$str = isset( $this->_get_param[ $key ] ) ? $this->_get_param[ $key ] : $default;
	return $str;
}

function get_text( $key, $default=null )
{
	return $this->strip_slashes_gpc( $this->get( $key, $default ) );
}

function get_int( $key, $default=0 )
{
	return intval( $this->get( $key, $default ) );
}

function get_float( $key, $default=0 )
{
	return floatval( $this->get( $key, $default ) );
}

function get_server_path_info()
{
	$str = isset($_SERVER["PATH_INFO"]) ? $_SERVER["PATH_INFO"] : null;
	return $str;
}

function get_param()
{
	return $this->_get_param;
}

function get_path( $num )
{
	if ( isset( $this->_pathinfo_array[ $num ] ) ) {
		return  $this->_pathinfo_array[ $num ] ;
	}
	return false;
}

//---------------------------------------------------------
// utlity
//---------------------------------------------------------
function strip_slashes_gpc( $str )
{
	if ( !get_magic_quotes_gpc() ) { return $str; }

	if ( !is_array( $str ) ) {
		return stripslashes( $str );
	}

	$arr = array();
	foreach ( $str as $k => $v ) {
		$arr[ $k ] = stripslashes( $v );
	}
	return $arr;
}

function str_to_array( $str, $pattern )
{
	$arr1 = explode( $pattern, $str );
	$arr2 = array();
	foreach ( $arr1 as $v )
	{
		$v = trim($v);
		if ($v == '') { continue; }
		$arr2[] = $v;
	}
	return $arr2;
}

// --- class end ---
}

?>