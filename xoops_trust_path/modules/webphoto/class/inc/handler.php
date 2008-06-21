<?php
// $Id: handler.php,v 1.1 2008/06/21 12:22:26 ohwada Exp $

//=========================================================
// webphoto module
// 2008-04-02 K.OHWADA
//=========================================================

if( ! defined( 'XOOPS_TRUST_PATH' ) ) die( 'not permit' ) ;

//=========================================================
// class webphoto_inc_handler
//=========================================================
class webphoto_inc_handler
{
	var $_db;
	var $_db_error;

	var $_DIRNAME;
	var $_MODULE_URL;
	var $_MODULE_DIR;

	var $_NORMAL_EXTS;

	var $_DEBUG_SQL   = false;
	var $_DEBUG_ERROR = false;

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function webphoto_inc_handler()
{
	$this->_db =& Database::getInstance();
}

function &getInstance()
{
	static $instance;
	if (!isset($instance)) {
		$instance = new webphoto_inc_handler();
	}
	return $instance;
}

function init_handler( $dirname )
{
	$this->_DIRNAME = $dirname;
	$this->_MODULE_URL = XOOPS_URL       .'/modules/'.$dirname;
	$this->_MODULE_DIR = XOOPS_ROOT_PATH .'/modules/'.$dirname;
}

//---------------------------------------------------------
// database handler
//---------------------------------------------------------
function get_count_by_sql( $sql )
{
	return intval( $this->get_first_row_by_sql( $sql ) );
}

function get_first_row_by_sql( $sql )
{
	$res = $this->query($sql);
	if ( !$res ) { return false; }

	$row = $this->_db->fetchRow( $res );
	if ( isset( $row[0] ) ) {
		return $row[0];
	}

	return false;
}

function get_row_by_sql( $sql )
{
	$res = $this->query( $sql );
	if ( !$res ) { return false; }

	$row = $this->_db->fetchArray($res);
	return $row; 
}

function get_rows_by_sql( $sql, $limit=0, $offset=0, $key=null )
{
	$arr = array();

	$res = $this->query( $sql, $limit, $offset );
	if ( !$res ) { return false; }

	while ( $row = $this->_db->fetchArray($res) ) 
	{
		if ( $key && isset( $row[ $key ] ) ) {
			$arr[ $row[ $key ] ] = $row;
		} else {
			$arr[] = $row;
		}
	}
	return $arr; 
}

function query( $sql, $limit=0, $offset=0 )
{
	if ( $this->_DEBUG_SQL ) {
		echo $this->sanitize( $sql ) .': limit='. $limit .' :offset='. $offset. "<br />\n";
	}

	$res = $this->_db->query( $sql, intval($limit), intval($offset) );
	if ( !$res  ) {
		$this->_db_error = $this->_db->error();
		if ( $this->_DEBUG_ERROR ) {
			echo $this->highlight( $this->_db_error )."<br />\n";
		}
	}

	return $res;
}

function prefix_dirname( $name )
{
	return $this->_db->prefix( $this->_DIRNAME.'_'.$name ) ;
}

function quote( $str )
{
	$str = "'". addslashes($str) ."'";
	return $str;
}

function sanitize( $str )
{
	return htmlspecialchars( $str, ENT_QUOTES );
}

function highlight( $str )
{
	$val = '<span style="color:#ff0000;">'. $str .'</span>';
	return $val;
}

function get_db_error( $flag_sanitize=true, $flag_highlight=true )
{
	$str = $this->_db_error;
	if ( $flag_sanitize ) {
		$str = $this->sanitize( $str );
	}
	if ( $flag_highlight ) {
		$str = $this->highlight( $str );
	}
	return $str;
}

//---------------------------------------------------------
// utility
//---------------------------------------------------------
function is_normal_ext( $ext )
{
	if ( in_array( strtolower( $ext ) , $this->_NORMAL_EXTS ) ) {
		return true;
	}
	return false;
}

function set_normal_exts( $val )
{
	if ( is_array($val) ) {
		$this->_NORMAL_EXTS = $val;
	} else {
		$this->_NORMAL_EXTS = explode( '|', $val );
	}
}

// --- class end ---
}

?>