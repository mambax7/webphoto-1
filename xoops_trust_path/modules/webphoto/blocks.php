<?php
// $Id: blocks.php,v 1.7 2009/02/01 09:04:29 ohwada Exp $

//=========================================================
// webphoto module
// 2008-04-02 K.OHWADA
//=========================================================

//---------------------------------------------------------
// change log
// 2009-01-25 K.OHWADA
// gmap_block.php
// 2008-12-12 K.OHWADA
// public.php
// 2008-11-29 K.OHWADA
// auto_publish.php etc
// 2008-07-01 K.OHWADA
// use webphoto_include_once_trust()
//---------------------------------------------------------

//---------------------------------------------------------
// $MY_DIRNAME WEBPHOTO_TRUST_PATH are set by caller
//---------------------------------------------------------

if ( ! defined( 'WEBPHOTO_TRUST_PATH' ) ) die( 'not permit' ) ;

//---------------------------------------------------------
// xoops system files
//---------------------------------------------------------
include_once XOOPS_ROOT_PATH.'/class/template.php' ;
include_once XOOPS_ROOT_PATH.'/class/xoopstree.php' ;

//---------------------------------------------------------
// webphoto files
//---------------------------------------------------------
include_once WEBPHOTO_TRUST_PATH.'/class/d3/optional.php';
include_once WEBPHOTO_TRUST_PATH.'/include/optional.php';

webphoto_include_once( 'preload/debug.php',              $MY_DIRNAME );
webphoto_include_once( 'class/d3/preload.php',           $MY_DIRNAME );
webphoto_include_once( 'include/constants.php',          $MY_DIRNAME );
webphoto_include_once( 'include/gmap_api.php',           $MY_DIRNAME );
webphoto_include_once( 'class/xoops/base.php',           $MY_DIRNAME );
webphoto_include_once( 'class/lib/multibyte.php',        $MY_DIRNAME );
webphoto_include_once( 'class/lib/cloud.php',            $MY_DIRNAME );
webphoto_include_once( 'class/lib/utility.php',          $MY_DIRNAME );
webphoto_include_once( 'class/inc/xoops_header.php',     $MY_DIRNAME );
webphoto_include_once( 'class/inc/handler.php',          $MY_DIRNAME );
webphoto_include_once( 'class/inc/config.php',           $MY_DIRNAME );
webphoto_include_once( 'class/inc/group_permission.php', $MY_DIRNAME );
webphoto_include_once( 'class/inc/catlist.php',          $MY_DIRNAME );
webphoto_include_once( 'class/inc/tagcloud.php',         $MY_DIRNAME );
webphoto_include_once( 'class/inc/public.php',           $MY_DIRNAME );
webphoto_include_once( 'class/inc/auto_publish.php',     $MY_DIRNAME );
webphoto_include_once( 'class/inc/uri.php',              $MY_DIRNAME );
webphoto_include_once( 'class/inc/gmap_block.php',       $MY_DIRNAME );
webphoto_include_once( 'class/inc/gmap_info.php',        $MY_DIRNAME );
webphoto_include_once( 'class/inc/blocks.php',           $MY_DIRNAME );
webphoto_include_once( 'blocks/functions.php',           $MY_DIRNAME );

webphoto_include_language( 'blocks.php', $MY_DIRNAME );

webphoto_include_once_preload_trust();

?>