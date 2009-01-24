<?php
// $Id: external_build.php,v 1.1 2009/01/24 07:10:39 ohwada Exp $

//=========================================================
// webphoto module
// 2009-01-10 K.OHWADA
//=========================================================

if( ! defined( 'XOOPS_TRUST_PATH' ) ) die( 'not permit' ) ;

//=========================================================
// class webphoto_edit_external_build
//=========================================================
class webphoto_edit_external_build extends webphoto_edit_base
{
	var $_item_row = null;

	var $_THUMB_EXT_DEFAULT = 'external';

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function webphoto_edit_external_build( $dirname , $trust_dirname )
{
	$this->webphoto_edit_base( $dirname , $trust_dirname );
}

function &getInstance( $dirname , $trust_dirname )
{
	static $instance;
	if (!isset($instance)) {
		$instance = new webphoto_edit_external_build( $dirname , $trust_dirname );
	}
	return $instance;
}

//---------------------------------------------------------
// public 
//---------------------------------------------------------
function is_type( $row )
{
	if ( $row['item_external_url'] ) {
		return true ;
	}
	return false;
}

function build( $row )
{
	$this->_item_row = $row ;

	$item_title          = $row['item_title'] ;
	$item_external_url   = $row['item_external_url'] ;
	$item_external_thumb = $row['item_external_thumb'] ;

	if ( ! $this->is_type( $row ) ) {
		return 1 ;	// no action
	}

	$item_ext = $this->parse_ext( $item_external_url ) ;
	$row['item_ext'] = $item_ext ;

	if ( $this->is_image_ext( $item_ext ) ) {
		$row['item_kind'] = _C_WEBPHOTO_ITEM_KIND_EXTERNAL_IMAGE ;

		if ( empty( $item_external_thumb ) ) {
			$row['item_external_thumb'] = $item_external_url ;
		}

	} else {
		$row['item_kind'] = _C_WEBPHOTO_ITEM_KIND_EXTERNAL_GENERAL ;
	}

	if ( empty($item_title) ) {
		$row['item_title'] = $this->build_title( $row ) ;
	}

	$row = $this->build_row_icon_if_empty( $row, $this->_THUMB_EXT_DEFAULT );

	$this->_item_row = $row ;
	return 0 ;	// OK
}

function build_title( $row )
{
	$file  = $this->parse_url_to_filename( $row['item_external_url'] );
	$title = $this->strip_ext( $file );
	if ( $title ) {
		$title = $this->_NO_TITLE;
	}
	return $title;
}

function get_item_row()
{
	return $this->_item_row ;
}

// --- class end ---
}

?>