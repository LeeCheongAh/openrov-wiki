
<?php
 
#####################################################################################
# Extension changes the look of the SignUp page. New user confirms by clicking 
# the submit button an agreement with Creative Commons licencing.
######################################################################################

if (!defined('MEDIAWIKI')) {
echo <<<EOT
        To install my extension, put the following line in LocalSettings.php:
        require_once( "\$IP/extensions/CCAgreement/CCAgreement.php" );
EOT;
exit( 1 );
}
 
$wgExtensionCredits['specialpage'][] = array(
        'name' => 'CCAgreement',
        'author' => 'Josef Martiňák',
        'url' => "http://www.mediawiki.org/wiki/Extension:CCAgreement",
        'description' => 'Agreement with Creative Commons licencing',
        'version' => '0.1',
);
 
 
$dir = dirname(__FILE__) . '/';
 
 
$wgExtensionMessagesFiles['CCAgreement'] = $dir . './CCAgreement.i18n.php';
$wgHooks['BeforePageDisplay'][] = 'AddLicencing';
 
 
function AddLicencing(&$out) { 
        $context = RequestContext::getMain();
        $title = $context->getTitle();
        if($title->isSpecialPage() && SpecialPage::resolveAlias($title->getBaseText()) == "Userlogin"){
                $query = $context->getRequest()->getQueryValues();
                if(!empty($query["type"]) && $query["type"] == "signup"){
 
                        // Change submit button text and position
                        $out->mBodytext = preg_replace("/(id=\"wpCreateaccount\"[^>]*)value=\"[^\"]*\"/","$1value=\"".$context->msg('mwe-cca-submit-button')."\"",$out->mBodytext);
                        $out->mBodytext = preg_replace("/(?=[^\"]*\"mw-submit)<td><\/td>/","",$out->mBodytext);
                        $out->mBodytext = preg_replace("/<td class=\"mw-submit\">/","<td class=\"mw-submit\" colspan=\"2\">",$out->mBodytext);
 
                        // Append the licence iframe and text message
                        $tmp = "<tr><td colspan=\"2\">\n<br/>";
                        if($context->getLanguage()->getCode() == "cs") $tmp .= "<iframe src= \"http://creativecommons.org/licenses/by/3.0/cz/\" width=\"800\" height=\"1050\"></iframe>\n";
                        else $tmp .= "<iframe src= \"http://creativecommons.org/licenses/by/3.0/\" width=\"800\" height=\"1020\" ></iframe>\n";
                        $tmp .= "<br/><br/>".$context->msg('mwe-cca-agreement')."<br/><br/>\n";
                        $tmp .= "</td>\n</tr>\n";
                        $out->mBodytext = preg_replace("/(?=[^\"]*\"mw-submit)<tr>/",$tmp."<tr>",$out->mBodytext);
                }
        }
        return true;
} 
 
 
?>
