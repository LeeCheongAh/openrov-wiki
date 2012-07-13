<?php
function wfSpecialWikiForum() 
{
    global $wgOut;
	global $wikiconf;
	
	$helper = new WikiForumHelper;

	if($wikiconf['installed'] == "3e3daa044205bb26a0925b9da4b67abf")
	{
		$forum 			= new WikiForumClass;
		$wrap_request 	= new UniRequest;
		
		$mod_page			= $wrap_request->getString('page');
		$mod_category		= $wrap_request->getInt('category');
		$mod_forum 			= $wrap_request->getInt('forum');
		$mod_thread 		= $wrap_request->getInt('thread');
		$mod_writethread 	= $wrap_request->getInt('writethread');
		$mod_addcomment 	= $wrap_request->getInt('addcomment');
		$mod_addthread 		= $wrap_request->getInt('addthread');
		$mod_editcomment 	= $wrap_request->getInt('editcomment');
		$mod_editthread 	= $wrap_request->getInt('editthread');
		$mod_deletecomment 	= $wrap_request->getInt('deletecomment');
		$mod_deletethread 	= $wrap_request->getInt('deletethread');
		$mod_closethread 	= $wrap_request->getInt('closethread');
		$mod_reopenthread 	= $wrap_request->getInt('reopenthread');
		$mod_addcategory 	= $wrap_request->getBool('addcategory');
		$mod_addforum 		= $wrap_request->getInt('addforum');
		$mod_editcategory 	= $wrap_request->getInt('editcategory');
		$mod_editforum	 	= $wrap_request->getInt('editforum');
		$mod_deletecategory	= $wrap_request->getInt('deletecategory');
		$mod_deleteforum 	= $wrap_request->getInt('deleteforum');
		$mod_makesticky		= $wrap_request->getInt('makesticky');
		$mod_removesticky 	= $wrap_request->getInt('removesticky');
		$mod_categoryup		= $wrap_request->getInt('categoryup');
		$mod_categorydown	= $wrap_request->getInt('categorydown');
		$mod_forumup		= $wrap_request->getInt('forumup');
		$mod_forumdown		= $wrap_request->getInt('forumdown');
		$mod_search			= $wrap_request->getString('txtSearch');
		$mod_submit			= $wrap_request->getBool('butSubmit');
		$mod_pastethread	= $wrap_request->getInt('pastethread');
		
		$wgOut->addHTML($forum->showCss());
		
		if(isset($mod_addcomment) && $mod_addcomment > 0)
		{
			$data_text		= $wrap_request->getString('frmText');
			$data_preview	= $wrap_request->getBool('butPreview');
			$data_save		= $wrap_request->getBool('butSave');
			if($data_save == true)
			{
				$result 		= $forum->addComment($mod_addcomment, $data_text);
				$mod_thread		= $mod_addcomment;
			}
			else if($data_preview == true)
			{
				$result 		= $wgOut->addHTML($forum->previewIssue("addcomment", $mod_addcomment, false, $data_text));
				$mod_none		= true;
			}
		}
		else if(isset($mod_addthread) && $mod_addthread > 0)
		{
			$data_title		= $wrap_request->getString('frmTitle');
			$data_text		= $wrap_request->getString('frmText');
			$data_preview	= $wrap_request->getBool('butPreview');
			$data_save		= $wrap_request->getBool('butSave');
			if($data_save == true)
			{
				$result 		= $forum->addThread($mod_addthread, $data_title, $data_text);
				$mod_forum			= $mod_addthread;
			}
			else if($data_preview == true)
			{
				$result 		= $wgOut->addHTML($forum->previewIssue("addthread", $mod_addthread, $data_title, $data_text));
				$mod_none		= true;
			}
			else			$mod_writethread 	= $mod_addthread;
		}
		else if(isset($mod_editcomment) && $mod_editcomment > 0)
		{
			$data_text		= $wrap_request->getString('frmText');
			$data_preview	= $wrap_request->getBool('butPreview');
			$data_save		= $wrap_request->getBool('butSave');
			if($data_save == true)
			{
				$result 		= $forum->editComment($mod_editcomment, $data_text);
				$mod_thread		= $mod_thread;
			}
			else if($data_preview == true)
			{
				$result 		= $wgOut->addHTML($forum->previewIssue("editcomment", $mod_editcomment, false, $data_text));
				$mod_none		= true;
			}
		}
		else if(isset($mod_editthread) && $mod_editthread > 0)
		{
			$data_title		= $wrap_request->getString('frmTitle');
			$data_text		= $wrap_request->getString('frmText');
			$data_preview	= $wrap_request->getBool('butPreview');
			$data_save		= $wrap_request->getBool('butSave');
			if($data_save == true)
			{
				$result 		= $forum->editThread($mod_editthread, $data_title, $data_text);
				$mod_thread		= $mod_editthread;
			}
			else if($data_preview == true)
			{
				$result 		= $wgOut->addHTML($forum->previewIssue("editthread", $mod_editthread, $data_title, $data_text));
				$mod_none		= true;
			}
			else			$mod_writethread 	= $mod_editthread;
		}
		else if(isset($mod_deletecomment) && $mod_deletecomment > 0)
		{
			$result 		= $forum->deleteComment($mod_deletecomment);
		}
		else if(isset($mod_deletethread) && $mod_deletethread > 0)
		{
			$result 		= $forum->deleteThread($mod_deletethread);
		}
		else if(isset($mod_deletecategory) && $mod_deletecategory > 0)
		{
			$result 		= $forum->deleteCategory($mod_deletecategory);
		}
		else if(isset($mod_deleteforum) && $mod_deleteforum > 0)
		{
			$result 		= $forum->deleteForum($mod_deleteforum);
		}
		else if(isset($mod_categoryup) && $mod_categoryup > 0)
		{
			$result 		= $forum->sortKeys($mod_categoryup, "category", true);
		}
		else if(isset($mod_categorydown) && $mod_categorydown > 0)
		{
			$result 		= $forum->sortKeys($mod_categorydown, "category", false);
		}
		else if(isset($mod_forumup) && $mod_forumup > 0)
		{
			$result 		= $forum->sortKeys($mod_forumup, "forum", true);
		}
		else if(isset($mod_forumdown) && $mod_forumdown > 0)
		{
			$result 		= $forum->sortKeys($mod_forumdown, "forum", false);
		}
		else if(isset($mod_closethread) && $mod_closethread > 0)
		{
			$result 		= $forum->closeThread($mod_closethread);
			$mod_thread		= $mod_closethread;
		}
		else if(isset($mod_reopenthread) && $mod_reopenthread > 0)
		{
			$result 		= $forum->reopenThread($mod_reopenthread);
			$mod_thread		= $mod_reopenthread;
		}
		else if(isset($mod_makesticky) && $mod_makesticky > 0)
		{
			$result 		= $forum->makeSticky($mod_makesticky, true);
			$mod_thread		= $mod_makesticky;
		}
		else if(isset($mod_removesticky) && $mod_removesticky > 0)
		{
			$result 		= $forum->makeSticky($mod_removesticky, false);
			$mod_thread		= $mod_removesticky;
		}
		else if(isset($mod_pastethread) && $mod_pastethread > 0)
		{
			$result 		= $forum->pasteThread($mod_pastethread, $mod_forum);
		}
		else if($mod_addcategory == true && $helper->isAdminView())
		{
			if($mod_submit == true)
			{
				$values['title']	= $wrap_request->getString('frmTitle');
				$mod_submit 		= $forum->addCategory($values['title']);
			}
			if($mod_submit == false) 
			{
				$mod_showform 	= true;
				$type			= "addcategory";
				$id				= $mod_addcategory;
			}
		}
		else if(isset($mod_addforum) && $mod_addforum > 0 && $helper->isAdminView())
		{
			if($mod_submit == true)
			{
				$values['title']	= $wrap_request->getString('frmTitle');
				$values['text']		= $wrap_request->getString('frmText');
				if($wrap_request->getBool('chkAnnouncement') == true) 	$values['announce'] = "1";
					else											$values['announce'] = "0";
				$mod_submit 		= $forum->addForum($mod_addforum, $values['title'], $values['text'], $values['announce']);
			}
			if($mod_submit == false) 
			{
				$mod_showform 	= true;
				$type			= "addforum";
				$id				= $mod_addforum;
			}
		}
		else if(isset($mod_editcategory) && $mod_editcategory > 0 && $helper->isAdminView())
		{
			if($mod_submit == true)
			{
				$values['title']	= $wrap_request->getString('frmTitle');
				$mod_submit 		= $forum->editCategory($mod_editcategory, $values['title']);
			}
			if($mod_submit == false) 
			{
				$mod_showform 	= true;
				$type			= "editcategory";
				$id				= $mod_editcategory;
			}
		}
		else if(isset($mod_editforum) && $mod_editforum > 0 && $helper->isAdminView())
		{
			if($mod_submit == true)
			{
				$values['title']	= $wrap_request->getString('frmTitle');
				$values['text']		= $wrap_request->getString('frmText');
				if($wrap_request->getBool('chkAnnouncement') == true) 	$values['announce'] = "1";
					else											$values['announce'] = "0";
				$mod_submit 		= $forum->editForum($mod_editforum, $values['title'], $values['text'], $values['announce']);
			}
			if($mod_submit == false) 
			{
				$mod_showform 	= true;
				$type			= "editforum";
				$id				= $mod_editforum;
			}
		}
		
		if(isset($mod_page) && $mod_page == "stats")
		{
			$wgOut->addHTML($forum->showStatistics());
		}
		else if(isset($mod_search) && $mod_search == true) 
		{
			$wgOut->addHTML($forum->showSearchResults($mod_search));
		}
		else if($mod_none == true)
		{
			// no data
		}
		else if(isset($mod_category) && $mod_category > 0) 
		{
			$wgOut->addHTML($forum->showCategory($mod_category));
		}
		else if(isset($mod_forum) && $mod_forum > 0) 
		{
			$wgOut->addHTML($forum->showForum($mod_forum));
		}
		else if(isset($mod_thread) && $mod_thread > 0) 
		{
			$wgOut->addHTML($forum->showThread($mod_thread));
		}
		else if(isset($mod_writethread) && $mod_writethread > 0) 
		{
			$wgOut->addHTML($forum->writeThread($mod_writethread));
		}
		else if(isset($mod_showform) && $mod_showform == true) 
		{
			$wgOut->addHTML($forum->showEditorCatForum($id, $type, $values));
		}
		else 
		{
			$wgOut->addHTML($forum->showOverview());
		}
	}
	else
	{
		if($helper->isAdmin())
		{
			$gc_install 	= new WikiForumInstall;
			$gc_install->install();
		}
		else
		{
			$wgOut->AddHtml($wgOut->parse("=== Installation of WikiForum $gs_version ==="));
			$wgOut->AddHtml($wgOut->parse("You are not allowed to install WikiForum. Admin rights required.
									Make sure that you are logged-in and added the correct rights into ''LocalSetting.php'' file of MediaWiki.
									The following lines of code needs to be added in LocalSetting.php:\n
									<source lang='php'>require_once(\"\$IP/extensions/WikiForum/WikiForum.php\");\n\$wgGroupPermissions['ForumModerator']['wikiforumadmin'] 	= false;\n\$wgGroupPermissions['ForumAdmin']['wikiforumadmin'] 		= true;\n\$wgGroupPermissions['ForumModerator']['wikiforummod'] 		= true;\n\$wgGroupPermissions['ForumAdmin']['wikiforummod'] 		= true;</source>
									And at least you need to add yourself to the Usergroup ''ForumAdmin''!"));
		}

	}
	$wgOut->addHTML(WikiForumGui::getInfoLine());
}
?>
