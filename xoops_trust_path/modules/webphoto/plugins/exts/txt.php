<?php
// $Id: txt.php,v 1.1 2009/01/24 07:10:39 ohwada Exp $

//=========================================================
// webphoto module
// 2009-01-10 K.OHWADA
//=========================================================

if ( ! defined( 'XOOPS_TRUST_PATH' ) ) die( 'not permit' ) ;

//=========================================================
// class webphoto_ext_txt
//=========================================================
class webphoto_ext_txt extends webphoto_ext_base
{
	var $_TXT_EXTS = array('txt');

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function webphoto_ext_txt( $dirname )
{
	$this->webphoto_ext_base( $dirname );
}

//---------------------------------------------------------
// check ext
//---------------------------------------------------------
function is_ext( $ext )
{
	return $this->is_txt_ext( $ext );
}

function is_txt_ext( $ext )
{
	return $this->is_ext_in_array( $ext, $this->_TXT_EXTS );
}

//---------------------------------------------------------
// create image
//---------------------------------------------------------

//---------------------------------------------------------
// text content
//---------------------------------------------------------
function get_text_content( $param )
{
	$file_cont = isset($param['file_cont'])  ? $param['file_cont'] : null ;

	if ( !is_file($file_cont) ) {
		return false;
	}

	$text = file_get_contents( $file_cont );

	$encoding = $this->_multibyte_class->m_mb_detect_encoding( $text );
	if ( $encoding) {
		$text = $this->_multibyte_class->convert_encoding( $text, _CHARSET, $encoding );
	}

	return $text;
}

// --- class end ---
}

?>