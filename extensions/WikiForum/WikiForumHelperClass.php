<?php

class WikiForumHelper 
{ 
	function cleanString($text)
	{
		return $text;
	}
	
	function getUserLink($username)
	{
		global $wgContLang, $wgUser;
		
		if($username)
		{
			$sk 	= $wgUser->getSkin();
			//return $sk->makeLink($wgContLang->getNsText(NS_USER) . ':' . $username, htmlspecialchars($username));
			if($user_id==0) 	return $username;
				else			return $this->getUserLink(User::whoIs($user_id));
			
		}
		else
		{
			return "Anonymous";
		}
	}
	
	function getUserLinkById($user_id)
	{
		if($user_id==0) 	return "Anonymous";
			else			return $this->getUserLink(User::whoIs($user_id));
	}
		
	function isAdmin()
	{
		global $wgUser;
		return $wgUser->isAllowed("wikiforumadmin");
	}
	
	function isModerator()
	{
		global $wgUser;
		return $wgUser->isAllowed("wikiforummod");
	}
	
	function isAnonym()
	{
		global $wgUser;
		if($wgUser->getID() > 0) return false;
			else return true;
	}
		
	function isAdminView()
	{
		$wrap_request 	= new UniRequest;
		if($this->isAdmin())	return $wrap_request->getBool('adminview');
			else				false;
	}
		
	function prepareSmilies($text)
	{
		global $smilies;
		
		if(is_array($smilies))
		{
			foreach($smilies as $key => $icon)
			{
				$text = str_replace($key, '<nowiki>'.$key.'</nowiki>', $text);
			}
		}
		return $text;
	}
	
	function getSmilies($text)
	{
		global $smilies;
		global $wikiconf;
		
		//damn unclear code => need a better preg_replace patter to simplify
		
		
		if(is_array($smilies))
		{
			foreach($smilies as $key => $icon)
			{
				$text = str_replace($key, '<img src="'.$wikiconf["extension_folder"].$icon.'" title="'.$key.'"/>', $text);
				$text = str_replace('&lt;nowiki&gt;<img src="'.$wikiconf["extension_folder"].$icon.'" title="'.$key.'"/>&lt;/nowiki&gt;', $key, $text);
				$text = preg_replace('/\&lt;nowiki\&gt;(.+)\&lt;\/nowiki\&gt;/iUs','\1',$text);
			}
		}
		return $text;
	}
	
	function parseIt($text)
	{
		global $wikiconf;
		global $wgOut;
		
		//add smilies for comment text//
		if($wikiconf["disable_smilies"] == false) 
		{
			$text = $this->prepareSmilies($text);
		}
		$text = $wgOut->parse($text);
		$text = $this->parseLinks($text);
		$text = $this->parseQuotes($text);
		if($wikiconf["disable_smilies"] == false) 
		{
			$text = $this->getSmilies($text);
		}
		//add smilies for comment text//
		
		return $text;
	}
	
	function parseLinks($text)
	{
		$text = preg_replace_callback('/\[thread#(.*?)\]/i',Array($this,"getThreadTitle"),$text);
		return $text;
	}
	
	function parseQuotes($text)
	{
		$text = preg_replace('/\[quote=(.*?)\]/','<blockquote><p class="posted">\1</p><span>&raquo;</span>',$text);
		$text = str_replace('[quote]','<blockquote><span>&raquo;</span>',$text);
		$text = str_replace('[/quote]','<span>&laquo;</span></blockquote>',$text);
		return $text;
	}

	function deleteTags($text)
	{
		$text = preg_replace('/\<WikiForumThread id=(.*?)\/\>/','&lt;WikiForumThread id=\1/&gt;',$text);
		$text = preg_replace('/\<WikiForumList(.*)\/>/','&lt;WikiForumList \1/&gt;',$text);
		return $text;
	}
	
	function transformTags($text)
	{
		$text = htmlspecialchars($text);
		return $text;
	}
	
	function getThreadTitle($id)
	{
		if(is_numeric($id[1]) && $id[1] > 0)
		{
			$dbr			= new UniDatabase(DB_SLAVE);
			$data_overview	= $dbr->fetchObject($dbr->query('SELECT Thread_name FROM wikiforum_threads WHERE Deleted = 0 AND pkThread='.$id[1]));
			
			if($data_overview) return '<i><a href="?thread='.$id[1].$this->getActionString('view').'">'.$data_overview->Thread_name.'</a></i>';
				else return '[thread deleted]';
		}
		return $id[0];
	}
	
	function shortText($text)
	{
		$max_size = 25;
		if(strlen($text) > $max_size)
		{
			$text = substr($text, 0, ($max_size-3)).'...';
		}
		
		return $text;
	}
	
	function getAllPermissions()
	{
		global $wgGroupPermissions;
		$list = array();
		foreach($wgGroupPermissions as $group)
		{
			foreach($group as $key => $permission)
			{
				if(!in_array($key, $list)) array_push($list, $key);
			}
		}
		
		array_multisort($list, SORT_ASC, SORT_STRING);
		return $list;
	}
	
	function getActionString($selfkey)
	{
		global $wikiconf;
		
		$actions 		= array();
		$wrap_request 	= new UniRequest;
		
		if($selfkey != "adminview") 	$adminview 	= $wrap_request->getBool('adminview');
		if($selfkey != "sort")
		{
										$st 		= $wrap_request->getString('st');
										$sd 		= $wrap_request->getString('sd');
		}
		if($selfkey != "limit") 		$lc 		= $wrap_request->getInt('lc');
		if($selfkey != "view")
		{
										$category 	= $wrap_request->getInt('category');
										$forum 		= $wrap_request->getInt('forum');
										$thread 	= $wrap_request->getInt('thread');
		}
		if($selfkey != "action") 		
		{
										$movethread = $wrap_request->getInt('movethread');
		}
		else if(isset($thread))
		{
			unset($thread);
		}
		
		if($adminview == true) 						array_push($actions, 'adminview=true');
		if(isset($st) && $st != "") 				array_push($actions, 'st='.$st);
		if(isset($sd) && $sd != "") 				array_push($actions, 'sd='.$sd);
		if(isset($lc) && $lc > 0) 					array_push($actions, 'lc='.$lc);
		if(isset($category) && $category > 0) 		array_push($actions, 'category='.$category);
		if(isset($forum) && $forum > 0) 			array_push($actions, 'forum='.$forum);
		if(isset($thread) && $thread > 0) 			array_push($actions, 'thread='.$thread);
		if(isset($movethread) && $movethread > 0) 	array_push($actions, 'movethread='.$movethread);
		
		$output = "";
		$output = implode("&", $actions);
		if(strlen($output) > 0) $output = "&".$output;
		$output .= $wikiconf['action_string'];
		
		return $output;
	}
}
?>
