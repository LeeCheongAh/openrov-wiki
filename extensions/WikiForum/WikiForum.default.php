<?php
if(!isset($wikiconf["forumname"])) 				$wikiconf["forumname"] 				= "WikiForum";
if(!isset($wikiconf["link_in_toolbox"])) 		$wikiconf["link_in_toolbox"] 		= true;
if(!isset($wikiconf["wikiforum_tag"])) 			$wikiconf["wikiforum_tag"] 			= true;
if(!isset($wikiconf["anonymous_allowed"])) 		$wikiconf["anonymous_allowed"] 		= false;
if(!isset($wikiconf["max_threads_per_page"])) 	$wikiconf["max_threads_per_page"] 	= 20;
if(!isset($wikiconf["max_comments_per_page"])) 	$wikiconf["max_comments_per_page"] 	= 10;
if(!isset($wikiconf["daydefinition_new"])) 		$wikiconf["daydefinition_new"] 		= 3;
if(!isset($wikiconf["showlicense"])) 			$wikiconf["showlicense"] 			= false;
if(!isset($wikiconf["showstats"])) 				$wikiconf["showstats"] 				= true;

// definition where the form is located and what addtional action parameters shall be used.
$wikiconf["main_link"] 			= $wgScriptPath."/index".$wgScriptExtension;
$wikiconf["extension_folder"] 	= $wgScriptPath."/extensions/WikiForum/";
$wikiconf["action_string"] 		= "&action=purge";

// additional check what kind of url will be used on your wiki page
if($wgUsePathInfo==false) 	$wikiconf["action_string"] 	.= "&title=Special:WikiForum";
	else 					$wikiconf["main_link"] 		.= "/Special:WikiForum";
?>