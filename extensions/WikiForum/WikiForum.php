<?php
if(!defined('MEDIAWIKI'))
{
   echo("This is an extension to the MediaWiki package and cannot be run standalone.\n");
   die();
}

/**
 * Extension: WikiForum
 * Created: 02 December 2010
 * Author: Michael Chlebek
 * Version: 1.1
 * Copyright (C) 2010 Unidentify Studios
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *  
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *  
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * I would ask you to show the infoline with version and creators
 * info. If you do not want to show this line disable it by using
 * $wikiconf['disableinfoline']=true; in the WikiForum.config.php.
 * Additionally you can display this license by using
 * $wikiconf['showlicense']=true; in the WikiForum.config.php.
 *
 * Add the following lines to LocalSettings.php:
 *    require_once('extensions/WikiForum/WikiForum.php');
 **/

require_once("WikiForum.default.php");
if(file_exists(dirname(__FILE__) . "/WikiForum.config.php")) 
require_once("WikiForum.config.php");
require_once("wrapper/MediaWikiDataBaseWrapper.php");
require_once("wrapper/MediaWikiRequestWrapper.php");
require_once("WikiForumClass.php");
require_once("WikiForumGui.php");
require_once("WikiForumHelperClass.php");
require_once("WikiForumInstall.php");
	
// WikiForum Version 1.2

$gc_gui 	= new WikiForumGui;
$gc_helper 	= new WikiForumHelper;
$gs_version = "1.31";

$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'WikiForum',
	'author' => 'Michael Chlebek',
	'version' => $gs_version,
	'url' => 'http://www.mediawiki.org/wiki/Extension:WikiForum',
	'description' => 'Forum extension for Mediawiki',
	'descriptionmsg' => 'Forum extension for Mediawiki'
);
	 
$wgExtensionFunctions[] = "wfExtensionSpecialWikiForum";
$wgExtensionMessagesFiles['WikiForumMsg'] = dirname(__FILE__) . '/WikiForum.i18n.php';

if($wikiconf['wikiforum_tag']==true)
{
	$wgHooks['ParserFirstCallInit'][] = 'efWikiForumTags';
}

if($wikiconf['link_in_toolbox']==true)
{
	$wgHooks['SkinTemplateBuildNavUrlsNav_urlsAfterPermalink'][] = 'wfSpecialWikiNav';
	$wgHooks['SkinTemplateToolboxEnd'][] = 'wfSpecialWikiToolbox';
}
 
function wfExtensionSpecialWikiForum() {
    global $wgMessageCache;
    require_once('includes/SpecialPage.php');
    SpecialPage::addPage( new SpecialPage('WikiForum'));
	return true;
}	

function efWikiForumTags( &$parser ) 
{
	$parser->setHook("WikiForumList", "WikiForumList");
	$parser->setHook("WikiForumThread", "WikiForumThread");
	return true;
}

function wfSpecialWikiNav( &$skintemplate, &$nav_urls, &$oldid, &$revid ) {
	wfLoadExtensionMessages('WikiForum');
	$nav_urls['wikiforum'] = array(
		'text' => wfMsg( 'wikiforum' ),
		'href' => $skintemplate->makeSpecialUrl('WikiForum', "page=" . wfUrlencode( "{$skintemplate->thispage}" ))
	);
	return true;
}

function wfSpecialWikiToolbox( &$skin ) {
	wfLoadExtensionMessages( 'WikiForum' );
	if(isset($skin->data['nav_urls']['wikiforum']))
	{
		if($skin->data['nav_urls']['wikiforum']['href'] == '') 
		{
			echo'<li id="t-iswf">'.$skin->msg('wikiforum').'</li>';
		} else {
			echo'<li id="t-wf"><a href="'.htmlspecialchars($skin->data['nav_urls']['wikiforum']['href']).'">';
			echo $skin->msg('wikiforum');
			echo'</a></li>';
		}
	}
	return true;
}

function WikiForumList($input, $args) 
{
	global $wgOut, $wgUser, $wgLang, $wgContLang;
	global $wikiconf;
	global $gc_gui, $gc_helper;
	
	$sk 	=& $wgUser->getSkin();
	$dbr	= new UniDatabase(DB_SLAVE);
	$c_for 	= new WikiForumClass;
	
	//if(!$args['type'] || !in_array($args['type'], array('last','view','comment','new'))) $args['type'] = 'last';	//not implemented yet
	//$args['type'] = 'last';
	if(!$args['num']) $args['num'] 	= '5';
		
	$sqlThreads 	= $dbr->query('SELECT t.*, f.pkForum, f.Forum_name, c.pkCategory, c.Category_name, u.user_name 
									FROM wikiforum_forums as f, wikiforum_category as c, wikiforum_threads as t
									LEFT JOIN '.$dbr->tableName('user').' as u ON u.user_id = t.PostedBy 
									WHERE f.Deleted = 0 AND c.Deleted = 0 AND t.Deleted = 0 AND fkCategory = pkCategory AND pkForum = fkForum
									ORDER BY t.lastpost_time DESC LIMIT 0, '.$args['num']);
									
	$output = '<link rel="stylesheet" href="'.$wikiconf['extension_folder'].'styles.css" />';
	$output .= $gc_gui->getMainPageHeader('Newly Forum Updates',wfMsg('wikiforum-answers'),wfMsg('wikiforum-calls'),wfMsg('wikiforum-lastcomment'));

	
	while ($thread = $dbr->fetchObject($sqlThreads))
	{
		
		$icon = $c_for->getThreadIcon($thread->Posted, $thread->Closed, $thread->Sticky);
				
		if($thread->num_answers>0) $lastpost = $wgLang->timeanddate($thread->lastpost_time).'<br/>by '.$gc_helper->getUserLinkById($thread->lastpost_user);
			else $lastpost = '';
			
		$output .= $gc_gui->getMainBody('<p class="thread">'.$icon.'<a href="'.$wikiconf['main_link'].'?thread='.$thread->pkThread.$wikiconf['action_string'].'"><nowiki>'.$gc_helper->transformTags($thread->Thread_name).'</nowiki></a>
		<p class="descr" style="border-top: 0;">'.wfMsg('wikiforum-posted', $wgLang->timeanddate($thread->Posted), $gc_helper->getUserLink($thread->user_name)).'
		<br/>'.wfMsg('wikiforum-forum').': <a href="'.$wikiconf['main_link'].'?category='.$thread->pkCategory.$wikiconf['action_string'].'">'.$thread->Category_name.'</a> &gt; <a href="'.$wikiconf['main_link'].'?forum='.$thread->pkForum.$wikiconf['action_string'].'">'.$thread->Forum_name.'</a></p></p>', $thread->num_answers, $thread->num_calls, $lastpost, false, false);
	}
	$output .= $gc_gui->getMainPageFooter();
	return $output;
}

function WikiForumThread($input, $args) 
{
	global $wgOut, $wgUser, $wgLang, $wgContLang;
	global $wikiconf;
	global $gc_gui, $gc_helper;
	
	$sk 	=& $wgUser->getSkin();
	$dbr	= new UniDatabase(DB_SLAVE);
	
	if($args['id'] > 0)
	{
		$sqlThreads 	= $dbr->query('SELECT pkThread, Thread_name, Text, pkForum, Forum_name, pkCategory, Category_name, user_name, Edit, EditBy, Posted, PostedBy, Closed, ClosedBy
									  FROM wikiforum_forums as f, wikiforum_category as c, wikiforum_threads as t
									  LEFT JOIN '.$dbr->tableName('user').' as u ON user_id = PostedBy 
									  WHERE f.Deleted = 0 AND c.Deleted = 0 AND t.Deleted = 0 AND fkCategory = pkCategory AND pkForum=fkForum AND pkThread='.$args['id']);
		$data_overview	= $dbr->fetchObject($sqlThreads);
		
		if($data_overview)
		{
			$posted = wfMsg('wikiforum-posted', $wgLang->timeanddate($data_overview->Posted),$gc_helper->getUserLink($data_overview->user_name));
			if($data_overview->Edit > 0)
			{
				$posted .= '<br/><i>'.wfMsg('wikiforum-edited', $wgLang->timeanddate($data_overview->Edit),$gc_helper->getUserLinkById($data_overview->EditBy)).'</i>';
			}
			$output  = '<link rel="stylesheet" href="'.$wikiconf['extension_folder'].'styles.css" />';
			$output .= $gc_gui->getHeaderRow($data_overview->pkCategory, $data_overview->Category_name, $data_overview->pkForum, $data_overview->Forum_name, false);
			$output .= $gc_gui->getThreadHeader('<a href="'.$wikiconf['main_link'].'?thread='.$data_overview->pkThread.$wikiconf['action_string'].'">'.$data_overview->Thread_name.'</a>', $wgOut->parse($gc_helper->deleteTags($data_overview->Text)), $posted, $edit_buttons, $data_overview->pkThread);
			
			if(!$args['nocomments'])
			{
				$sqlComments 	= $dbr->query('SELECT c.*, u.user_name FROM wikiforum_comments as c
											  LEFT JOIN '.$dbr->tableName('user').' as u ON user_id = PostedBy 
											  WHERE c.Deleted = 0 AND c.fkThread='.$data_overview->pkThread.'
											  ORDER BY Posted ASC');
											  
				while ($comment = $dbr->fetchObject($sqlComments))
				{
					$posted = wfMsg('wikiforum-posted', $wgLang->timeanddate($comment->Posted),$gc_helper->getUserLink($comment->user_name));
					if($comment->Edit > 0)
					{
						$posted .= '<br/><i>'.wfMsg('wikiforum-edited', $wgLang->timeanddate($comment->Edit), $gc_helper->getUserLinkById($comment->EditBy)).'</i>';
					}
					$output .= $gc_gui->getComment($wgOut->parse($gc_helper->deleteTags($comment->Comment)), $posted, $edit_buttons, $comment->pkComment);
				}
			}

			$output .= $gc_gui->getThreadFooter();
			return $output;
		}
	}
	else return '';
}
?>