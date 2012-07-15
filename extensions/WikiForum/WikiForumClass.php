<?php
class WikiForumClass 
{
	function __construct() 
	{ 
	
	} 

	private $m_result 	= true;
	
	private $m_error_t 	= "";
	private $m_error_i 	= "";
	private $m_error_m 	= "";
	
	function deleteComment($comment_id)
	{
		global $wgUser;
		global $gc_helper;
		
		$dbr = new UniDatabase(DB_SLAVE);
		$dbw = new UniDatabase(DB_MASTER);
		
		$data_comment = $dbr->fetchObject($dbr->query('SELECT pkComment, c.PostedBy, pkThread, pkForum FROM wikiforum_comments as c, wikiforum_threads, wikiforum_forums WHERE pkForum = fkForum AND pkThread = fkThread AND c.Deleted=0 AND pkComment='.$comment_id));
										
		if($data_comment->pkComment > 0 && $wgUser->getID() > 0 && ($wgUser->getID() == $data_comment->PostedBy || $gc_helper->isModerator()))
		{
			$result = $dbw->query('UPDATE wikiforum_comments 
									  SET Deleted='.time().', DeletedBy='.$wgUser->getID().' 
									  WHERE pkComment='.$data_comment->pkComment);
									  
			$dbw->minus('wikiforum_threads', 'num_answers', 'pkThread='.$data_comment->pkThread);
			$dbw->minus('wikiforum_forums', 'num_articles', 'pkForum='.$data_comment->pkForum);
		}
		else $result = false;
		
		if($result == false)
		{
			$this->m_error_t = wfMsg('wikiforum-error_delete');
			$this->m_error_m = wfMsg('wikiforum-error_general');
		}
		return $result;
	}
	
	function deleteThread($thread_id)
	{
		global $wgUser;
		global $gc_helper;
		
		$dbr = new UniDatabase(DB_SLAVE);
		$dbw = new UniDatabase(DB_MASTER);
		
		$data_thread = $dbr->fetchObject($dbr->query('SELECT pkThread, t.PostedBy, fkCategory, pkForum FROM wikiforum_threads as t, wikiforum_forums WHERE pkForum = fkForum AND t.Deleted=0 AND pkThread='.$thread_id));
										
		if($data_thread->pkThread > 0 && $wgUser->getID() > 0 && ($wgUser->getID() == $data_thread->PostedBy || $gc_helper->isModerator()) && $this->getCategoryAccess($data_thread->fkCategory))
		{
			$result = $dbw->query('UPDATE wikiforum_threads 
									  SET Deleted='.time().', DeletedBy='.$wgUser->getID().' 
									  WHERE pkThread='.$data_thread->pkThread);
									  
			$dbw->minus('wikiforum_forums', 'num_threads', 'pkForum='.$data_thread->pkForum);
		}
		else $result = false;
		
		if($result == false)
		{
			$this->m_error_t = wfMsg('wikiforum-error_delete');
			$this->m_error_m = wfMsg('wikiforum-error_general');
		}
		return $result;
	}
	
	function deleteCategory($category_id)
	{
		global $wgUser;
		global $gc_helper;
		
		if($gc_helper->isAdminView())
		{
			$dbw = new UniDatabase(DB_MASTER);
			$result = $dbw->query('UPDATE wikiforum_category 
									  SET Deleted='.time().', DeletedBy='.$wgUser->getID().' 
									  WHERE pkCategory='.$category_id);
		}
		else $result = false;
		
		if($result == false)
		{
			$this->m_error_t = wfMsg('wikiforum-error_delete');
			$this->m_error_m = wfMsg('wikiforum-error_general');
		}
		return $result;
	}
	
	function deleteForum($forum_id)
	{
		global $wgUser;
		global $gc_helper;
		
		if($gc_helper->isAdminView())
		{
			$dbw = new UniDatabase(DB_MASTER);
			$result = $dbw->query('UPDATE wikiforum_forums 
									  SET Deleted='.time().', DeletedBy='.$wgUser->getID().' 
									  WHERE pkForum='.$forum_id);
		}
		else $result = false;
		
		if($result == false)
		{
			$this->m_error_t = wfMsg('wikiforum-error_delete');
			$this->m_error_m = wfMsg('wikiforum-error_general');
		}
		return $result;
	}
	
	function reopenThread($thread_id)
	{
		global $gc_helper;
		
		$dbr = new UniDatabase(DB_SLAVE);
		$dbw = new UniDatabase(DB_MASTER);
		
		$data_thread = $dbr->fetchObject($dbr->query('SELECT pkThread, fkCategory FROM wikiforum_threads as t, wikiforum_forums WHERE pkForum = fkForum AND Closed>0 AND t.Deleted=0 AND pkThread='.$thread_id));
										
		if($data_thread->pkThread > 0 && $gc_helper->isModerator() && $this->getCategoryAccess($data_thread->fkCategory))
		{
			$result = $dbw->query('UPDATE wikiforum_threads 
									  SET Closed=0, ClosedBy=0 
									  WHERE pkThread='.$data_thread->pkThread);
		}
		else $result = false;
		
		if($result == false)
		{
			$this->m_error_t = wfMsg('wikiforum-error_thread_reopen');
			$this->m_error_m = wfMsg('wikiforum-error_general');
		}
		return $result;
	}
	
	function closeThread($thread_id)
	{
		global $wgUser;
		global $gc_helper;
		
		$dbr = new UniDatabase(DB_SLAVE);
		$dbw = new UniDatabase(DB_MASTER);
		
		$data_thread = $dbr->fetchObject($dbr->query('SELECT pkThread, fkCategory FROM wikiforum_threads as t, wikiforum_forums WHERE pkForum = fkForum AND Closed=0 AND t.Deleted=0 AND pkThread='.$thread_id));
										
		if($data_thread->pkThread > 0 && $gc_helper->isModerator() && $this->getCategoryAccess($data_thread->fkCategory))
		{
			$result = $dbw->query('UPDATE wikiforum_threads 
									  SET Closed='.time().', ClosedBy='.$wgUser->getID().' 
									  WHERE pkThread='.$data_thread->pkThread);
		}
		else $result = false;
		
		if($result == false)
		{
			$this->m_error_t = wfMsg('wikiforum-error_thread_close');
			$this->m_error_m = wfMsg('wikiforum-error_general');
		}
		return $result;
	}
	
	function makeSticky($thread_id, $value)
	{
		global $gc_helper;
		
		$dbr = new UniDatabase(DB_SLAVE);
		$dbw = new UniDatabase(DB_MASTER);
		
		$data_thread = $dbr->fetchObject($dbr->query('SELECT pkThread, fkCategory FROM wikiforum_threads as t, wikiforum_forums WHERE pkForum = fkForum AND t.Deleted=0 AND pkThread='.$thread_id));
		
		if($data_thread->pkThread > 0 && $gc_helper->isAdminView() && $this->getCategoryAccess($data_thread->fkCategory))
		{
			if($value == false) $value = 0;	
			$result = $dbw->query('UPDATE wikiforum_threads SET Sticky='.$value.' WHERE pkThread='.$data_thread->pkThread);
		}
		else $result = false;
		
		if($result == false)
		{
			$this->m_error_t = wfMsg('wikiforum-error_sticky');
			$this->m_error_m = wfMsg('wikiforum-error_general');
		}
		return $result;
	}
	
	function pasteThread($thread_id, $forum_id)
	{
		global $gc_helper;
		
		$dbr = new UniDatabase(DB_SLAVE);
		$dbw = new UniDatabase(DB_MASTER);
		
		$data_thread = $dbr->fetchObject($dbr->query('SELECT pkThread, fkForum FROM wikiforum_threads WHERE Deleted=0 AND pkThread='.$thread_id));
		$data_forum  = $dbr->fetchObject($dbr->query('SELECT pkForum, fkCategory FROM wikiforum_forums WHERE Deleted=0 AND pkForum='.$forum_id));
		
		if($data_thread->pkThread > 0 && $data_forum->pkForum > 0 && $gc_helper->isModerator() && $this->getCategoryAccess($data_forum->fkCategory))
		{
			if($data_thread->fkForum != $data_forum->pkForum)
			{
				$result = $dbw->query('UPDATE wikiforum_threads SET fkForum='.$data_forum->pkForum.' WHERE pkThread='.$data_thread->pkThread);
			}
			else $result = true;
		}
		else $result = false;
		
		if($result == false)
		{
			$this->m_error_t = wfMsg('wikiforum-error_movethread');
			$this->m_error_m = wfMsg('wikiforum-error_general');
		}
		return $result;
	}
	
	function editThread($thread_id, $title, $text)
	{
		global $wgUser;
		global $gc_helper;
		
		if($text && $title && strlen($text)>1 && strlen($title)>1)
		{
			$dbr = new UniDatabase(DB_SLAVE);
			$dbw = new UniDatabase(DB_MASTER);
			
			$data_thread = $dbr->fetchObject($dbr->query('SELECT pkThread, Thread_name, Text, PostedBy, fkCategory FROM wikiforum_threads as t, wikiforum_forums WHERE pkForum = fkForum AND t.Deleted=0 AND pkThread='.$thread_id));
		
			if($data_thread->pkThread > 0 && $this->getCategoryAccess($data_thread->fkCategory))
			{			
				if($data_thread->Thread_name != $title || $data_thread->Text != $text)
				{
					if($wgUser->getID() > 0 && ($wgUser->getID() == $data_thread->PostedBy || $gc_helper->isModerator()))
					{
						$result = $dbw->query('UPDATE wikiforum_threads 
												  SET Thread_name='.$dbw->addQuotes($title).', Text='.$dbw->addQuotes($gc_helper->cleanString($text)).', Edit='.time().', EditBy='.$wgUser->getID().' 
												  WHERE pkThread='.$data_thread->pkThread);
					}
					else
					{ 
						$this->m_error_m = wfMsg('wikiforum-error_norights');
						$result = false;
					}
				} else $result = true; //no changes
			}
			else
			{ 
				$this->m_error_m = wfMsg('wikiforum-error_notfound');
				$result = false;
			}
		
			if($result == false)
			{
				$this->m_error_t = wfMsg('wikiforum-error_edit');
				if($this->m_error_m == "") $this->m_error_m = wfMsg('wikiforum-error_general');
			}
		}
		else
		{
			if(!$text && !$title || strlen($text) == 0 && strlen($title)==0)
			{
				$result = false;
			}
			else
			{
				$this->m_result = false;
				$result = false;
			}
		}
		return $result;
	}
	
	function addThread($forum_id, $title, $text)
	{
		global $wgUser;
		global $wikiconf;
		global $gc_helper;
		$timestamp = time();
		
		if($wikiconf['anonymous_allowed'] == true || $wgUser->getID()>0)
		{
			if($forum_id>0 && strlen($text)>1 && strlen($title)>1 && $title!=wfMsg('wikiforum-threadtitle'))
			{
				$dbr = new UniDatabase(DB_SLAVE);
				$dbw = new UniDatabase(DB_MASTER);
				
				$data_overview = $dbr->fetchObject($dbr->query('SELECT pkForum, Announcement, pkCategory
															FROM wikiforum_forums as f, wikiforum_category as c 
															WHERE c.Deleted = 0 AND f.Deleted = 0 AND c.pkCategory = f.fkCategory AND pkForum='.$forum_id));
				
				if($data_overview->pkForum > 0 && $this->getCategoryAccess($data_overview->pkCategory))
				{
					if($data_overview->Announcement == false || $gc_helper->isModerator() == true)
					{
						$title 	= $dbw->addQuotes($title);
						$text	= $dbw->addQuotes($gc_helper->cleanString($text));
						
						$doublepost = $dbr->fetchObject($dbr->query('SELECT pkThread FROM wikiforum_threads WHERE 
											Deleted = 0 AND 
											Thread_name='.$title.' AND 
											Text='.$text.' AND 
											PostedBy='.$wgUser->getID().' AND
											fkForum='.$forum_id.' AND 
											Posted>'.($timestamp-(24*3600))));
					
						if($doublepost == false) 
						{
							$result = $dbw->query('INSERT INTO wikiforum_threads (Thread_name, Text, Posted, PostedBy, fkForum, lastpost_time) 
													VALUES ('.$title.', '.$text.', '.$timestamp.', '.$wgUser->getID().', '.$forum_id.', '.$timestamp.')');
							if($result == true)
							{
								$dbw->query('UPDATE wikiforum_forums 
													  SET num_threads=num_threads+1, lastpost_time='.$timestamp.', lastpost_user='.$wgUser->getID().' 
													  WHERE pkForum='.$forum_id);
								$this->m_result = true;
							}
							else $this->m_result = false;
						}
						else
						{
							$this->m_error_m = wfMsg('wikiforum-error_doublepost');
							$this->m_result = false;
						}
					}
					else
					{
						$this->m_error_m = wfMsg('wikiforum-error_norights');
						$this->m_result = false;
					}
				}
				else 
				{
					$this->m_result = false;	
				}
			}
			else 
			{
				$this->m_error_m = wfMsg('wikiforum-error_notextortitle');
				$this->m_result = false;	
			}
		}
		else
		{
			$this->m_error_m = wfMsg('wikiforum-error_norights');
			$this->m_result = false;
		}
		
		if($this->m_result == false)
		{
			$this->m_error_t = wfMsg('wikiforum-error_add');
			if($this->m_error_m == "") $this->m_error_m = wfMsg('wikiforum-error_general');
		}
		return $this->m_result;
	}

	function editComment($comment_id, $text)
	{
		global $wgUser;
		global $gc_helper;
		
		if($text && strlen($text)>1)
		{
			$dbr = new UniDatabase(DB_SLAVE);
			$dbw = new UniDatabase(DB_MASTER);
			
			$data_comment = $dbr->fetchObject($dbr->query('SELECT fkThread, pkComment, Comment, PostedBy FROM wikiforum_comments WHERE pkComment='.$comment_id));
			$data_thread = $dbr->fetchObject($dbr->query('SELECT pkThread, Closed, fkCategory FROM wikiforum_threads as t, wikiforum_forums WHERE pkForum = fkForum AND t.Deleted = 0 AND pkThread='.$data_comment->fkThread));
			
			if($data_comment->pkComment > 0 && $data_thread->pkThread > 0 && $this->getCategoryAccess($data_thread->fkCategory))
			{
				if($data_comment->Comment != $text)
				{
					if($wgUser->getID() > 0 && (($wgUser->getID() == $data_comment->PostedBy && $data_thread->Closed == 0) || $gc_helper->isModerator()))
					{
						$result = $dbw->query('UPDATE wikiforum_comments 
												  SET Comment='.$dbw->addQuotes($gc_helper->cleanString($text)).', Edit='.time().', EditBy='.$wgUser->getID().' 
												  WHERE pkComment='.$data_comment->pkComment);
					}
					else
					{ 
						$this->m_error_m = wfMsg('wikiforum-error_norights');
						$result = false;
					}
				} else $result = true;
			}
			else
			{ 
				$this->m_error_m = wfMsg('wikiforum-error_notfound');
				$result = false;
			}
		}
		else
		{
			$wrap_request 	= new UniRequest;
			
			$form = $wrap_request->getBool('form');
			
			if($form==true)
			{
				$this->m_error_m = wfMsg('wikiforum-error_nocomment');
				$result = false;
			} else $result = true;
		}
		
		if($result == false)
		{
			$this->m_error_t = wfMsg('wikiforum-error_edit');
			if($this->m_error_m == "") $this->m_error_m = wfMsg('wikiforum-error_general');
		}
		return $result;
	}
	
	function addComment($thread_id, $text)
	{
		global $wgUser;
		global $wikiconf;
		global $gc_helper;
		$timestamp = time();
		
		if($wikiconf['anonymous_allowed'] == true || $wgUser->getID()>0)
		{
			if($thread_id>0)
			{
				if(strlen($text)>1)
				{
					$dbr = new UniDatabase(DB_SLAVE);
					$dbw = new UniDatabase(DB_MASTER);
					
					$data_thread = $dbr->fetchObject($dbr->query('SELECT pkThread, fkCategory FROM wikiforum_threads as t, wikiforum_forums WHERE pkForum = fkForum AND t.Deleted = 0 AND Closed = 0 AND pkThread='.$thread_id));
					
					if($data_thread->pkThread > 0 && $this->getCategoryAccess($data_thread->fkCategory))
					{
						$text = $dbw->addQuotes($gc_helper->cleanString($text));
						$doublepost = $dbr->fetchObject($dbr->query('SELECT pkComment FROM wikiforum_comments WHERE 
											Deleted = 0 AND 
											Comment='.$text.' AND 
											PostedBy='.$wgUser->getID().' AND
											fkThread='.$data_thread->pkThread.' AND 
											Posted>'.($timestamp-(24*3600))));
					
						if($doublepost == false) 
						{
							$result = $dbw->query('INSERT INTO wikiforum_comments (Comment, Posted, PostedBy, fkThread) 
													VALUES ('.$text.', '.$timestamp.', '.$wgUser->getID().', '.$data_thread->pkThread.')');
							if($result == true)
							{
								$dbw->query('UPDATE wikiforum_threads 
													  SET num_answers=num_answers+1, lastpost_time='.$timestamp.', lastpost_user='.$wgUser->getID().' 
													  WHERE pkThread='.$thread_id);
								
								$sqlForum 	= $dbr->query('SELECT fkForum FROM wikiforum_threads WHERE pkThread='.$thread_id);
								$pkForum 	= $dbr->fetchObject($sqlForum);
								$dbw->query('UPDATE wikiforum_forums SET num_articles=num_articles+1 WHERE pkForum='.$pkForum->fkForum);
								
								$this->m_result = true;
							}
							else $this->m_result = false;
						}
						else
						{
							$this->m_error_m = wfMsg('wikiforum-error_doublepost');
							$this->m_result = false;
						}
					}
					else
					{
						$this->m_error_m = wfMsg('wikiforum-error_threadclosed');
						$this->m_result = false;
					}
				}
				else
				{
					$this->m_error_m = wfMsg('wikiforum-error_nocomment');
					$this->m_result = false;
				}
			}
			else 
			{
				$this->m_error_m = wfMsg('wikiforum-error_notfound');
				$this->m_result = false;	
			}
		}
		else 
		{
			$this->m_error_m = wfMsg('wikiforum-error_norights');
			$this->m_result = false;	
		}
		
		if($this->m_result == false)
		{
			$this->m_error_t = wfMsg('wikiforum-error_add');
			if($this->m_error_m == "") $this->m_error_m = wfMsg('wikiforum-error_general');
		}
		return $this->m_result;
	}
	
	function addCategory($cat_name)
	{
		global $wgUser;
		global $wikiconf;
		global $gc_helper;
		
		if($gc_helper->isAdminView())
		{
			if(strlen($cat_name) > 0)
			{
				$wrap_request 	= new UniRequest;
				
				$dbr = new UniDatabase(DB_SLAVE);
				$dbw = new UniDatabase(DB_MASTER);
				
				$data_sortkey 	= $dbr->fetchRow($dbr->query('SELECT MAX(SortKey) FROM wikiforum_category WHERE Deleted=0'));
				$timestamp 		= time();
				$cat_name 		= $dbw->addQuotes($gc_helper->cleanString($cat_name));
				$this->m_result = $dbw->query('INSERT INTO wikiforum_category (Category_name, SortKey, Added, AddedBy) 
										VALUES ('.$cat_name.', '.($data_sortkey[0]+1).', '.$timestamp.', '.$wgUser->getID().')');
				
				$mod_permission	= $wrap_request->getString('selPermissions');
				
				if($this->m_result == true && isset($mod_permission) && strlen($mod_permission)>0 && $mod_permission != "---")
				{
					if(in_array($mod_permission, $gc_helper->getAllPermissions()))
					{
						$data_saved 	= $dbr->fetchObject($dbr->query('SELECT pkCategory FROM wikiforum_category WHERE Category_name = '.$cat_name.' AND Added = '.$timestamp.' AND AddedBy = '.$wgUser->getID()));
						if($data_saved != false)
						{
							$this->m_result = $dbw->query('INSERT INTO wikiforum_cataccess (fkCategory, GroupeRight) VALUES ('.$data_saved->pkCategory.', '.$dbw->addQuotes($mod_permission).')');
						}
					}
				}
			}
			else 
			{
				$this->m_error_m = wfMsg('wikiforum-notextortitle');
				$this->m_result = false;	
			}
		}
		else 
		{
			$this->m_error_m = wfMsg('wikiforum-error_norights');
			$this->m_result = false;	
		}
		
		if($this->m_result == false)
		{
			$this->m_error_t = wfMsg('wikiforum-error_add');
			if($this->m_error_m == "") $this->m_error_m = wfMsg('wikiforum-error_general');
		}
		return $this->m_result;
	}
	
	function editCategory($id, $cat_name)
	{
		global $wgUser;
		global $wikiconf;
		global $gc_helper;
		
		if($gc_helper->isAdminView())
		{
			if(strlen($cat_name) > 0)
			{
				$wrap_request 	= new UniRequest;
				
				$dbr = new UniDatabase(DB_SLAVE);
				$dbw = new UniDatabase(DB_MASTER);
				
				$data_category = $dbr->fetchObject($dbr->query('SELECT pkCategory, Category_name FROM wikiforum_category WHERE Deleted=0 AND pkCategory='.$id));
				
				if($data_category->pkCategory > 0 && $data_category->Category_name != $cat_name)
				{
					$this->m_result = $dbw->query('UPDATE wikiforum_category 
													  SET Category_name='.$dbw->addQuotes($gc_helper->cleanString($cat_name)).', Edited='.time().', EditedBy='.$wgUser->getID().' 
													  WHERE pkCategory='.$data_category->pkCategory);
				}
				
				$mod_usedpermissions 	= $wrap_request->getArray('chkPermissions');
				$usedpermissions_object	= $dbr->query('SELECT pkCatAccess FROM wikiforum_cataccess WHERE fkCategory = '.$data_category->pkCategory);
				while ($permission = $dbr->fetchObject($usedpermissions_object))
				{
					$to_delete = true;
					if(is_array($mod_usedpermissions))
					{
						foreach($mod_usedpermissions as $key => $value)
						{
							if($permission->pkCatAccess == $key)
							{
								$to_delete = false;
								break;
							}
						}
					}
					if($to_delete == true)
					{
						$result = $dbw->query('DELETE FROM wikiforum_cataccess WHERE pkCatAccess='.$permission->pkCatAccess);
					}
				}
				
				
				$mod_permission	= $wrap_request->getString('selPermissions');
				if(isset($mod_permission) && strlen($mod_permission)>0 && $mod_permission != "---")
				{
					if(in_array($mod_permission, $gc_helper->getAllPermissions()))
					{
						$cat_access = $dbr->fetchObject($dbr->query('SELECT pkCatAccess FROM wikiforum_cataccess WHERE fkCategory = '.$data_category->pkCategory.' AND GroupeRight='.$dbr->addQuotes($mod_permission)));
						if($cat_access == false) 
						{
							$this->m_result = $dbw->query('INSERT INTO wikiforum_cataccess (fkCategory, GroupeRight) VALUES ('.$data_category->pkCategory.', '.$dbw->addQuotes($mod_permission).')');
						}
					}
				}
			}
			else 
			{
				$this->m_error_m = wfMsg('wikiforum-notextortitle');
				$this->m_result = false;	
			}
		}
		else 
		{
			$this->m_error_m = wfMsg('wikiforum-error_norights');
			$this->m_result = false;	
		}
		
		if($this->m_result == false)
		{
			$this->m_error_t = wfMsg('wikiforum-error_edit');
			if($this->m_error_m == "") $this->m_error_m = wfMsg('wikiforum-error_general');
		}
		return $this->m_result;
	}
	
	function addForum($category_id, $forum_name, $description, $announcement)
	{
		global $wgUser;
		global $wikiconf;
		global $gc_helper;
		
		if($gc_helper->isAdminView())
		{
			if(strlen($forum_name) > 0)
			{
				$dbr = new UniDatabase(DB_SLAVE);
				$dbw = new UniDatabase(DB_MASTER);
				
				$data_sortkey = $dbr->fetchRow($dbr->query('SELECT MAX(SortKey) FROM wikiforum_forums WHERE Deleted=0 AND fkCategory='.$category_id));
				$data_category = $dbr->fetchObject($dbr->query('SELECT pkCategory FROM wikiforum_category WHERE Deleted = 0 AND pkCategory='.$category_id));

				if($data_category->pkCategory > 0)
				{
					$this->m_result = $dbw->query('INSERT INTO wikiforum_forums (Forum_name, Description, fkCategory, SortKey, Added, AddedBy, Announcement) 
											VALUES ('.$dbw->addQuotes($gc_helper->cleanString($forum_name)).', '.$dbw->addQuotes($gc_helper->cleanString($description)).', '.$data_category->pkCategory.', '.($data_sortkey[0]+1).', '.time().', '.$wgUser->getID().', '.$announcement.')');
				}
				else
				{
					$this->m_error_m = wfMsg('wikiforum-error_notfound');
					$this->m_result = false;	
				}
			}
			else 
			{
				$this->m_error_m = wfMsg('wikiforum-notextortitle');
				$this->m_result = false;	
			}
		}
		else 
		{
			$this->m_error_m = wfMsg('wikiforum-error_norights');
			$this->m_result = false;	
		}
		
		if($this->m_result == false)
		{
			$this->m_error_t = wfMsg('wikiforum-error_add');
			if($this->m_error_m == "") $this->m_error_m = wfMsg('wikiforum-error_general');
		}
		return $this->m_result;
	}
	
	function editForum($id, $forum_name, $description, $announcement)
	{
		global $wgUser;
		global $wikiconf;
		global $gc_helper;
		
		if($gc_helper->isAdminView())
		{
			if(strlen($forum_name) > 0)
			{
				$dbr = new UniDatabase(DB_SLAVE);
				$data_forum = $dbr->fetchObject($dbr->query('SELECT pkForum, Forum_name, Description, Announcement FROM wikiforum_forums WHERE Deleted=0 AND pkForum='.$id));

				if($data_forum->pkForum > 0 && ($data_forum->Forum_name != $forum_name || $data_forum->Description != $description || $data_forum->Announcement != $announcement))
				{
					$dbw = new UniDatabase(DB_MASTER);
					$this->m_result = $dbw->query('UPDATE wikiforum_forums 
													  SET Forum_name='.$dbw->addQuotes($gc_helper->cleanString($forum_name)).', Description='.$dbw->addQuotes($gc_helper->cleanString($description)).', Edited='.time().', EditedBy='.$wgUser->getID().', Announcement='.$announcement.'
													  WHERE pkForum='.$data_forum->pkForum);
				}
			}
			else
			{
				$this->m_error_m = wfMsg('wikiforum-notextortitle');
				$this->m_result = false;	
			}
		}
		else 
		{
			$this->m_error_m = wfMsg('wikiforum-error_norights');
			$this->m_result = false;	
		}
		
		if($this->m_result == false)
		{
			$this->m_error_t = wfMsg('wikiforum-error_add');
			if($this->m_error_m == "") $this->m_error_m = wfMsg('wikiforum-error_general');
		}
		return $this->m_result;
	}
	
	function sortKeys($id, $type, $direction_up)
	{
		global $gc_helper;
		if($gc_helper->isAdminView())
		{
			$dbr = new UniDatabase(DB_SLAVE);
			$dbw = new UniDatabase(DB_MASTER);
			
			if($type == "category")
			{
				$fieldname 	= "pkCategory";
				$tablename 	= "wikiforum_category";
				$sqlData 	= $dbr->query('SELECT '.$fieldname.', SortKey FROM '.$tablename.' WHERE Deleted=0 ORDER BY SortKey ASC');
			}
			else
			{
				$fieldname 	= "pkForum";
				$tablename 	= "wikiforum_forums";
				$data_forum = $dbr->fetchObject($dbr->query('SELECT fkCategory FROM '.$tablename.' WHERE Deleted=0 AND pkForum='.$id));
				$sqlData 	= $dbr->query('SELECT '.$fieldname.', SortKey FROM '.$tablename.' WHERE Deleted=0 AND fkCategory='.$data_forum->fkCategory.' ORDER BY SortKey ASC');
			}
			
			$i = 0;
			$new_array = array();
			while ($entry = $dbr->fetchObject($sqlData))
			{
				$entry->SortKey = $i;
				array_push($new_array, $entry); 
				$i++;
			}
			for($i = 0; $i < sizeof($new_array); $i++)
			{
				if($new_array[$i]->$fieldname == $id)
				{
					if($direction_up == true && $i > 0)
					{
						$new_array[$i]->SortKey--;
						$new_array[$i-1]->SortKey++;
					}
					else if($direction_up == false && $i+1 < sizeof($new_array))
					{
						$new_array[$i]->SortKey++;
						$new_array[$i+1]->SortKey--;
					}
					$i = sizeof($new_array);
				}
			}
			foreach ($new_array as $entry)
			{
				$result = $dbw->query('UPDATE '.$tablename.' SET SortKey='.$entry->SortKey.' WHERE '.$fieldname.'='.$entry->$fieldname);
			}
		}
	}
	
	function showOverview()
	{
		global $wgOut, $wgUser, $wgLang;
		global $wikiconf;
		global $gc_gui, $gc_helper;
		
		$output 		= $this->showFailure();
		$dbr			= new UniDatabase(DB_SLAVE);
		
		$sqlCategories 	= $dbr->query('SELECT * FROM wikiforum_category WHERE Deleted=0 ORDER BY SortKey ASC, pkCategory ASC');
		
		$output .= $gc_gui->getSearchbox();
		
		while ($cat = $dbr->fetchObject($sqlCategories)) 
		{
			if($this->getCategoryAccess($cat->pkCategory))
			{
				$sqlForums = $dbr->query('SELECT f.*, u.user_name FROM wikiforum_forums as f 
											LEFT JOIN '.$dbr->tableName('user').' as u ON u.user_id = f.lastpost_user 
											WHERE f.Deleted=0 AND f.fkCategory='.$cat->pkCategory.'
											ORDER BY f.SortKey ASC, f.pkForum ASC');
	
				if($gc_helper->isAdminView())
				{
					if($wikiconf['icon_forum_add']) $icon = '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_forum_add'].'" title="'.wfMsg('wikiforum-addforum').'" /> ';
						else $icon = "";
					$menu_link = $icon.'<a href="?addforum='.$cat->pkCategory.$gc_helper->getActionString("view").'">'.wfMsg('wikiforum-addforum').'</a>';
									
					$cat_link = $this->showAdminIcons("category", $cat->pkCategory, true, true);
				}
				if($menu_link) $output .= $gc_gui->getHeaderRow(0, "", 0, "", $menu_link);
				$output .= $gc_gui->getMainHeader($cat->Category_name, wfMsg('wikiforum-threads'), wfMsg('wikiforum-comments'), wfMsg('wikiforum-lastpost'), $cat_link);
	
				while ($forum = $dbr->fetchObject($sqlForums))
				{
					$forum_link = $this->showAdminIcons("forum", $forum->pkForum, true, true);
					
					if($wikiconf['icon_forum']) $icon = '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_forum'].'" title="Forum '.$forum->Forum_name.'" /> ';
						else $icon = "";
						
					if($forum->lastpost_time>0)	
					{
						$thread = $dbr->fetchObject($dbr->query('SELECT * FROM wikiforum_threads
											WHERE Deleted=0 AND fkForum='.$forum->pkForum.'
											ORDER BY lastpost_time DESC
											LIMIT 0, 1'));
					
						if($thread->lastpost_user == 0) $user_id = $thread->PostedBy;
							else $user_id = $thread->lastpost_user;
						$last_post = '<b><a href="?thread='.$thread->pkThread.$gc_helper->getActionString("view").'">'.$gc_helper->shortText($thread->Thread_name).'</a></b><br/>'.$wgLang->timeanddate($thread->lastpost_time).'<br/>'.wfMsg('wikiforum-by').' '.$gc_helper->getUserLinkById($user_id);
					}
					else $last_post = "";
	
				
					$output .= $gc_gui->getMainBody('<p class="issue">'.$icon.'<a href="?forum='.$forum->pkForum.$gc_helper->getActionString("view").'">'.$forum->Forum_name.'</a></p><p class="descr">'.$forum->Description.'</p>', $forum->num_threads, $forum->num_articles, $last_post, $forum_link, false);
				}
				$output .= $gc_gui->getMainFooter();
				}
		}
					
		if($gc_helper->isAdmin())
		{
			$menu_link = $this->getAdminViewLink();
			
			if($gc_helper->isAdminView())
			{
				if($wikiconf['icon_category_add']) $icon = '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_category_add'].'" title="'.wfMsg('wikiforum-addcategory').'" /> ';
					else $icon = "";
				$menu_link .= $icon.'<a href="?addcategory=true'.$gc_helper->getActionString("view").'">'.wfMsg('wikiforum-addcategory').'</a>';
			}
			$output .= $gc_gui->getHeaderRow(0, "", 0, "", $menu_link.$gc_gui->getStatsLink());
		}
		
		return $output;
	}
	
	function showCategory($category_id)
	{
		global $wgOut, $wgLang, $wgUser;
		global $wikiconf;
		global $gc_gui, $gc_helper;
		
		$output 		= $this->showFailure();
		$dbr			= new UniDatabase(DB_SLAVE);
		
		$sqlData 		= $dbr->query('SELECT pkCategory, Category_name FROM wikiforum_category WHERE Deleted=0 AND pkCategory='.$category_id);
		$data_overview	= $dbr->fetchObject($sqlData);

		if($data_overview && $this->getCategoryAccess($data_overview->pkCategory))
		{
			$sqlForums = $dbr->query('SELECT f.*, u.user_name FROM wikiforum_forums as f 
										LEFT JOIN '.$dbr->tableName('user').' as u ON u.user_id = f.lastpost_user 
										WHERE f.Deleted=0 AND f.fkCategory='.$data_overview->pkCategory.'
										ORDER BY f.SortKey ASC, f.pkForum ASC');

	
			if($gc_helper->isAdminView())
			{
				if($wikiconf['icon_forum_add']) $icon = '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_forum_add'].'" title="'.wfMsg('wikiforum-addforum').'" /> ';
					else $icon = "";
				$menu_link = $icon.'<a href="?addforum='.$data_overview->pkCategory.$gc_helper->getActionString("view").'">'.wfMsg('wikiforum-addforum').'</a>';	
				$cat_link = $this->showAdminIcons("category", $data_overview->pkCategory, false, false);
			}

			$output .= $gc_gui->getSearchbox();
			$output .= $gc_gui->getHeaderRow($data_overview->pkCategory, $data_overview->Category_name, 0, "", $menu_link);
			$output .= $gc_gui->getMainHeader($data_overview->Category_name, wfMsg('wikiforum-threads'), wfMsg('wikiforum-comments'), wfMsg('wikiforum-lastthread'), $cat_link);
	
			while ($forum = $dbr->fetchObject($sqlForums))
			{
				$forum_link = $this->showAdminIcons("forum", $forum->pkForum, true, true);
				
				if($wikiconf['icon_forum']) $icon = '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_forum'].'" title="Forum '.$forum->Forum_name.'" /> ';
					else $icon = "";
					
				if($forum->lastpost_time>0)	
				{
					$thread = $dbr->fetchObject($dbr->query('SELECT * FROM wikiforum_threads
										WHERE Deleted=0 AND fkForum='.$forum->pkForum.'
										ORDER BY lastpost_time DESC
										LIMIT 0, 1'));
					
					if($thread->lastpost_user == 0) $user_id = $thread->PostedBy;
						else $user_id = $thread->lastpost_user;
					$last_post = '<b><a href="?thread='.$thread->pkThread.$gc_helper->getActionString("view").'">'.$gc_helper->shortText($thread->Thread_name).'</a></b><br/>'.$wgLang->timeanddate($thread->lastpost_time).'<br/>'.wfMsg('wikiforum-by').' '.$gc_helper->getUserLinkById($user_id);
				}
				else $last_post = "";
						
				$output .= $gc_gui->getMainBody('<p class="issue">'.$icon.'<a href="?forum='.$forum->pkForum.$gc_helper->getActionString("view").'">'.$forum->Forum_name.'</a></p><p class="descr">'.$forum->Description.'</p>', $forum->num_threads, $forum->num_articles, $last_post, $forum_link, false);
			}			
			$output .= $gc_gui->getMainFooter();
		}
		else
		{
			$this->m_error_t = wfMsg('wikiforum-catnotfound');
			$this->m_error_m = wfMsg('wikiforum-catnotfoundtxt', ' <a href="?overview=true'.$gc_helper->getActionString("view").'">'.wfMsg('wikiforum-overview').'</a>!');
		}
		$output .= $gc_gui->getHeaderRow(0, "", 0, "", $this->getAdminViewLink().$gc_gui->getStatsLink());
		$output .= $this->showFailure();
		return $output;
	}
	
	function showForum($forum_id)
	{
		global $wgOut, $wgLang;
		global $wikiconf;
		global $gc_gui, $gc_helper;
		
		$wrap_request 	= new UniRequest;
		$output 		= $this->showFailure();
		$dbr			= new UniDatabase(DB_SLAVE);
	
		$f_movethread = $wrap_request->getInt('movethread');
		//sorting//
		if($wrap_request->getString('sd')=="up")				$sort_direction = "ASC";
			else 										$sort_direction = "DESC";
			
		if($wrap_request->getString('st')=="answers")			$sort_type = "num_answers";
			else if($wrap_request->getString('st')=="calls")	$sort_type = "num_calls";
			else if($wrap_request->getString('st')=="thread")	$sort_type = "Thread_name";
			else										$sort_type = "lastpost_time";
		//end>sorting//
		//limiting//
		if($wikiconf['max_threads_per_page'] && $wrap_request->getString('lc')>0)						
															$limit_count = $wrap_request->getString('lc');
			else if($wikiconf['max_threads_per_page']>0)	$limit_count = $wikiconf['max_threads_per_page'];
			
		if(is_numeric($wrap_request->getString('lp')))			$limit_page = $wrap_request->getString('lp') - 1;
			else 											$limit_page = 0;
		//end>limiting//

		$sqlData 		= $dbr->query('SELECT pkForum, Forum_name, pkCategory, Category_name, Announcement FROM wikiforum_forums, wikiforum_category WHERE wikiforum_forums.Deleted=0 AND wikiforum_category.Deleted=0 AND fkCategory = pkCategory AND pkForum='.$forum_id);
		$data_overview	= $dbr->fetchObject($sqlData);
		
		if($data_overview && $this->getCategoryAccess($data_overview->pkCategory))
		{
			$sql		 	= 'SELECT t.*, u.user_name FROM wikiforum_threads as t
									LEFT JOIN '.$dbr->tableName('user').' as u ON u.user_id = t.PostedBy 
									WHERE t.Deleted = 0 AND t.fkForum='.$data_overview->pkForum.' 
									ORDER BY Sticky DESC, '.$sort_type.' '.$sort_direction;
			//if($limit_count>0) $sql	= $dbr->limitResult($sql, $limit_count, $limit_page*$limit_count);
			if($limit_count>0) $sql	= $dbr->limit($sql, $limit_count, $limit_page*$limit_count);
			$sqlThreads = $dbr->query($sql);
			
			if($wikiconf['icon_sort_up']) 	$button['up'] 	= '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_sort_up'].'" />';
				else 						$button['up'] 	= ' + ';
			if($wikiconf['icon_sort_down']) $button['down'] = '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_sort_down'].'" />';
				else 						$button['down'] = ' - ';
				
				
			if($data_overview->Announcement == true && $gc_helper->isModerator() == false)
			{
				$write_thread = "";
			}
			else
			{
				if($wikiconf['icon_thread_add']) 
						$icon = '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_thread_add'].'" title="'.wfMsg('wikiforum-writethread').'" /> ';
					else $icon = "";
					
				$write_thread = $icon.'<a href="?writethread='.$data_overview->pkForum.$gc_helper->getActionString("view").'">'.wfMsg('wikiforum-writethread').'</a>';
			}
				
			$output .= $gc_gui->getSearchbox();
			$output .= $gc_gui->getHeaderRow($data_overview->pkCategory, $data_overview->Category_name, $data_overview->pkForum, $data_overview->Forum_name, $write_thread);
			
			$output .= $gc_gui->getMainHeader($data_overview->Forum_name.' <a href="?st=thread&sd=up'.$gc_helper->getActionString("sort").'">'.$button['up'].'</a><a href="?st=thread&sd=down'.$gc_helper->getActionString("sort").'">'.$button['down'].'</a>', 
								wfMsg('wikiforum-answers').' <a href="?st=answers&sd=up'.$gc_helper->getActionString("sort").'">'.$button['up'].'</a><a href="?st=answers&sd=down'.$gc_helper->getActionString("sort").'">'.$button['down'].'</a>', 
								wfMsg('wikiforum-calls').' <a href="?st=calls&sd=up'.$gc_helper->getActionString("sort").'">'.$button['up'].'</a><a href="?st=calls&sd=down'.$gc_helper->getActionString("sort").'">'.$button['down'].'</a>', 
								wfMsg('wikiforum-lastcomment').' <a href="?st=last&sd=up'.$gc_helper->getActionString("sort").'">'.$button['up'].'</a><a href="?st=last&sd=down'.$gc_helper->getActionString("sort").'">'.$button['down'].'</a>',
								false);
			
			$threads_exist = false;
			while ($thread = $dbr->fetchObject($sqlThreads))
			{
				$threads_exist = true;
				$icon = $this->getThreadIcon($thread->Posted, $thread->Closed, $thread->Sticky);
					
				if($thread->num_answers>0) $last_post = $wgLang->timeanddate($thread->lastpost_time).'<br/>by '.$gc_helper->getUserLinkById($thread->lastpost_user);
					else $last_post = "";
					
				if($thread->Sticky == true) $sticky = "sticky";
					else $sticky = false;
	
				$output .= $gc_gui->getMainBody('<p class="thread">'.$icon.'<a href="?thread='.$thread->pkThread.$gc_helper->getActionString("view").'"><nowiki>'.$gc_helper->transformTags($thread->Thread_name).'</nowiki></a>
										<p class="descr">'.wfMsg('wikiforum-posted', $wgLang->timeanddate($thread->Posted),$gc_helper->getUserLink($thread->user_name)).'</p></p>', 
										$thread->num_answers, 
										$thread->num_calls, 
										$last_post,
										false,
										$sticky);
	
			}
			if($threads_exist == false)
			{
				$output .= $gc_gui->getSingleLine(wfMsg('wikiforum-nothreads'), 4);
			}
			$output .= $gc_gui->getMainFooter();
			
			if($limit_count>0) 
			{
				$sql	= 'SELECT COUNT(*) FROM wikiforum_threads WHERE Deleted = 0 AND fkForum='.$data_overview->pkForum;
				$countComments = $dbr->fetchRow($dbr->query($sql));
				$output .= $gc_gui->getFooterRow($limit_page, $countComments["COUNT(*)"], $limit_count);
			}
		}
		else
		{
			$this->m_error_t = wfMsg('wikiforum-forumnotfound');
			$this->m_error_m = wfMsg('wikiforum-forumnotfoundtxt', ' <a href="?'.$gc_helper->getActionString("view").'overview=true'.'">'.wfMsg('wikiforum-overview').'</a>!');
		}
		
		if($f_movethread > 0)
		{
			if($wikiconf['icon_paste_thread']) $icon = '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_paste_thread'].'" title="'.wfMsg('wikiforum-pastethread').'" /> ';
				else $icon = "";
			$pastethread_link = $icon.'<a href="?pastethread='.$f_movethread.$gc_helper->getActionString("action").'">'.wfMsg('wikiforum-pastethread').'</a> ';
		}
		else
		{
			$movethread_link = "";
		}
		$output .= $gc_gui->getHeaderRow(0, "", 0, "", $this->getAdminViewLink()." ".$pastethread_link.$gc_gui->getStatsLink());
		$output .= $this->showFailure();
		return $output;
	}

	function showThread($thread_id)
	{
		global $wgOut, $wgUser, $wgLang;
		global $wikiconf;
		global $gc_gui, $gc_helper;
		
		$wrap_request 	= new UniRequest;
		$output 		= $this->showFailure();
		$dbr			= new UniDatabase(DB_SLAVE);
		$dbw			= new UniDatabase(DB_MASTER);

		$sqlData 		= $dbr->query('SELECT pkThread, Thread_name, Text, pkForum, Forum_name, pkCategory, Category_name, user_name, Sticky, t.Edit, t.EditBy, t.Posted, t.PostedBy, t.Closed, t.ClosedBy
									  FROM wikiforum_forums as f, wikiforum_category as c, wikiforum_threads as t
									  LEFT JOIN '.$dbr->tableName('user').' as u ON user_id = PostedBy 
									  WHERE f.Deleted = 0 AND c.Deleted = 0 AND t.Deleted = 0 AND fkCategory = pkCategory AND pkForum=fkForum AND pkThread='.$thread_id);
		
		$data_overview	= $dbr->fetchObject($sqlData);
		
		//limiting//
		if($wikiconf['max_comments_per_page'] && $wrap_request->getString('lc')>0)						
															$limit_count = $wrap_request->getString('lc');
			else if($wikiconf['max_comments_per_page']>0)	$limit_count = $wikiconf['max_comments_per_page'];
			
		if(is_numeric($wrap_request->getString('lp')))			$limit_page = $wrap_request->getString('lp') - 1;
			else 											$limit_page = 0;
		//end>limiting//
		
		if($data_overview && $this->getCategoryAccess($data_overview->pkCategory))
		{						
			$sql	= 'SELECT c.*, u.user_name FROM wikiforum_comments as c
										  LEFT JOIN '.$dbr->tableName('user').' as u ON user_id = PostedBy 
										  WHERE c.Deleted = 0 AND c.fkThread='.$data_overview->pkThread.'
										  ORDER BY Posted ASC';
			if($limit_count>0) $sql	= $dbr->limit($sql, $limit_count, $limit_page*$limit_count );
			$sqlComments = $dbr->query($sql);
			$dbw->plus('wikiforum_threads', 'num_calls', 'pkThread='.$thread_id);
			
			$edit_buttons = $this->showThreadButtons($data_overview->PostedBy, $data_overview->Closed, $data_overview->pkThread, $data_overview->pkForum);
			
			if($gc_helper->isAdminView())
			{
				$menu_link = "";
				if($data_overview->Sticky == 1)
				{
					if($wikiconf['icon_sticky_delete']) $icon = '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_sticky_delete'].'" title="'.wfMsg('wikiforum-removesticky').'" /> ';
						else $icon = "";
					$menu_link = $icon.'<a href="?removesticky='.$data_overview->pkThread.$gc_helper->getActionString("view").'">'.wfMsg('wikiforum-removesticky').'</a> ';
				}
				else
				{
					if($wikiconf['icon_sticky_add']) $icon = '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_sticky_add'].'" title="'.wfMsg('wikiforum-makesticky').'" /> ';
						else $icon = "";
					$menu_link = $icon.'<a href="?makesticky='.$data_overview->pkThread.$gc_helper->getActionString("view").'">'.wfMsg('wikiforum-makesticky').'</a> ';
				}
			}	
			
			if($wikiconf['icon_comment_add']) $icon = '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_comment_add'].'" title="'.wfMsg('wikiforum-writecomment').'" /> ';
				else $icon = "";
			if($data_overview->Closed == 0) $menu_link .= $icon.'<a href="#writecomment">'.wfMsg('wikiforum-writecomment').'</a>';
			
			$posted = wfMsg('wikiforum-posted', $wgLang->timeanddate($data_overview->Posted),$gc_helper->getUserLink($data_overview->user_name));
			if($data_overview->Edit > 0)
			{
				$posted .= '<br/><i>'.wfMsg('wikiforum-edited', $wgLang->timeanddate($data_overview->Edit),$gc_helper->getUserLinkById($data_overview->EditBy)).'</i>';
			}
			
			$output .= $gc_gui->getSearchbox();
			$output .= $gc_gui->getHeaderRow($data_overview->pkCategory, $data_overview->Category_name, $data_overview->pkForum, $data_overview->Forum_name, $menu_link);
			
			$output .= $gc_gui->getThreadHeader($gc_helper->transformTags($data_overview->Thread_name), $gc_helper->parseIt($data_overview->Text), $posted, $edit_buttons, $data_overview->pkThread);

			while ($comment = $dbr->fetchObject($sqlComments))
			{				
				$edit_buttons = $this->showCommentButtons($comment->PostedBy, $comment->pkComment, $data_overview->pkThread, $data_overview->Closed);
				
				$posted = wfMsg('wikiforum-posted', $wgLang->timeanddate($comment->Posted),$gc_helper->getUserLink($comment->user_name));
				if($comment->Edit > 0)
				{
					$posted .= '<br/><i>'.wfMsg('wikiforum-edited', $wgLang->timeanddate($comment->Edit), $gc_helper->getUserLinkById($comment->EditBy)).'</i>';
				}

				$output .= $gc_gui->getComment($gc_helper->parseIt($comment->Comment), $posted, $edit_buttons, $comment->pkComment);

			}
			
			$output .= $gc_gui->getThreadFooter();
			
			if($limit_count>0) 
			{
				$sql	= 'SELECT COUNT(*) FROM wikiforum_comments WHERE Deleted = 0 AND fkThread='.$data_overview->pkThread;
				$countComments = $dbr->fetchRow($dbr->query($sql));
				$output .= $gc_gui->getFooterRow($limit_page, $countComments["COUNT(*)"], $limit_count);
			}
			
			$mod_editcomment 	= $wrap_request->getInt('editcomment');
			$mod_form 			= $wrap_request->getBool('form');
			if($data_overview->Closed == 0 || (isset($mod_editcomment) && $mod_editcomment > 0 && $mod_form!=true && $gc_helper->isModerator()))
			{
				$output .= $this->showEditor($data_overview->pkThread, "addcomment");
			}
			else
			{
				$this->m_error_t = wfMsg('wikiforum-threadclosed');
				$this->m_error_m = wfMsg('wikiforum-error_threadclosed');
				$this->m_error_i = 'icon_thread_closed';
			}
		}
		else
		{
			$this->m_error_t = wfMsg('wikiforum-threadnotfound');
			$this->m_error_m = wfMsg('wikiforum-threadnotfoundtxt', ' <a href="?overview=true'.$gc_helper->getActionString("view").'">'.wfMsg('wikiforum-overview').'</a>!');
		}
		
		if($gc_helper->isModerator() == true)
		{
			if($wikiconf['icon_move_thread']) $icon = '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_move_thread'].'" title="'.wfMsg('wikiforum-movethread').'" /> ';
				else $icon = "";
			$movethread_link = $icon.'<a href="?movethread='.$data_overview->pkThread.$gc_helper->getActionString("action").'">'.wfMsg('wikiforum-movethread').'</a> ';
		}
		else
		{
			$movethread_link = "";
		}
		$output .= $gc_gui->getHeaderRow(0, "", 0, "", $this->getAdminViewLink()." ".$movethread_link.$gc_gui->getStatsLink());
		$output .= $this->showFailure();
		return $output;
	}

	function showSearchResults($search_string)
	{
		global $wgOut, $wgUser, $wgLang;
		global $wikiconf;
		global $gc_gui, $gc_helper;
		
		$search_string = trim($search_string);
				
		$output 		= $this->showFailure();
		$output 	   .= $gc_gui->getSearchbox();
		$output 	   .= $gc_gui->getHeaderRow(0, "", 0, "", "");
		
		if(strlen($search_string)>2)
		{
			$dbr					= new UniDatabase(DB_SLAVE);
			$i						= 0;
			$search_string_array 	= $dbr->searchString($search_string);
			$search_string			= $search_string_array[0];
			
			$sqlData 				= $dbr->query('SELECT c.Posted, c.PostedBy, Thread_name, pkThread, Comment as Search, pkComment, fkCategory
										  FROM wikiforum_forums as f, wikiforum_threads as t, wikiforum_comments as c
											LEFT JOIN '.$dbr->tableName('user').' ON user_id = c.PostedBy
										  WHERE c.Deleted = 0 AND t.Deleted = 0 AND pkThread = fkThread AND Comment LIKE "'.$search_string.'"
										   AND pkForum = fkForum 
										  UNION ALL
										  SELECT t.Posted, t.PostedBy, Thread_name, pkThread, Text as Search, 0, fkCategory
										  FROM wikiforum_forums as f, wikiforum_threads as t
											LEFT JOIN '.$dbr->tableName('user').' ON user_id = t.PostedBy
										  WHERE t.Deleted = 0 AND (Text LIKE "'.$search_string.'" OR Thread_name LIKE "'.$search_string.'")
										  AND pkForum = fkForum 
										  ORDER BY Posted DESC LIMIT 0, 30');//*/
			
			while ($result = $dbr->fetchObject($sqlData))
			{			
				if($this->getCategoryAccess($result->fkCategory))
				{
					if($result->pkComment > 0) $anker = "#comment_".$result->pkComment;
						else $anker = "";
					$posted = wfMsg('wikiforum-posted', $wgLang->timeanddate($result->Posted),$gc_helper->getUserLinkById($result->PostedBy)).
								'<br/>'.wfMsg('wikiforum-thread').': <a href="?thread='.$result->pkThread.$gc_helper->getActionString('view').$anker.'">'.$result->Thread_name.'</a>';
					$output_temp .= $gc_gui->getComment($gc_helper->parseIt($result->Search), $posted, false, $comment->pkComment);
					$i++;
				}
			}
			
			$title = 'Found: '.$i.' hits';				
			$output .= $gc_gui->getCommentHeader($title);
			$output .= $output_temp;
			$output .= $gc_gui->getThreadFooter();
		}
		else
		{
			$this->m_error_t = wfMsg('wikiforum-error_search');
			$this->m_error_m = wfMsg('wikiforum-error_tooshort');
			$this->m_error_i = 'icon_failure';
		}
		$output .= $this->showFailure();
		return $output;
	}
	
	
	function showStatistics()
	{
		global $wgOut, $wgLang;
		global $wikiconf;
		global $gc_gui, $gc_helper;
		if($wikiconf['showstats'] == true)
		{
			$output 		= $this->showFailure();
			$dbr			= new UniDatabase(DB_SLAVE);
			
			$temp 					= $dbr->fetchObject($dbr->query('SELECT COUNT(*) as num FROM wikiforum_category WHERE Deleted = 0'));
			$total['categories']	= $temp->num;
			$temp 					= $dbr->fetchObject($dbr->query('SELECT COUNT(*) as num FROM wikiforum_forums WHERE Deleted = 0'));
			$total['forums']		= $temp->num;
			$temp 					= $dbr->fetchObject($dbr->query('SELECT COUNT(*) as num, SUM(num_calls) as views FROM wikiforum_threads WHERE Deleted = 0'));
			$total['threads']		= $temp->num;
			$total['views']			= $temp->views;
			$temp 					= $dbr->fetchObject($dbr->query('SELECT COUNT(*) as num FROM wikiforum_comments WHERE Deleted = 0'));
			$total['posts']			= $total_threads + $temp->num;
			
			if(isset($wikiconf['install_date']) && $wikiconf['install_date'] > 0)
			{
				$days = (time() - $wikiconf['install_date']) / 3600 / 24;
				$average['posts']		= round($total['posts'] / $days, 2);
				$average['threads']		= round($total['threads'] / $days, 2);
				$average['views']		= round($total['views'] / $days, 2);
			}
			
			$top['threads_by_replies'] 	= $dbr->query('SELECT t.*, f.fkCategory FROM wikiforum_threads as t, wikiforum_forums as f WHERE t.Deleted = 0 AND fkForum = pkForum AND num_answers > 0 ORDER BY num_answers DESC LIMIT 0, 15');
			$top['threads_by_views'] 	= $dbr->query('SELECT t.*, f.fkCategory FROM wikiforum_threads as t, wikiforum_forums as f WHERE t.Deleted = 0 AND fkForum = pkForum AND num_calls > 0 ORDER BY num_calls DESC LIMIT 0, 15');
			$top['users_by_threads'] 	= $dbr->query('SELECT user_id, user_name, COUNT(*) as num FROM '.$dbr->tableName('user').', wikiforum_threads WHERE PostedBy = user_id AND Deleted = 0 GROUP BY user_name ORDER BY num DESC LIMIT 0, 10');
			$top['users_by_replies'] 	= $dbr->query('SELECT user_id, user_name, COUNT(*) as num FROM '.$dbr->tableName('user').', wikiforum_comments WHERE PostedBy = user_id AND Deleted = 0 GROUP BY user_name ORDER BY num DESC LIMIT 0, 10');
			
			$output .= $gc_gui->getStatsPage($total, $average, $top);
		}
		$output .= $this->showFailure();
		return $output;
	}

	function previewIssue($type, $id, $previewTitle, $previewText)
	{
		global $wgOut, $wgUser, $wgLang;
		global $wikiconf;
		global $gc_gui, $gc_helper;
		
		$output = $this->showFailure();
		
		$title = wfMsg('wikiforum-preview');
		if($previewTitle) $title .= ": $previewTitle";
									
		$posted = wfMsg('wikiforum-posted', $wgLang->timeanddate(time()),$gc_helper->getUserLinkById($wgUser->getID()));
		if($type == "addcomment" || $type == "editcomment")
		{
			$output .= $gc_gui->getCommentHeader($title);
			$output .= $gc_gui->getComment($gc_helper->parseIt($previewText), $posted, false, 0);
		}
		else
		{
			$output .= $gc_gui->getThreadHeader($title, $gc_helper->parseIt($previewText), $posted, false, false);
		}
		$output .= $gc_gui->getThreadFooter();

		$this->m_result = false;
		
		$output .= $this->showEditor($id, $type);

		$output .= $gc_gui->getHeaderRow(0, "", 0, "", $this->getAdminViewLink());
		$output .= $this->showFailure();
		return $output;
	}

	function writeThread($forum_id)
	{
		global $gc_gui, $gc_helper;
		
		$wrap_request 	= new UniRequest;
		$output 		= $this->showFailure();
		$dbr			= new UniDatabase(DB_SLAVE);

		$mod_editthread = $wrap_request->getInt('editthread');
		if($mod_editthread)
		{
			$sqlData 		= $dbr->query('SELECT pkForum, Forum_name, pkCategory, Category_name, Announcement
										  FROM wikiforum_forums as f, wikiforum_category as c, wikiforum_threads as t
										  WHERE f.Deleted = 0 AND c.Deleted = 0 AND t.Deleted = 0 AND fkCategory = pkCategory AND pkForum=fkForum AND pkThread='.$forum_id);
		}
		else
		{
			$sqlData 		= $dbr->query('SELECT pkForum, Forum_name, pkCategory, Category_name, Announcement
										  FROM wikiforum_forums as f, wikiforum_category as c 
										  WHERE f.Deleted = 0 AND c.Deleted = 0 AND fkCategory = pkCategory AND pkForum='.$forum_id);
		}
		$data_overview	= $dbr->fetchObject($sqlData);
		
		if(($data_overview->Announcement == false || $gc_helper->isModerator() == true) && $this->getCategoryAccess($data_overview->pkCategory))
		{
			$output .= $gc_gui->getHeaderRow($data_overview->pkCategory, $data_overview->Category_name, $data_overview->pkForum, $data_overview->Forum_name, "");
			$output .= $this->showEditor($data_overview->pkForum, "addthread");
			$output .= $this->showFailure();
		}
		return $output;
	}
	
	function showEditorCatForum($id, $type, $values)
	{
		global $gc_gui, $gc_helper;
		$dbr			= new UniDatabase(DB_SLAVE);
		$save_button 	= wfMsg('wikiforum-save');
		if(isset($values['text']) && strlen($values['text'])>0) $text_prev = $values['text'];
		
		if($type == "addcategory")
		{
			$cat_name = wfMsg('wikiforum-addcategory');
		}
		else if($type == "editcategory")
		{
			$data_overview 		= $dbr->fetchObject($dbr->query('SELECT pkCategory, Category_name
										  FROM wikiforum_category
										  WHERE Deleted = 0 AND pkCategory = '.$id));
			$id 		= $data_overview->pkCategory;
			$title_prev = $data_overview->Category_name;
			$cat_name 	= wfMsg('wikiforum-editcategory');
			
			$object_parameter 	= $dbr->query('SELECT *  FROM wikiforum_cataccess WHERE fkCategory = '.$id);
		}
		else if($type == "addforum")
		{
			$cat_name = wfMsg('wikiforum-addforum');
		}
		else if($type == "editforum")
		{
			$data_overview 		= $dbr->fetchObject($dbr->query('SELECT pkForum, Forum_name, Description, Announcement
										  FROM wikiforum_forums
										  WHERE Deleted = 0 AND pkForum = '.$id));
			$id 		= $data_overview->pkForum;
			$title_prev = $data_overview->Forum_name;
			if(strlen($text_prev)==0) $text_prev 	= $data_overview->Description;
			$cat_name 	= wfMsg('wikiforum-editforum');
			
			$object_parameter = $data_overview;
		}
		$action 		= "$type=$id".$gc_helper->getActionString("view");
		
		$output .= $gc_gui->getFormCatForum($type, $cat_name, $action, $title_prev, $text_prev, $save_button, $object_parameter);
		$output .= $this->showFailure();
		return $output;
	}
	
	function showEditor($id, $type)
	{
		global $wgLang;
		global $gc_gui, $gc_helper;
		
		$wrap_request 	= new UniRequest;
		$dbr			= new UniDatabase(DB_SLAVE);

		if($this->m_result == false) 
		{
			$text_prev 	= $wrap_request->getString('frmText');
			$title_prev	= $wrap_request->getString('frmTitle');
		}
		else
		{
			$title_prev = wfMsg('wikiforum-threadtitle');
		}
		
		if($type == "addthread" || $type == "editthread")
		{
			$mod_editthread = $wrap_request->getInt('editthread');
			$mod_preview 	= $wrap_request->getBool('butPreview');
			if($mod_editthread && $mod_editthread>0)
			{
				if(!$text_prev || !$title_prev || $mod_preview==true)
				{
					$data_thread 	= $dbr->fetchObject($dbr->query('SELECT pkThread, Thread_name, Text FROM wikiforum_threads WHERE Deleted=0 AND pkThread='.$mod_editthread));
					$action 		= "editthread=".$data_thread->pkThread;
					if(!$text_prev)		$text_prev 	= $data_thread->Text;
					if($title_prev == wfMsg('wikiforum-threadtitle'))	$title_prev	= $data_thread->Thread_name;
				}
			}
			else
			{
				$action = "addthread=$id";
			}
			$height = "25em";
			$save_button = wfMsg('wikiforum-savethread');
			$input	= $gc_gui->getInput($title_prev);
		} else { //add comment
			$mod_comment 	= $wrap_request->getInt('editcomment');
			$mod_form 		= $wrap_request->getBool('form');
			$mod_preview 	= $wrap_request->getBool('butPreview');
			$mod_quotec 	= $wrap_request->getInt('quotecomment');
			$mod_quotet 	= $wrap_request->getInt('quotethread');
			
			//quote
			if(isset($mod_quotec) && $mod_quotec > 0)
			{
				$data_comment 	= $dbr->fetchObject($dbr->query('SELECT Comment, Posted, user_name FROM wikiforum_comments 
																LEFT JOIN '.$dbr->tableName('user').' ON user_id = PostedBy 
																WHERE Deleted=0 AND pkComment='.$mod_quotec));
				if($data_comment)
				{
					var_dump($data_comment->PostedBy);
					$posted = wfMsg('wikiforum-posted', $wgLang->timeanddate($data_comment->Posted),$data_comment->user_name);
					$text_prev	= '[quote='.$posted.']'.$data_comment->Comment.'[/quote]';
				}
			}
			else if(isset($mod_quotet) && $mod_quotet > 0)
			{
				$data_thread 	= $dbr->fetchObject($dbr->query('SELECT Text, user_name FROM wikiforum_threads 
																LEFT JOIN '.$dbr->tableName('user').' ON user_id = PostedBy 
																WHERE Deleted=0 AND pkThread='.$mod_quotet));
				if($data_thread)
				{
					$posted = wfMsg('wikiforum-posted', $wgLang->timeanddate($data_thread->Posted),$data_thread->user_name);
					$text_prev	= '[quote='.$posted.']'.$data_thread->Text.'[/quote]';
				}
			}
			//end>quote
			
			if(isset($mod_comment) && $mod_comment>0 && ($mod_form != true || $mod_preview == true))
			{
				if($mod_preview == true) $id = $wrap_request->getInt('thread');
				$dbr = new UniDatabase(DB_SLAVE);
				$data_comment = $dbr->fetchObject($dbr->query('SELECT pkComment, Comment FROM wikiforum_comments WHERE Deleted=0 AND pkComment='.$mod_comment));
				$action = "thread=$id&form=true&editcomment=".$data_comment->pkComment;
				if($mod_preview != true) $text_prev = $data_comment->Comment;
			}
			else
			{
				$action = "addcomment=$id&thread=$id";
			}
			$height = "10em";
			$input	= "";
			$save_button = wfMsg('wikiforum-savecomment');
		}
		$action .= $gc_helper->getActionString("view");
		return $gc_gui->getWriteForm($type, $action, $input, $height, $text_prev, $save_button);
	}
	
	function showCommentButtons($postedby, $pkComment, $pkThread, $closed)
	{
		global $wgUser;
		global $wikiconf;
		global $gc_helper;
		
		$edit_buttons  .= '<a href="?thread='.$pkThread.'&quotecomment='.$pkComment.$gc_helper->getActionString("view").'#writecomment">';
		if($wikiconf['icon_quote']) $edit_buttons  .= '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_quote'].'" title="'.wfMsg('wikiforum-quote').'" />';
			else $edit_buttons  .= wfMsg('wikiforum-quote').' /';
				
		if(($wgUser->getID() == $postedby && $closed == 0) || $gc_helper->isModerator())
		{
			$edit_buttons  .= '</a>&nbsp;&nbsp;<a href="?thread='.$pkThread.'&editcomment='.$pkComment.$gc_helper->getActionString("view").'#writecomment">';
			if($wikiconf['icon_comment_edit']) $edit_buttons  .= '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_comment_edit'].'" title="'.wfMsg('wikiforum-editcomment').'" />';
				else $edit_buttons  .= wfMsg('wikiforum-edit').' /';
			$edit_buttons  .= '</a> <a href="?thread='.$pkThread.'&deletecomment='.$pkComment.$gc_helper->getActionString("view").'">';
			if($wikiconf['icon_comment_delete']) $edit_buttons  .= '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_comment_delete'].'" title="'.wfMsg('wikiforum-deletecomment').'" />';
				else $edit_buttons  .= wfMsg('wikiforum-delete');
		}
		$edit_buttons  .= '</a>';
		
		return $edit_buttons;
	}
	
	function showThreadButtons($postedby, $closed, $pkThread, $pkForum)
	{
		global $wgUser;
		global $wikiconf;
		global $gc_helper;
		
		$edit_buttons  .= '<a href="?thread='.$pkThread.'&quotethread='.$pkThread.$gc_helper->getActionString("view").'#writecomment">';
		if($wikiconf['icon_quote']) $edit_buttons  .= '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_quote'].'" title="'.wfMsg('wikiforum-quote').'" />';
			else $edit_buttons  .= wfMsg('wikiforum-quote').' /';
		$edit_buttons  .= '</a>';
		
		if($wgUser->getID() == $postedby || $gc_helper->isModerator())
		{
			$edit_buttons  .= ' <a href="?editthread='.$pkThread.$gc_helper->getActionString("view").'">';
			if($wikiconf['icon_thread_edit']) $edit_buttons  .= '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_thread_edit'].'" title="'.wfMsg('wikiforum-editthread').'" />';
				else $edit_buttons  .= wfMsg('wikiforum-edit').' /';
			$edit_buttons  .= '</a> <a href="?forum='.$pkForum.'&deletethread='.$pkThread.$gc_helper->getActionString("view").'">';
			if($wikiconf['icon_thread_delete']) $edit_buttons  .= '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_thread_delete'].'" title="'.wfMsg('wikiforum-deletethread').'" />';
				else $edit_buttons  .= wfMsg('wikiforum-delete').' /';
			$edit_buttons  .= '</a> ';

			if($gc_helper->isModerator())
			{
				if($closed == 0)
				{
					$edit_buttons  .= ' <a href="?closethread='.$pkThread.$gc_helper->getActionString("view").'">';
					if($wikiconf['icon_thread_close']) $edit_buttons  .= '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_thread_close'].'" title="'.wfMsg('wikiforum-closethread').'" />';
						else $edit_buttons  .= wfMsg('wikiforum-close');
					$edit_buttons  .= '</a>';
				}
				else
				{
					$edit_buttons  .= ' <a href="?reopenthread='.$pkThread.$gc_helper->getActionString("view").'">';
					if($wikiconf['icon_thread_reopen']) $edit_buttons  .= '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_thread_reopen'].'" title="'.wfMsg('wikiforum-reopenthread').'" />';
						else $edit_buttons  .= wfMsg('wikiforum-reopen');
					$edit_buttons  .= '</a>';
				}
			}
		}
		
		return $edit_buttons;
	}
	
	function showAdminIcons($type, $id, $sortup, $sortdown)
	{
		global $wikiconf;
		global $gc_helper;
		
		if($gc_helper->isAdminView())
		{
			if($wikiconf['icon_'.$type.'_edit']) $icon = '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_'.$type.'_edit'].'" title="'.wfMsg('wikiforum-edit'.$type).'" />';
				else $icon = '<span class="small">'.wfMsg('wikiforum-edit').'</span>';
			$link = ' <a href="?edit'.$type.'='.$id.$gc_helper->getActionString("view").'">'.$icon.'</a>';
			
			if($wikiconf['icon_'.$type.'_delete']) $icon = '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_'.$type.'_delete'].'" title="'.wfMsg('wikiforum-delete'.$type).'" />';
				else $icon = '<span class="small">'.wfMsg('wikiforum-delete').'</span>';
			$link .= ' <a href="?delete'.$type.'='.$id.$gc_helper->getActionString("view").'">'.$icon.'</a>';
			
			if($sortup == true)
			{
				if($wikiconf['icon_sortkey_up']) $icon = '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_sortkey_up'].'" title="'.wfMsg('wikiforum-sort-up').'" />';
					else $icon = '<span class="small">'.wfMsg('wikiforum-sort-up').'</span>';
				$link .= ' <a href="?'.$type.'up='.$id.$gc_helper->getActionString("view").'">'.$icon.'</a>';
			}
			
			if($sortdown == true)
			{
				if($wikiconf['icon_sortkey_down']) $icon = '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_sortkey_down'].'" title="'.wfMsg('wikiforum-sort-down').'" />';
					else $icon = '<span class="small">'.wfMsg('wikiforum-sort-down').'</span>';
				$link .= ' <a href="?'.$type.'down='.$id.$gc_helper->getActionString("view").'">'.$icon.'</a>';
			}
		}

		return $link;
	}
	
	function showCss()
	{
		global $wikiconf;
		return '<link rel="stylesheet" href="'.$wikiconf['extension_folder'].'styles.css" />';
	}
	
	function getThreadIcon($posted, $closed, $sticky)
	{
		global $wikiconf;
		
		if($wikiconf['icon_sticky'] && $sticky == 1)							return '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_sticky'].'" title="'.wfMsg('wikiforum-sticky').'" /> ';
		else if($wikiconf['icon_thread_closed'] && $closed > 0) 				return '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_thread_closed'].'" title="'.wfMsg('wikiforum-threadclosed').'" /> ';
		else if($wikiconf['icon_thread_new'] && $posted + (86400*$wikiconf['daydefinition_new']) > time()) return '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_thread_new'].'" title="'.wfMsg('wikiforum-newthread').'" /> ';
		else if($wikiconf['icon_thread']) 										return '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_thread'].'" title="'.wfMsg('wikiforum-thread').'" /> ';
		else																	return '';
	}
	
	function showFailure()
	{
		global $wikiconf;
		
		if(strlen($this->m_error_t)>0)
		{
			if(strlen($this->m_error_i)>0)	
			{
				if($wikiconf[$this->m_error_i]) $icon = '<img src="'.$wikiconf['extension_folder'].$wikiconf[$this->m_error_i].'" /> ';
					else $icon = "";
			}
			else
			{
				if($wikiconf['icon_failure']) $icon = '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_failure'].'" title="'.wfMsg('wikiforum-threadclosed').'" /> ';
					else $icon = "";
			}
			$output	= '<br/><table class="frame"><tr><td>'.$icon.$this->m_error_t.'<p class="descr">'.$this->m_error_m.'</p></td></tr></table>';
		}
		$this->m_error_t 	= "";
		$this->m_error_m 	= "";
		return $output;
	}
	
	function getAdminViewLink()
	{
		global $wikiconf;
		global $gc_helper;

		if($gc_helper->isAdmin())
		{			
			if($gc_helper->isAdminView() == true)
			{
				if($wikiconf['icon_normal_view']) $icon = '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_normal_view'].'" title="'.wfMsg('wikiforum-normalview').'" /> ';
					else $icon = "";
				$output = $icon.'<a href="?'.$gc_helper->getActionString("adminview").'">'.wfMsg('wikiforum-normalview').'</a> ';
			}
			else
			{
				if($wikiconf['icon_admin_view']) $icon = '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_admin_view'].'" title="'.wfMsg('wikiforum-adminview').'" /> ';
					else $icon = "";
				$output = $icon.'<a href="?adminview=true'.$gc_helper->getActionString("adminview").'">'.wfMsg('wikiforum-adminview').'</a> ';
			}
			
		}
		else $output = "";
		
		return $output;
	}
	
	function getCategoryAccess($cat_id)
	{		
		global $wgUser;
		global $gc_helper;
		$access		= true;
		
		if(!$gc_helper->isAdmin())
		{
			$dbr = new UniDatabase(DB_SLAVE);
			$sqlAccess 	= $dbr->query('SELECT GroupeRight FROM wikiforum_cataccess WHERE fkCategory='.$cat_id);
			while ($cat_access = $dbr->fetchObject($sqlAccess)) 
			{
				$access		= false;
				if($wgUser->isAllowed($cat_access->GroupeRight))
				{
					$access		= true;
					break;
				}
			}
		}
		return $access;
	}
}
?>
