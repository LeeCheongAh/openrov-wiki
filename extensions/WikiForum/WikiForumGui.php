<?php
class WikiForumGui
{
	function getFrameHeader()
	{		
		return '<table class="frame" cellspacing="10"><tr><td class="innerframe">';
	}
	function getFrameFooter()
	{		
		return '</td></tr></table>';
	}
	
	function getSearchbox()
	{		
		global $wikiconf;
		global $gc_helper;
		
		if($wikiconf['icon_search']) $icon = '<img src="'.$wikiconf['extension_folder'].$wikiconf['icon_search'].'" id="searchbox_picture" title="Search" />';
			else $icon = "<small>Search</small>";
			
		$output = '<div id="testsearch"><div id="searchbox"><form method="post" action="?'.$gc_helper->getActionString('view').'"><div id="searchbox_border">'.$icon.'
			<input type="text" value="" name="txtSearch" id="txtSearch" /></div>
		</form></div></div>';
		
		return $output;
	}
	
	function getHeaderRow($cat_id, $cat_name, $forum_id, $forum_name, $additional_links)
	{		
		global $wgUser;
		global $wikiconf;
		global $gc_helper;
		
		$output = '<table class="headerrow"><tr><td class="leftside">';
		if(strlen($additional_links) == 0 || $cat_id > 0 && strlen($cat_name) > 0) 
		{
			$output .= '<a href="?overview=true'.$gc_helper->getActionString("view").'">'.wfMsg('wikiforum-overview').'</a>';
			if($cat_id > 0 && strlen($cat_name) > 0)
			{
				$output .= ' &gt; <a href="?category='.$cat_id.$gc_helper->getActionString("view").'">'.$cat_name.'</a>';
				if($forum_id > 0 && strlen($forum_name) > 0)
				{
					$output .= ' &gt; <a href="?forum='.$forum_id.$gc_helper->getActionString("view").'">'.$forum_name.'</a>';
				}
			}
		}
		if(strlen($additional_links) > 0 && ($wikiconf['anonymous_allowed'] == true || $wgUser->getID() > 0))
		{
			$output .= '</td><td class="rightside">'.$additional_links;
		}
		$output .= '</td></tr></table>';
		return $output;
	}
	
	function getFooterRow($page, $maxissues, $limit)
	{		
		global $wikiconf;
		global $gc_helper;
		
		$def_limits	= array(5, 10, 20, 50);
		if($maxissues/$limit > 1)
		{
			$output = '<table class="footerrow"><tr><td class="leftside">'.wfMsg('wikiforum-pages').': ';
			for($i = 1; $i < ($maxissues/$limit) + 1; $i++)
			{
				if($i != $page+1) $output .= '<a href="?lp='.$i.$gc_helper->getActionString("lp").'">';
					else $output .= '[';
					
				if($i <= 9) $output .= '0'.$i;
					else	$output .= $i;
					
				if($i != $page+1) $output .= '</a>';
					else $output .= ']';

				$output .= ' ';
			}
			$output .= '</td><td class="rightside">';
			foreach($def_limits as $def_limit)
			{
				//$output .= '[<a href="?lc='.$def_limit.$gc_helper->getActionString("lc").'">'.$def_limit.'</a>] ';
			}
			$output .= '</td></tr></table>';
		}
		return $output;
	}
	
	function getMainHeader($col_title1, $col_title2, $col_title3, $col_title4, $col_title5)
	{		
		return $this->getFrameHeader().'<table class="title">'.$this->getMainHeaderRow($col_title1, $col_title2, $col_title3, $col_title4, $col_title5);
	}
	
	function getMainPageHeader($col_title1, $col_title2, $col_title3, $col_title4)
	{		
		return '<table class="mainpage" cellspacing="0">'.$this->getMainHeaderRow($col_title1, $col_title2, $col_title3, $col_title4, false);
	}
	
	function getMainHeaderRow($col_title1, $col_title2, $col_title3, $col_title4, $col_title5)
	{		
		$output = '<tr class="title">
					<th class="title">'.$col_title1.'</th>';
		if($col_title5) $output .= '<th class="admin"><p class="valuetitle">'.$col_title5.'</p></th>';
		$output .= '
					<th class="value"><p class="valuetitle">'.$col_title2.'</p></th>
					<th class="value"><p class="valuetitle">'.$col_title3.'</p></th>
					<th class="lastpost"><p class="valuetitle">'.$col_title4.'</p></th></tr>';
		return $output;
	}
	
	function getMainBody($col_value1, $col_value2, $col_value3, $col_value4, $col_title5, $marked)
	{		
		$output = '<tr class="';
		if($marked) $output .= $marked;
			else  $output .= "normal";
		$output .= '"><td class="title">'.$col_value1.'</td>';
		if($col_title5) $output .= '<td class="admin">'.$col_title5.'</td>';
		$output .= '
					<td class="value">'.$col_value2.'</td>
					<td class="value">'.$col_value3.'</td>
					<td class="value">'.$col_value4.'</td></tr>';
		return $output;
	}
	
	function getMainFooter()
	{		
		return '</table>'.$this->getFrameFooter();
	}
	
	function getMainPageFooter()
	{		
		return '</table>';
	}
	
	function getThreadHeader($title, $text, $posted, $buttons, $id)
	{		
		return $this->getFrameHeader().'
				<table style="width:100%">
				 <tr>
				  <th class="thread_top"><nowiki>'.$title.'</nowiki></th>
				  <th class="thread_top" style="text-align: right;"><nowiki>[#'.$id.']</nowiki></th>
				 </tr>
				 <tr>
				  <td class="thread_main" colspan="2">'.$text.$this->getBottomLine($posted, $buttons).'
				  </td>
				 </tr>';
	}
	
	function getCommentHeader($title)
	{		
		return $this->getFrameHeader().'
				<table style="width:100%">
				 <tr>
				  <th class="thread_top" colspan="2"><nowiki>'.$title.'</nowiki></th>
				 </tr>
				 ';
	}
	
	function getThreadFooter()
	{		
		return '</table>'.$this->getFrameFooter();
	}
	
	function getComment($comment, $posted, $buttons, $id)
	{		
		return 	'<tr><td class="thread_sub" colspan="2" id="comment_'.$id.'">'.$comment.$this->getBottomLine($posted, $buttons).'</td></tr>';
	}
	
	function getBottomLine($posted, $buttons)
	{
		global $wgUser;
		
		$output = '<table cellspacing="0" cellpadding="0" class="posted"><tr><td class="leftside">'.$posted.'</td>';
		
		if($wgUser->getID() > 0) $output .= '<td class="rightside">'.$buttons.'</td>';
		
		$output .= '</tr></table>';
		
		return $output;
	}
	
	function getSingleLine($message, $cols)
	{
		return '<tr class="sub"><td class="title" colspan="'.$cols.'">'.$message.'</td></tr>';
	}
	
	function getWriteForm($type, $action, $input, $height, $text_prev, $save_button)
	{
		global $wgUser, $wgScriptPath;
		global $wikiconf, $smilies;
		
		$output = "";
		
		if($wikiconf['anonymous_allowed'] == true || $wgUser->getID()>0)
		{
			$output = '<script src="'.$wgScriptPath.'/skins/common/edit.js"></script>
			<form name="frmMain" method="post" action="?'.$action.'" id="writecomment">
			<table class="frame" cellspacing="10">'.$input.'';
			if($wikiconf["disable_buttons"] != true)
			{
				$output .= '<tr>
				  <td colspan="2"><div id="toolbar">
				  <script> 
					addButton("'.$wgScriptPath.'/skins/common/images/button_bold.png","Bold text","\'\'\'","\'\'\'","Bold text","mw-editbutton-bold");
					addButton("'.$wgScriptPath.'/skins/common/images/button_italic.png","Italic text","\'\'","\'\'","Italic text","mw-editbutton-italic");
					addButton("'.$wgScriptPath.'/skins/common/images/button_link.png","Internal link","[[","]]","Link title","mw-editbutton-link");
					addButton("'.$wgScriptPath.'/skins/common/images/button_extlink.png","External link (remember http:// prefix)","[","]","http://www.example.com link title","mw-editbutton-extlink");
					addButton("'.$wgScriptPath.'/skins/common/images/button_headline.png","Level 2 headline","\n== "," ==\n","Headline text","mw-editbutton-headline");
					addButton("'.$wgScriptPath.'/skins/common/images/button_image.png","Embedded file","[[File:","]]","Example.jpg","mw-editbutton-image");
					addButton("'.$wgScriptPath.'/skins/common/images/button_media.png","File link","[[Media:","]]","Example.ogg","mw-editbutton-media");
					addButton("'.$wgScriptPath.'/skins/common/images/button_math.png","Mathematical formula (LaTeX)","\x3cmath\x3e","\x3c/math\x3e","Insert formula here","mw-editbutton-math");
					addButton("'.$wgScriptPath.'/skins/common/images/button_nowiki.png","Ignore wiki formatting","\x3cnowiki\x3e","\x3c/nowiki\x3e","Insert non-formatted text here","mw-editbutton-nowiki");
					addButton("'.$wgScriptPath.'/skins/common/images/button_sig.png","Your signature with timestamp","--~~~~","","","mw-editbutton-signature");
					addButton("'.$wgScriptPath.'/skins/common/images/button_hr.png","Horizontal line (use sparingly)","\n----\n","","","mw-editbutton-hr");
					</script>
					</div>
				  </td>
				 </tr>';
			}
			$output .= '<tr>
			  <td width="100%" colspan="2" valign="top"><textarea id="frmText" name="frmText" style="height:'.$height.';">'.$text_prev.'</textarea></td>
			  </tr>
			 <tr>
			  <td>
				<input name="butSave" type="submit" value="'.$save_button.'" accesskey="s" title="'.$save_button.' [s]" />
				<input name="butPreview" type="submit" value="'.wfMsg('wikiforum-preview').'" accesskey="p" title="'.wfMsg('wikiforum-preview').' [p]" />';
			if($type == "addthread") $output .= ' <input name="butCancel" type="button" value="'.wfMsg('wikiforum-cancel').'" accesskey="c" title="'.wfMsg('wikiforum-cancel').' [c]" onclick="javascript:history.back();" />';
			$output .= '</td><td id="form_linkslist" rowspan="2">
			 ';
			 if($wikiconf["disable_iconlist"] != true && is_array($smilies) && sizeof($smilies) > 0)
			 {
			 	$output .= '<a href="#writecomment" onclick="document.getElementById(\'form_iconlist\').style.display = \'block\'">show list of smilies/icons</a>';
				$iconlist = '<div id="form_iconlist" style="display: none; float:left;">'.$this->showIcons().'</div>';
			 }
			 $output .= '</td></tr>
			</table>
			</form>
			'.$iconlist;
		}
		return $output;
	}
	
	function getFormCatForum($type, $category_name, $action, $title_prev, $text_prev, $save_button, $parameter_object)
	{
		global $gc_helper;
		
		if($gc_helper->isAdminView())
		{
			$title_prev = str_replace('"', '&quot;', $title_prev);
			$output = '
			<form name="frmMain" method="post" action="?'.$action.'" id="form">
			<table class="frame" cellspacing="10"><tr><th class="title">'.$category_name.'</th></tr>
			<tr><td><p>'.wfMsg('wikiforum-name').':</p><input type="text" name="frmTitle" style="width:100%" value="'.$title_prev.'" /></td></tr>';
			if($type == "addforum" || $type == "editforum")
			{
			 $output .= '<tr>
						  <td><p>'.wfMsg('wikiforum-description').':</p><textarea name="frmText" style="height:40px;">'.$text_prev.'</textarea></td>
						 </tr><tr>
						  <td><p><input type="checkbox" name="chkAnnouncement" ';
			 if($parameter_object->Announcement == true) $output .= 'CHECKED'; 
			 $output .= '/> '.wfMsg('wikiforum-announcement').'</p></td>
						 </tr>';
			}
			else
			{
				$dbr				= new UniDatabase(DB_SLAVE);
				$list_permissions 	= $gc_helper->getAllPermissions();
				
				$output .= '<tr><td><br/><p>'.wfMsg('wikiforum-grouppermission').':</p></td></tr>';
				while ($permission = $dbr->fetchObject($parameter_object))
				{	
					$output .= '<tr><td><input type="checkbox" name="chkPermissions['.$permission->pkCatAccess.']" CHECKED>'.$permission->GroupeRight.'</option></td></tr>';
				}
				
				if(is_array($list_permissions)) 
				{
					$output .= '<tr><td><select name="selPermissions">';
					$output .= '<option value="---">---</option>';
					foreach($list_permissions as $permission) $output .= '<option value="'.$permission.'">'.$permission.'</option>';
					$output .= '</select></td></tr>';
				}
			}
			$output .= '
			 <tr>
			  <td>
				<input name="butSubmit" type="submit" value="'.$save_button.'" accesskey="s" title="'.$save_button.' [s]" />
				<input name="butCancel" type="button" value="'.wfMsg('wikiforum-cancel').'" accesskey="c" title="'.wfMsg('wikiforum-cancel').' [c]" onclick="javascript:history.back();" />
			  </td>
			 </tr>
			</table>
			</form>
			';
			return $output;
		} else return '';
	}
	
	function showIcons()
	{
		global $smilies, $wikiconf;
		
		if($wikiconf['smilie_list_style'] == "simple")
		{
			$output  = '<table cellspacing="0" class="frame" style="font-size: smaller;">';
			if(is_array($smilies))
			{
				$i = 0;
				foreach($smilies as $key => $icon)
				{
					if($i % 6 == 0) $output .= '<tr>';
					if($i % 2 == 0) $color =  'style="background-color: #dfdfdf;"';
						else $color = '';
					$output .= '<td '.$color.'>'.$key.'</td><td '.$color.'><img src="'.$wikiconf["extension_folder"].$icon.'" title="'.$key.'"/></td>';
					if($i % 6 == 5) $output .= '</tr>';
					$i++;
				}
				if($i % 6 != 0) $output .= '</tr>';
			}
			$output .= '</table>';
		}
		else
		{
			if(is_array($smilies))
			{
				foreach($smilies as $key => $icon)
				{
					//$output .= '<img src="'.$wikiconf["extension_folder"].$icon.'" title="'.$key.'" onclick="document.getElementById(\'frmText\').focus();document.selection.createRange().text = \''.$key.'\';" style="Cursor:Hand;"/> ';
					$output .= '<img src="'.$wikiconf["extension_folder"].$icon.'" title="'.$key.'" onclick="document.getElementById(\'frmText\').value += \''.$key.'\';" style="Cursor:Hand;"/> ';
				}
			}
		}
		return $output;
	}
	
	function getStatsPage($total, $average, $top)
	{
		$dbr	= new UniDatabase(DB_SLAVE);
		
		$output .= $this->getSearchbox();
		$output .= $this->getHeaderRow(1, "", 0, "", "");
		$output .= $this->getFrameHeader();
	
		$output .= '
		<table id="mainstats"><tr><td>
		<table class="stats">
		 <tr><th colspan="2">'.wfMsg('wikiforum-totalstatistics').'</th></tr>
		 <tr><td class="title">'.wfMsg('wikiforum-totalcategories').'</td><td>'.$total['categories'].'</td></tr>
		 <tr><td class="title">'.wfMsg('wikiforum-totalforums').'</td><td>'.$total['forums'].'</td></tr>
		 <tr><td class="title">'.wfMsg('wikiforum-totalthreads').'</td><td>'.$total['threads'].'</td></tr>
		 <tr><td class="title">'.wfMsg('wikiforum-totalposts').'</td><td>'.$total['posts'].'</td></tr>
		 <tr><td class="title">'.wfMsg('wikiforum-totalviews').'</td><td>'.$total['views'].'</td></tr>
		</table>
		</td><td>
		<table class="stats">
		 <tr><th colspan="2">'.wfMsg('wikiforum-averagestatistics').'</th></tr>
		 <tr><td class="title">'.wfMsg('wikiforum-averagethreadsperday').'</td><td>'.$average['threads'].'</td></tr>
		 <tr><td class="title">'.wfMsg('wikiforum-averagepostsperday').'</td><td>'.$average['posts'].'</td></tr>
		 <tr><td class="title">'.wfMsg('wikiforum-averageviewsperday').'</td><td>'.$average['views'].'</td></tr>
		</table>
		</td></tr><tr><td>
		<table class="stats">
		 <tr><th colspan="2">'.wfMsg('wikiforum-threadsbyreply').'</th></tr>';
		 while ($issue = $dbr->fetchObject($top['threads_by_replies']))
		 {
			$output .= '<tr><td class="title">'.$issue->Thread_name.'</td><td>'.$issue->num_answers.'</td></tr>';
		 }
		$output .= '</table>
		</td><td>
		<table class="stats">
		 <tr><th colspan="2">'.wfMsg('wikiforum-threadsbycalls').'</th></tr>';
		 while ($issue = $dbr->fetchObject($top['threads_by_views']))
		 {
			$output .= '<tr><td class="title">'.$issue->Thread_name.'</td><td>'.$issue->num_calls.'</td></tr>';
		 }
		$output .= '</table>
		</td></tr><tr><td>
		<table class="stats">
		 <tr><th colspan="2">'.wfMsg('wikiforum-usersbythreads').'</th></tr>';
		 while ($issue = $dbr->fetchObject($top['users_by_threads']))
		 {
			$output .= '<tr><td class="title">'.$issue->user_name.'</td><td>'.$issue->num.'</td></tr>';
		 }
		$output .= '</table>
		</td><td>
		<table class="stats">
		 <tr><th colspan="2">'.wfMsg('wikiforum-usersbytreplies').'</th></tr>';
		 while ($issue = $dbr->fetchObject($top['users_by_replies']))
		 {
			$output .= '<tr><td class="title">'.$issue->user_name.'</td><td>'.$issue->num.'</td></tr>';
		 }
		$output .= '</table>
		</td></tr></table>';
		$output .= $this->getFrameFooter();
		return $output;
	}
	
	function getInput($title_prev)
	{
		$title_prev = str_replace('"', '&quot;', $title_prev);
		return '<tr><td colspan="2"><input type="text" name="frmTitle" style="width:100%" value="'.$title_prev.'" /></td></tr>';
	}
	
	function getInfoLine()
	{
		global $wikiconf;
		global $gs_version;
		
		if($wikiconf['disableinfoline'] != true)
		{
			$output = '<div id="infoline">';
			$output .= '<p><a href="http://www.mediawiki.org/wiki/Extension:WikiForum" target="_blank">WikiForum v'.$gs_version.'</a> is powered by <a href="http://www.unidentify.com" target="_blank">Unidentify Studios</a></p>';
			if($wikiconf['showlicense'] == true) $output .= '<p>This work is licensed under <a rel="license" href="http://www.gnu.org/licenses/gpl.html" target="_blank">GPL v3</a>.</p>';
			if(strlen($wikiconf['additional_info'])>0) $output .= '<p>'.$wikiconf['additional_info'].'</p>';
			$output .= '</div>';
		}
		return $output;
	}
	
	function getStatsLink()
	{
		global $wikiconf;
		global $gc_helper;
		
		if($wikiconf['icon_statistics']) $icon = ' <img src="'.$wikiconf['extension_folder'].$wikiconf['icon_statistics'].'" title="'.wfMsg('wikiforum-statistics').'" /> ';
			else $icon = " ";
		return $icon.'<a href="?page=stats'.$gc_helper->getActionString("view").'">'.wfMsg('wikiforum-statistics').'</a>';
	}
	
}
?>
