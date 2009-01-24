<?php
// $Id: submit.php,v 1.12 2009/01/24 07:10:39 ohwada Exp $

//=========================================================
// webphoto module
// 2008-04-02 K.OHWADA
//=========================================================

//---------------------------------------------------------
// change log
// 2009-01-10 K.OHWADA
// webphoto_photo_action -> webphoto_edit_submit
// 2009-01-04 K.OHWADA
// webphoto_photo_misc_form
// 2008-11-16 K.OHWADA
//_print_form_error()
// 2008-11-08 K.OHWADA
// remove update_init()
// 2008-11-04 K.OHWADA
// BUG: Fatal error in upload.php
// 2008-10-01 K.OHWADA
// webphoto_photo_action
// 2008-08-24 K.OHWADA
// photo_handler -> item_handler
// 2008-08-06 K.OHWADA
// used webphoto_xoops_user
// used update_video_thumb()
// not use msg_class
// 2008-08-05 K.OHWADA
// BUG: cannot preview
// 2008-07-01 K.OHWADA
// added _exec_video()
// used  build_uri_category()
//---------------------------------------------------------

if( ! defined( 'XOOPS_TRUST_PATH' ) ) die( 'not permit' ) ;

//=========================================================
// class webphoto_main_submit
//=========================================================
class webphoto_main_submit extends webphoto_edit_submit
{
	var $_THIS_FCT = 'submit';
	var $_THIS_URL = null;

	var $_TIME_SUCCESS = 1;
	var $_TIME_PENDING = 3;
	var $_TIME_FAILED  = 5;

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function webphoto_main_submit( $dirname , $trust_dirname )
{
	$this->webphoto_edit_submit( $dirname , $trust_dirname );

	$this->_THIS_URL = $this->_MODULE_URL .'/index.php?fct='.$this->_THIS_FCT;

	$this->init_preload();
}

function &getInstance( $dirname , $trust_dirname )
{
	static $instance;
	if (!isset($instance))  {
		$instance = new webphoto_main_submit( $dirname , $trust_dirname );
	}
	return $instance;
}

//---------------------------------------------------------
// main
//---------------------------------------------------------
function check_submit()
{
	$ret = 0;
	$this->_check();

	$action = $this->_get_action();
	switch ( $action ) 
	{
		case 'submit':
			$ret = $this->_submit();
			break;

		case 'video':
			$this->_video();
			exit();
	}

	if ( $ret == _C_WEBPHOTO_RET_VIDEO_FORM ) {
		$this->_form_action = 'form_video_thumb';

	} elseif ( $ret == _C_WEBPHOTO_RET_ERROR ) {
		$this->_form_action = 'form_error';

	} else {
		$this->_form_action = $action;
	}
}

function print_form()
{
	echo $this->build_bread_crumb( 
		$this->get_constant('TITLE_ADDPHOTO'), $this->_THIS_URL );

	switch ( $this->_form_action )
	{
		case 'form_video_thumb':
			$this->_print_form_video();
			break;

		case 'form_error':
			$this->_print_form_error() ;
			break;

		case 'preview' :
			$this->_print_form_preview();
			break;

		default:
			$this->_print_form_default();
			break;
	}
}

function _get_action()
{
	$preview = $this->_post_class->get_post_text( 'preview' );
	$op      = $this->_post_class->get_post_text( 'op' );
	if ( $preview ) {
		return 'preview';
	}
	return $op;
}

//---------------------------------------------------------
// check 
//---------------------------------------------------------
function _check()
{
	$this->get_post_param();

	$ret = $this->submit_check();
	if ( !$ret ) {
		redirect_header( 
			$this->get_redirect_url() , 
			$this->get_redirect_time() ,
			$this->get_redirect_msg()
		) ;
		exit();
	}
}

//---------------------------------------------------------
// submit
//---------------------------------------------------------
function _submit()
{
	if ( ! $this->check_token() ) {
		$this->set_token_error() ;
		return _C_WEBPHOTO_RET_ERROR ;
	}

	$ret = $this->submit_main();
	switch ( $ret )
	{

// video form, error
		case _C_WEBPHOTO_RET_VIDEO_FORM :
		case _C_WEBPHOTO_RET_ERROR :
			return $ret;

// success
		case _C_WEBPHOTO_RET_SUCCESS :
			break;
	}

	$item_row = $this->get_created_row();
	$cat_id   = $item_row['item_cat_id'];

	list( $url, $time, $msg ) = $this->build_redirect( 
		$this->_build_redirect_param( false, $cat_id ) );

	redirect_header( $url, $time, $msg );
	exit();
}

function _check_token_and_redirect()
{
	$this->check_token_and_redirect( $this->_THIS_URL, $this->_TIME_FAILED );
}

function _build_redirect_param( $is_failed, $cat_id )
{
	$param = array(
		'is_failed'   => $is_failed ,
		'is_pending'  => ! $this->_has_superinsert ,
		'url_success' => $this->_build_redirect_url_success( $cat_id ) ,
		'url_pending' => $this->build_uri_operate( 'latest' ) , 
		'url_failed'  => $this->_THIS_URL , 
		'msg_success' => $this->get_constant('SUBMIT_RECEIVED') ,
		'msg_pending' => $this->get_constant('SUBMIT_ALLPENDING') , 
	);
	return $param ;
}

function _build_redirect_url_success( $cat_id )
{
	$param = array(
		'orderby' => 'dated'
	);
	return $this->build_uri_category( $cat_id, $param );
}

//---------------------------------------------------------
// video
//---------------------------------------------------------
function _video()
{
	$this->_check_token_and_redirect();
	$item_row = $this->_get_item_row_or_redirect();

	$ret = $this->video_thumb( $item_row );

	list( $url, $time, $msg ) = $this->build_redirect( 
		$this->_build_redirect_param( !$ret, $item_row['item_cat_id'] ) );

	redirect_header( $url, $time, $msg );
	exit();
}

function _get_item_row_or_redirect()
{
	$item_id  = $this->_post_class->get_post_get_int('item_id') ;
	$item_row = $this->_item_handler->get_row_by_id( $item_id );
	if ( !is_array($item_row) ) {
		redirect_header( $this->_THIS_URL , $this->_TIME_FAILED , 
			$this->get_constant('NOMATCH_PHOTO') ) ;
		exit() ;
	}

	return $item_row ;
}

//---------------------------------------------------------
// preview
//---------------------------------------------------------
function _build_preview_info( $item_row )
{
	if ( $this->is_readable_new_photo() ) {
		$image_info = $this->_preview_new( $item_row );

	} elseif ( $this->is_readable_preview() ) {
		// old preview
		$image_info = $this->_preview_old();

	} else {
		// preview without image
		$image_info = $this->_preview_no_image();
	}

	if ( empty( $item_row['item_title'] ) && $this->_photo_media_name ) {
		$item_row['item_title'] = $this->strip_ext( $this->_photo_media_name );
	}

	return array( $item_row, $image_info );
}

function _print_preview_submit( $item_row, $image_info )
{
	$show_class =& webphoto_show_photo::getInstance( 
		$this->_DIRNAME , $this->_TRUST_DIRNAME );

	$show1 = $show_class->build_photo_show_basic( $item_row, $this->get_tag_name_array() );
	$show2 = array_merge( $show1, $image_info );

	echo $this->build_preview_template( $show2 );
}

function _preview_new( $item_row )
{
// BUG: Fatal error in upload.php
	$ret = $this->upload_fetch_photo( true );

	if ( $ret < 0 ) {
		return $this->_preview_no_image();
	}

	$photo_tmp_name = $this->_photo_tmp_name;

// overwrite preview name
	$this->set_preview_name( str_replace( 
		_C_WEBPHOTO_UPLOADER_PREFIX , 
		_C_WEBPHOTO_UPLOADER_PREFIX_PREV , 
		$photo_tmp_name ) );

	return $this->create_preview_new(
		$this->get_preview_name(), $photo_tmp_name );

}

function _preview_old()
{
	return $this->build_preview( $this->get_preview_name() ) ;
}

function _preview_no_image()
{
	return $this->build_no_image_preview();
}

//---------------------------------------------------------
// print form
//---------------------------------------------------------
function _print_form_video()
{
	$this->print_form_video_thumb( 'submit', $this->get_created_row() );
}

function _print_form_error()
{
	$err = $this->get_format_error( $flag_sanitize=false, $flag_highlight=false );
	echo $this->error_in_box( $err );
	$this->_print_form_preview() ;
}

function _print_form_preview()
{
	$item_row = $this->create_item_row_preview() ;
	list( $item_row, $image_info ) =
		$this->_build_preview_info( $item_row );
	$this->_print_preview_submit( $item_row, $image_info );
	$this->_print_form_submit( $item_row );
}

function _print_form_default()
{
	$item_row = $this->create_item_row_default();
	$options  = $this->_editor_class->build_list_options( true );

	if ( $this->is_show_form_embed() ) {
		$this->_print_form_embed( $item_row );
	}

	if ( $this->is_show_form_editor_option( $options ) ) {
		$this->_print_form_editor( $item_row, $options );
	}

	$this->_print_form_submit( $item_row );
}

function _print_form_submit( $item_row )
{
	$form_class =& webphoto_edit_photo_form::getInstance( 
		$this->_DIRNAME , $this->_TRUST_DIRNAME );
	$form_class->print_form_common( 
		$item_row, $this->build_form_param( 'submit' ) );
}

function _print_form_embed( $item_row )
{
	$form_class =& webphoto_edit_misc_form::getInstance( 
		$this->_DIRNAME , $this->_TRUST_DIRNAME );
	$form_class->print_form_embed( 'submit', $item_row );
}

function _print_form_editor( $item_row, $options )
{
	$form_class =& webphoto_edit_misc_form::getInstance( 
		$this->_DIRNAME , $this->_TRUST_DIRNAME );

	$param = $this->build_form_param( 'submit' );
	$param['options'] = $options ;

	$form_class->print_form_editor( $item_row, $param );
}

// --- class end ---
}

?>