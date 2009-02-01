<?php
// $Id: gmap_location.php,v 1.6 2009/02/01 23:58:44 ohwada Exp $

//=========================================================
// webphoto module
// 2008-04-02 K.OHWADA
//=========================================================

//---------------------------------------------------------
// change log
// 2009-01-25 K.OHWADA
// get_gmap_center()
// 2008-08-24 K.OHWADA
// photo_handler -> item_handler
// 2008-07-01 K.OHWADA
// added _build_list_location()
//---------------------------------------------------------

if( ! defined( 'XOOPS_TRUST_PATH' ) ) die( 'not permit' ) ;

//---------------------------------------------------------
// myself:     default
// new window: mode=opener
// inline:     mode=parent
//---------------------------------------------------------

//=========================================================
// class webphoto_main_gmap_location
//=========================================================
class webphoto_main_gmap_location extends webphoto_base_this
{
	var $_gmap_class;
	var $_multibyte_class;

	var $_TEMPLATE     = null;
	var $_GMAP_HEIGHT  = 300;
	var $_OPNER_MODE   = 'parent';

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function webphoto_main_gmap_location( $dirname , $trust_dirname )
{
	$this->webphoto_base_this( $dirname , $trust_dirname );

	$this->_TEMPLATE = 'db:'. $dirname .'_main_gmap_location.html';

	$this->_gmap_class      =& webphoto_gmap::getInstance( $dirname , $trust_dirname );
	$this->_multibyte_class =& webphoto_lib_multibyte::getInstance();
}

function &getInstance( $dirname , $trust_dirname )
{
	static $instance;
	if (!isset($instance)) {
		$instance = new webphoto_main_gmap_location( $dirname , $trust_dirname );
	}
	return $instance;
}

//---------------------------------------------------------
// main
//---------------------------------------------------------
function main()
{
	$cfg_gmap_apikey = $this->get_config_by_name( 'gmap_apikey' );

	if ( $cfg_gmap_apikey ) {
		$this->_assign_template( $cfg_gmap_apikey );
	} else {
		$this->_show_error();
	}
}

function _assign_template( $cfg_gmap_apikey )
{

	$flag_location = false;
	$show_gmap = false;
	$gmap_list = null;

	list( $code, $latitude, $longitude, $zoom ) = $this->_get_center() ;
	if ( $code > 0 ) {
		$flag_location  = true;
		$gmap_latitude  = $latitude;
		$gmap_longitude = $longitude;
		$gmap_zoom      = $zoom;
	}

// when item
	if ( $code == 2 ) {
		list( $show_gmap, $gmap_list ) = $this->_build_list_location();
	}

	$this->_http_output( 'pass' );
	header ('Content-Type:text/html; charset=UTF-8');

	$tpl = new XoopsTpl();

	$tpl->assign('xoops_dirname',      $this->_DIRNAME );
	$tpl->assign('mydirname',          $this->_DIRNAME );
	$tpl->assign('gmap_opener_mode',   $this->_OPNER_MODE );
	$tpl->assign('gmap_height',        $this->_GMAP_HEIGHT );
	$tpl->assign('gmap_apikey',        $cfg_gmap_apikey );

	if ( $flag_location ) {
		$tpl->assign('gmap_latitude',   $gmap_latitude );
		$tpl->assign('gmap_longitude',  $gmap_longitude );
		$tpl->assign('gmap_zoom',       $gmap_zoom );
	}

	$tpl->assign('show_gmap',    $show_gmap );
	$tpl->assign('gmap_list',    $gmap_list );

	$tpl->assign('gmap_lang_latitude',       $this->_constant('GMAP_LATITUDE') );
	$tpl->assign('gmap_lang_longitude',      $this->_constant('GMAP_LONGITUDE') );
	$tpl->assign('gmap_lang_zoom',           $this->_constant('GMAP_ZOOM') );
	$tpl->assign('gmap_lang_no_match_place', $this->_constant('GMAP_NO_MATCH_PLACE') );
	$tpl->assign('gmap_lang_not_compatible', $this->_constant('GMAP_NOT_COMPATIBLE') );

	$tpl->assign('lang_title',            $this->_constant('TITLE_GET_LOCATION') );
	$tpl->assign('lang_address',          $this->_constant('GMAP_ADDRESS') );
	$tpl->assign('lang_get_location',     $this->_constant('GMAP_GET_LOCATION') );
	$tpl->assign('lang_search_list',      $this->_constant('GMAP_SEARCH_LIST') );
	$tpl->assign('lang_current_location', $this->_constant('GMAP_CURRENT_LOCATION') );
	$tpl->assign('lang_current_address',  $this->_constant('GMAP_CURRENT_ADDRESS') );
	$tpl->assign('lang_js_invalid',       $this->_constant('JS_INVALID') );
	$tpl->assign('lang_search',           $this->_constant('SR_SEARCH') );

	$tpl->display( $this->_TEMPLATE );
}

function _get_center()
{
	$get_photo_id   = $this->_post_class->get_get_int('photo_id');
	$get_cat_id     = $this->_post_class->get_get_int('cat_id');
	$get_block_id   = $this->_post_class->get_get_int('block_id');
	$get_option_num = $this->_post_class->get_get_int('option_num');

// when block
	if ( $get_block_id > 0 ) {
		$options = $this->_xoops_class->get_block_options_by_bid( $get_block_id );
		if ( is_array($options) ) {
			$latitude  = floatval($options[ $get_option_num ]) ;
			$longitude = floatval($options[ $get_option_num + 1 ]) ;
			$zoom      = intval(  $options[ $get_option_num + 2 ]) ;
			if ( $this->_gmap_class->exist_gmap( $latitude, $longitude, $zoom ) ) {
				$code = 4 ;	// block
				return array( $code, $latitude, $longitude, $zoom ) ;
			}
		}
	}

	return $this->_gmap_class->get_gmap_center( $get_photo_id, $get_cat_id );
}

function _build_list_location()
{
	$get_photo_id = $this->_post_class->get_get_int('photo_id');

	$show_gmap = false;
	$gmap_list = null;

	$item_row = $this->_item_handler->get_cached_row_by_id( $get_photo_id );
	if ( !is_array($item_row) || !count($item_row) ) {
		return array( $show_gmap, $gmap_list );
	}

	$list = $this->_gmap_class->build_list_location( $item_row );
	if ( !is_array($list) || !count($list) ) {
		return array( $show_gmap, $gmap_list );
	}

// convert to UTF-8
	$gmap_list = array();
	foreach ( $list as $loc ) 
	{
		$temp         = $loc;
		$temp['info'] = $this->_utf8( $loc['info'] );
		$gmap_list[]  = $temp ;
	}

	if ( is_array($gmap_list) && count($gmap_list)) {
		$show_gmap = true;
	}

	return array( $show_gmap, $gmap_list );
}

function _constant( $name )
{
	return $this->_utf8( $this->get_constant( $name ) );
}

//---------------------------------------------------------
// show error
//---------------------------------------------------------
function _show_error()
{
	header ('Content-Type:text/html; charset='._CHARSET);

// --- raw HTML begin ---
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
<meta http-equiv="content-type" content="text/html; charset='. _CHARSET .'"/>
<title>weblinks - <?php echo $this->get_constant('TITLE_GET_LOCATION') ; ?></title>
</head>
<body>
<h3><?php echo $this->get_constant('TITLE_GET_LOCATION') ; ?></h3>
<h4 style="color: #ff0000;">not set google map api key</h4>
</body>
</html>
<?php
// --- raw HTML end ---
}

//---------------------------------------------------------
// multibyte
//---------------------------------------------------------
function _http_output( $encoding )
{
	return $this->_multibyte_class->m_mb_http_output( $encoding );
}

function _utf8( $str )
{
	return $this->_multibyte_class->convert_to_utf8( $str );
}

// --- class end ---
}

?>