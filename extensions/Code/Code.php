<?php
if( !defined( 'MEDIAWIKI' ) ) {
        echo( "This is an extension to the MediaWiki package and cannot be run standalone.\n" );
        die( -1 );
}
$wgExtensionCredits['validextensionclass'][] = array(
        'path'           => __FILE__,
        'name'           => 'Code',
        'version'        => '0.9',
        'author'         => 'Paul Grinberg',
        'url'            => 'http://www.mediawiki.org/wiki/Extension:Code',
        'descriptionmsg' => 'Syntax Highlighting using GeSHi',
        'description'    => 'Adds <nowiki><Code></nowiki> tag'
);
 
$wgHooks['ParserFirstCallInit'][] = 'efCodeExtensionInit';
 
function efCodeExtensionInit(Parser &$parser) {
    $parser->setHook( "Code", "efCodeExtensionRenderCode" );
    return true;
}
 
function efCodeExtensionRenderCode($input, $argv, $parser) {
    global $wgShowHideDivi, $wgOut;
 
    // default values
    $language = 'text';
    $showLineNumbers = false;
    $showDownloadLink = false;
    $source = $input;
    $tabwidth = 4;
 
    foreach ($argv as $key => $value) {
        switch ($key) {
            case 'lang':
                $language = $value; 
                break;  
            case 'linenumbers':
                $showLineNumbers = true; 
                break;  
            case 'tabwidth':
                $tabwidth = $value; 
                break;  
            case 'download':
                $showDownloadLink = true; 
                break;  
            case 'fileurl':
                $html = $parser->unstrip($parser->recursiveTagParse($value),$parser->mStripState);
                $i = preg_match('/<a.*?>(.*?)<\/a>/', $html, $matches);
                $url = $matches[1];
                //print("URL is '$url'");
                #$source = "file_get_contents disabled! Contact your wiki admin with questions.";
                $source =  file_get_contents($url);
                break;  
            default :
                wfDebug( __METHOD__.": Requested '$key ==> $value'\n" );
                break;  
        }
    }
    if (!defined('GESHI_VERSION')) {
        include('geshi/geshi.php'); // include only once or else wiki dies
    }
    $geshi = new GeSHi($source, $language);
    $error = $geshi->error();           // die gracefully if errors found
    if ($error) {
        return "Code Extension Error: $error";
    }
    $geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS); // always display line numbers
    $geshi->set_tab_width($tabwidth);
    $code = $geshi->parse_code();
    $code_pieces = preg_split('/\<ol/', $code );
 
    $output = '';
    $ol_tag = '<ol';
    if (!$showLineNumbers) {
        // if not asked to show line numbers, then we should hide them. This is the preferred method
        // because this allows for a means of a block of code in the middle of a numbered list
        $output .= "<style type='text/css'><!-- ol.codelinenumbers { list-style: none; margin-left: 0; padding-left: 0em;} --></style>";
        $ol_tag = "<ol class='codelinenumbers'";
    }
    $output .= $code_pieces[0];
    if ($showDownloadLink) {
        $output .= "<a href=\"javascript:win3 = window.open('', 'code', 'width=320,height=210,scrollbars=yes');win3.document.writeln('$source');\"  style=\"float:right\">Download Code</a>\n";
    }
    $output .= $ol_tag . $code_pieces[1];
 
    return $output;
}