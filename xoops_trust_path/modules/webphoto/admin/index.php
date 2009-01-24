<?php
// $Id: index.php,v 1.6 2009/01/24 07:10:39 ohwada Exp $

//=========================================================
// webphoto module
// 2008-04-02 K.OHWADA
//=========================================================

//---------------------------------------------------------
// change log
// 2000-01-10 K.OHWADA
// xpdf.php
// 2008-11-16 K.OHWADA
// server_info.php
// 2008-11-08 K.OHWADA
// workdir.php
// 2008-10-01 K.OHWADA
// added player_handler.php
// 2008-08-24 K.OHWADA
// added photo_handler.php
//---------------------------------------------------------

if( ! defined( 'WEBPHOTO_TRUST_PATH' ) ) die( 'not permit' ) ;

//---------------------------------------------------------
// webphoto files
//---------------------------------------------------------
webphoto_include_once( 'admin/header.php' );
webphoto_include_once( 'class/inc/workdir.php' );
webphoto_include_once( 'class/lib/server_info.php' );
webphoto_include_once( 'class/lib/gd.php' );
webphoto_include_once( 'class/lib/imagemagick.php' );
webphoto_include_once( 'class/lib/netpbm.php' );
webphoto_include_once( 'class/lib/ffmpeg.php' );
webphoto_include_once( 'class/lib/xpdf.php' );
webphoto_include_once( 'class/handler/player_handler.php' );
webphoto_include_once( 'class/handler/photo_handler.php' );
webphoto_include_once( 'class/admin/checkconfigs.php' );
webphoto_include_once( 'class/admin/update_check.php' );
webphoto_include_once( 'class/admin/index.php' );

//=========================================================
// main
//=========================================================
$manager =& webphoto_admin_index::getInstance( WEBPHOTO_DIRNAME , WEBPHOTO_TRUST_DIRNAME );
$manager->main();
exit();

?>