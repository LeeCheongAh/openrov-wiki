<?php

/*
 *	GeShi-module for Processing
 *
 *	fjenett - 2006-04-29 - mail@bezier.de
 *	updated for http://wiki.processing.org/
 *
 *	this reads keywords.txt and preferences.txt from
 *	the original Processing dists. if a new version
 *	is released just replace these files with the 
 *	new ones.
 *
 *	http://processing.org/
 *	http://bezier.de/
 */
 

// the processing files.
// location depends on your setup ...
// 

$preferences_file = dirname(__FILE__).'/processing_lang/preferences.txt';
$keywords_file =    dirname(__FILE__).'/processing_lang/keywords.txt';

//require_once( dirname(__FILE__).'/java.php' );
//return;

// geshi default language array
//

$language_data = array (
	'LANG_NAME' => 'Processing',
	'COMMENT_SINGLE' => array(1 => '//'),
	'COMMENT_MULTI' => array('/*' => '*/'),
    'COMMENT_REGEXP' => array(
        //Import and Package directives (Basic Support only)
        2 => '/(?:(?<=import[\\n\\s])|(?<=package[\\n\\s]))[\\n\\s]*([a-zA-Z0-9_]+\\.)*([a-zA-Z0-9_]+|\*)(?=[\n\s;])/i'),
	'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
	'QUOTEMARKS' => array("'", '"'),
	'ESCAPE_CHAR' => '\\',
	// KEYWORDS are added further down
	// SYMBOLS are added further down
	'CASE_SENSITIVE' => array(
		GESHI_COMMENTS => false,
		1 => true, /*  KEYWORDS1 */
		2 => true, /*  KEYWORDS2 */
		3 => true, /*  KEYWORDS3 */
		4 => true, /*  LITERALS1 */
                5 => true  /*  LITERALS2 */
		),
	// more STYLES are added further down
	'STYLES' => array(
		'ESCAPE_CHAR' => array(
			0 => 'color: #993300;'
			),
		'BRACKETS' => array(
			0 => 'color: #000000;'
			),
		'STRINGS' => array(
			0 => 'color: #993300;'
			),
		'NUMBERS' => array(
			0 => 'color: #000000;'
			),
		'METHODS' => array(
			1 => 'color: #000000;',
			2 => 'color: #000000;'
			),
		'SYMBOLS' => array(
			0 => 'color: #000000;'
			),
		'SCRIPT' => array(
			),
		'REGEXPS' => array(
			)
		),
	'URLS' => array(
		1 => 'http://processing.org/reference/{FNAME}.html',
		2 => 'http://processing.org/reference/{FNAME}_.html',
		3 => 'http://processing.org/reference/{FNAME}_.html',
		4 => '',
		5 => 'http://processing.org/reference/{FNAME}.html'
		),
	'OOLANG' => true,
	'OBJECT_SPLITTERS' => array(
		1 => '.'
		),
	'REGEXPS' => array(
		),
	'STRICT_MODE_APPLIES' => GESHI_NEVER,
	'SCRIPT_DELIMITERS' => array(
		),
	'HIGHLIGHT_STRICT_BLOCK' => array(
		)
);



// read & parse the new preferences.txt file.
// will hook in the default font & color.
//

if ( file_exists($preferences_file) 
	 && is_readable($preferences_file) )
{
	$preferences_raw = @file( $preferences_file );
	
	foreach ( $preferences_raw as $p_line )
	{
		if ( preg_match( '@^editor\.[^.0-9]+[0-9]+.style@i', $p_line ) )
		{
			$p_pair = 	 explode( '=', $p_line );
			
			$p_key = 	 preg_replace( '@^editor\.([^.0-9]+)[0-9]+.style@i', '\1', $p_pair[0] );
			$p_key = 	 strtoupper( $p_key ).'S';
			
			$p_key_num = preg_replace( '@^editor\.[^.0-9]+([0-9]+).style@i', '\1', $p_pair[0] );
			$p_key_num += ($p_key == 'LITERALS' ? 3 : 0);
			
			$p_key =	 ( $p_key == 'LITERALS' ? 'KEYWORDS' : $p_key );
			
			$styles = 	 explode(',', trim($p_pair[1]) );
			$styles = 	 'color: '.$styles[0].';'.'  '.
						 ($styles[1] == 'plain' ? '' : ''); //'font-weight: '.$styles[1].';');
			
			$language_data['STYLES'][$p_key][$p_key_num] = $styles;
			
			if ( $p_key == 'COMMENTS' )
				$language_data['STYLES'][$p_key]['MULTI'] = $styles;
		}
	}
}


# this was true 2006, today? 2010?
# KEYWORD1 specifies datatypes
# KEYWORD2 specifies methods and functions
# KEYWORD3 ...
# LITERAL1 specifies constants
# LITERAL2 specifies buildin vars

$language_data['KEYWORDS'] = array( 1 => array(), 2 => array(), 3 => array(), 4 => array(), 5 => array());
$language_data['SYMBOLS']  = array();

// read & parse the new keywords.txt file.
//

if ( file_exists($keywords_file)
	 && is_readable($keywords_file) )
{
	$keywords_raw = @file( $keywords_file );
	
	if ( is_array($keywords_raw) )
	{
		foreach( $keywords_raw as $keyword_line )
		{
			$keyword_line = trim($keyword_line);
			
			if ( $keyword_line[0] !== '#' && $keyword_line[0] !== '?'  && !empty($keyword_line) )
			{
				$k_line_arr = explode( "\t", $keyword_line );
				
				switch( $k_line_arr[1] )
				{
					case 'KEYWORD1':
						array_push( $language_data['KEYWORDS'][1], $k_line_arr[0] );
						break;
					
					case 'KEYWORD2':
						array_push( $language_data['KEYWORDS'][2], $k_line_arr[0] );
						break;
						
					case 'KEYWORD3':
						array_push( $language_data['KEYWORDS'][3], $k_line_arr[0] );
						break;
						
					case 'LITERAL1':
						array_push( $language_data['KEYWORDS'][4], $k_line_arr[0] );
						break;
						
					case 'LITERAL2':
						array_push( $language_data['KEYWORDS'][5], $k_line_arr[0] );
						break;
						
					case '':
					default:
						array_push( $language_data['SYMBOLS'], $k_line_arr[0] );
						break;
						
				}
			}
		}
	}
}

?>