<?php
class WikiForumInstall 
{ 
	private $m_key = array(	"1.0" => "4abd6655683301dfcb19c615bb19e84f",
							"1.1" => "0fc20479f0a2bcc0536cea58efe0b1f8",
							"1.2" => "369fa889bcdd17838419328a8f78679d",
							"1.3" => "3e3daa044205bb26a0925b9da4b67abf");
	function install()
	{
		global $wgOut;
		global $gs_version;
		
		if(file_exists(dirname(__FILE__) . "/WikiForum.config.php.new")) //deprecated
		{
			$wgOut->AddHtml($wgOut->parse("=== Installation of WikiForum $gs_version ==="));
			$wgOut->AddHtml($wgOut->parse("'''Installation completed'''. Please delete file ''WikiForum.config.php'' and rename afterwards ''WikiForum.config.php.new'' to ''WikiForum.config.php''.
								Make sure to set ''WikiForum.config.php'' to read-only. If you want to reconfigure delete the value of ''\$wikiconf[\"installed\"]'' and you will be able to reinstall the script. "));
		}
		else
		{
			$wgOut->AddHtml($wgOut->parse("=== Installation of WikiForum $gs_version ==="));
			$wgOut->AddHtml($wgOut->parse("===== SQL table setup ====="));
			$result = $this->checkSqlTable("wikiforum_cataccess");
			$result = $this->checkSqlTable("wikiforum_category");
			$result = $this->checkSqlTable("wikiforum_forums");
			$result = $this->checkSqlTable("wikiforum_threads");
			$result = $this->checkSqlTable("wikiforum_comments");
			if($result == true)
			{
				$wgOut->AddHtml($wgOut->parse("===== Configuration setup ====="));
				$result = $this->checkConfig();
			}
		}
	}
	
	function parseLine($text, $result)
	{
		global $wgOut;
		if($result === true) 		$ok = "OK";
		else if($result === false) 	$ok = "'''NOT OK'''";
		else						$ok = $result;
		$wgOut->AddHtml($wgOut->parse("* $text :: $ok"));
	}
	
	function checkSqlTable($table)
	{
		global $wikiconf;
		$dbr	= new UniDatabase(DB_SLAVE);
		$msg	= "checking table ''$table''";
		
		$resTable = $dbr->fetchObject($dbr->query("SHOW tables LIKE '$table'"));		
		if($resTable)
		{
			//check for Version change 1.0 > 1.1+ in wikiforum_category and wikiforum_forums
			if($table == "wikiforum_category" || $table == "wikiforum_forums")
			{
				$resColumn = $dbr->fetchObject($dbr->query("SHOW columns FROM $table LIKE 'Deleted'"));
				if($resColumn)
				{
					//check for Version change 1.1 > 1.2+ in wikiforum_forums
					if($table == "wikiforum_forums") $resColumn = $dbr->fetchObject($dbr->query("SHOW columns FROM $table LIKE 'Announcement'"));
						else $resColumn = true;
					if($resColumn)
					{
						if($wikiconf["installed"] != $this->m_key["1.3"])
						{
							$this->parseLine($msg, "not the correct version");
							return $this->updateSqlTable($table, $this->m_key["1.2"]);
						}
						else 
						{
							$this->parseLine($msg, true);
							return true;
						}
					}
					else 
					{
						$this->parseLine($msg, "not the correct version of table");
						return $this->updateSqlTable($table, $this->m_key["1.1"]);
					}
				}
				else 
				{
					$this->parseLine($msg, "not the correct version of table");
					return $this->updateSqlTable($table, $this->m_key["1.0"]);
				}
			}
			else
			{
				if($wikiconf["installed"] != $this->m_key["1.3"])
				{
					$this->parseLine($msg, "not the correct version");
					return $this->updateSqlTable($table, $this->m_key["1.2"]);
				}
				else 
				{
					$this->parseLine($msg, true);
					return true;
				}
			}
		}
		else 
		{
			$this->parseLine($msg, "table does not exists");
			return $this->createSqlTable($table);
		}
	}
	
	function updateSqlTable($table, $version)
	{
		$dbw = new UniDatabase(DB_MASTER);
		
		if($version == $this->m_key["1.1"] && $table == "wikiforum_forums")
		{
			$updateSql = "ALTER TABLE `$table`
					ADD `Announcement` TINYINT( 1 ) NOT NULL DEFAULT '0';";	
			$result = $dbw->query($updateSql);
		}
		else if($version == $this->m_key["1.0"])
		{
			if($table == "wikiforum_forums") $announce = ", ADD `Announcement` TINYINT( 1 ) NOT NULL DEFAULT '0'";
				else $announce = "";
			$updateSql = "ALTER TABLE `$table` 	
					ADD `Added` INT( 10 ) NOT NULL DEFAULT '0',
					ADD `AddedBy` INT( 10 ) NOT NULL DEFAULT '0',
					ADD `Edited` INT( 10 ) NOT NULL DEFAULT '0',
					ADD `EditedBy` INT( 10 ) NOT NULL DEFAULT '0',
					ADD `Deleted` INT( 10 ) NOT NULL DEFAULT '0',
					ADD `DeletedBy` INT( 10 ) NOT NULL DEFAULT '0'
					$announce;";
			$result = $dbw->query($updateSql);
		}
		if($version != $this->m_key["1.3"])
		{
			if($table == "wikiforum_forums")
			{
				$updateSql = "ALTER TABLE `$table` CHANGE `Forum_name` `Forum_name` VARBINARY( 128 ) NOT NULL";	
				$result = $dbw->query($updateSql);
				$updateSql = "ALTER TABLE `$table` CHANGE `Description` `Description` TINYBLOB NOT NULL";	
				$result = $dbw->query($updateSql);
			}
			else if($table == "wikiforum_category")
			{
				$updateSql = "ALTER TABLE `$table` CHANGE `Category_name` `Category_name` VARBINARY( 128 ) NOT NULL";	
				$result = $dbw->query($updateSql);
			}
			else if($table == "wikiforum_threads")
			{
				$updateSql = "ALTER TABLE `$table` CHANGE `Thread_name` `Thread_name` TINYBLOB NOT NULL";	
				$result = $dbw->query($updateSql);
				$updateSql = "ALTER TABLE `$table` CHANGE `Text` `Text` BLOB NOT NULL";	
				$result = $dbw->query($updateSql);
			}
			else if($table == "wikiforum_comments")
			{
				$updateSql = "ALTER TABLE `$table` CHANGE `Comment` `Comment` BLOB NOT NULL";	
				$result = $dbw->query($updateSql);
			}
			else 
			{
				$this->parseLine("no need to update table ''$table''", true);
				return true;
			}
		}
		
		$this->parseLine("updating table ''$table''", $result);
		return $result;	
	}
	
	function createSqlTable($table)
	{
		$dbw = new UniDatabase(DB_MASTER);
		
		$insertSql['wikiforum_cataccess'] = 
			"CREATE TABLE IF NOT EXISTS `wikiforum_cataccess` (
			  `pkCatAccess` int(10) NOT NULL AUTO_INCREMENT,
			  `fkCategory` int(10) NOT NULL,
			  `GroupeRight` varbinary(128) NOT NULL,
			  PRIMARY KEY (`pkCatAccess`)
			) ENGINE=MyISAM  DEFAULT CHARSET=binary;";
		
		$insertSql['wikiforum_category'] = 
			"CREATE TABLE IF NOT EXISTS `wikiforum_category` (
			  `pkCategory` int(10) NOT NULL AUTO_INCREMENT,
			  `Category_name` varbinary(64) NOT NULL,
			  `SortKey` mediumint(5) NOT NULL DEFAULT '9',
			  `Added` int(10) NOT NULL DEFAULT '0',
			  `AddedBy` int(10) NOT NULL DEFAULT '0',
			  `Edited` int(10) NOT NULL DEFAULT '0',
			  `EditedBy` int(10) NOT NULL DEFAULT '0',
			  `Deleted` int(10) NOT NULL DEFAULT '0',
			  `DeletedBy` int(10) NOT NULL DEFAULT '0',
			  PRIMARY KEY (`pkCategory`)
			) ENGINE=MyISAM  DEFAULT CHARSET=binary;";
			
		$insertSql['wikiforum_forums'] = 
			"CREATE TABLE IF NOT EXISTS `wikiforum_forums` (
			  `pkForum` int(10) NOT NULL AUTO_INCREMENT,
			  `Forum_name` varbinary(64) NOT NULL,
			  `Description` tinyblob NOT NULL,
			  `fkCategory` int(10) NOT NULL,
			  `SortKey` mediumint(5) NOT NULL DEFAULT '9',
			  `num_threads` int(10) NOT NULL DEFAULT '0',
			  `num_articles` int(10) NOT NULL DEFAULT '0',
			  `lastpost_user` int(10) NOT NULL DEFAULT '0',
			  `lastpost_time` int(10) NOT NULL DEFAULT '0',
			  `Added` int(10) NOT NULL DEFAULT '0',
			  `AddedBy` int(10) NOT NULL DEFAULT '0',
			  `Edited` int(10) NOT NULL DEFAULT '0',
			  `EditedBy` int(10) NOT NULL DEFAULT '0',
			  `Deleted` int(10) NOT NULL DEFAULT '0',
			  `DeletedBy` int(10) NOT NULL DEFAULT '0',
			  `Announcement` TINYINT( 1 ) NOT NULL DEFAULT '0',
			  PRIMARY KEY (`pkForum`)
			) ENGINE=MyISAM  DEFAULT CHARSET=binary;";
			
		$insertSql['wikiforum_threads'] = 
			"CREATE TABLE IF NOT EXISTS `wikiforum_threads` (
			  `pkThread` int(10) NOT NULL AUTO_INCREMENT,
			  `Thread_name` tinyblob NOT NULL,
			  `Text` blob NOT NULL,
			  `Sticky` tinyint(1) NOT NULL DEFAULT '0',
			  `Posted` int(10) NOT NULL DEFAULT '0',
			  `PostedBy` int(10) NOT NULL DEFAULT '0',
			  `Deleted` int(10) NOT NULL DEFAULT '0',
			  `DeletedBy` int(10) NOT NULL DEFAULT '0',
			  `Edit` int(10) NOT NULL DEFAULT '0',
			  `EditBy` int(10) NOT NULL DEFAULT '0',
			  `Closed` int(10) NOT NULL DEFAULT '0',
			  `ClosedBy` int(10) NOT NULL DEFAULT '0',
			  `fkForum` int(10) NOT NULL,
			  `num_answers` int(10) NOT NULL DEFAULT '0',
			  `num_calls` int(10) NOT NULL DEFAULT '0',
			  `lastpost_user` int(10) NOT NULL DEFAULT '0',
			  `lastpost_time` int(10) NOT NULL DEFAULT '0',
			  PRIMARY KEY (`pkThread`)
			) ENGINE=MyISAM  DEFAULT CHARSET=binary;";
			
		$insertSql['wikiforum_comments'] = 
			"CREATE TABLE IF NOT EXISTS `wikiforum_comments` (
			  `pkComment` int(10) NOT NULL AUTO_INCREMENT,
			  `Comment` blob NOT NULL,
			  `Posted` int(10) NOT NULL DEFAULT '0',
			  `PostedBy` int(10) NOT NULL DEFAULT '0',
			  `Deleted` int(10) NOT NULL DEFAULT '0',
			  `DeletedBy` int(10) NOT NULL DEFAULT '0',
			  `Edit` int(10) NOT NULL DEFAULT '0',
			  `EditBy` int(10) NOT NULL DEFAULT '0',
			  `fkThread` int(10) NOT NULL,
			  PRIMARY KEY (`pkComment`)
			) ENGINE=MyISAM  DEFAULT CHARSET=binary;";
			
		$result = $dbw->query($insertSql[$table]);
		$this->parseLine("creating table ''$table''", $result);
		return $result;
	}
	
	function checkConfig()
	{
		global $wikiconf;
		global $wgOut, $wgRequest;
		
		if($wgRequest->getBool('frmSubmit'))
		{
			$content = $this->dataConfigFile();
			if($content !== false) $this->createConfigFile($content);
		}
		if(!$wgRequest->getBool('frmSubmit') || $content === false)
		{
			$wgOut->AddHtml($this->getNewForm());
		}
	}
	
	function getNewForm()
	{
		global $wikiconf;
		
		if(!isset($wikiconf['link_in_toolbox']) || $wikiconf['link_in_toolbox'] === true)	$lit = "checked";
		if(!isset($wikiconf['wikiforum_tag']) || $wikiconf['wikiforum_tag']) 				$tags = "checked";
		if($wikiconf['anonymous_allowed']) 													$anonym = "checked";
		if($wikiconf['showlicense']) 														$license = "checked";
		if(!isset($wikiconf['max_threads_per_page'])) 										$maxthreads = "20";
			else																			$maxthreads = $wikiconf['max_threads_per_page'];
		if(!isset($wikiconf['max_comments_per_page'])) 										$maxcomments = "10";
			else																			$maxcomments = $wikiconf['max_comments_per_page'];
		if(!isset($wikiconf['daydefinition_new'])) 											$daysnew = "3";
			else																			$daysnew = $wikiconf['daydefinition_new'];
		if(isset($wikiconf['superuserpassword']))											$password = "********";
		
		$output = '<form name="frmForm" method="post"><table>
		 <tr><td>Superuser password</td><td><input type="password" name="pw1" value="'.$password.'"/></td></tr>
		 <tr><td>Retype password</td><td><input type="password" name="pw2" value="'.$password.'"/></td></tr>
		 <tr><td>Link in Toolbox</td><td><input type="checkbox" name="lit" '.$lit.'/></td></tr>
		 <tr><td>Activate tags</td><td><input type="checkbox" name="tags" '.$tags.'/></td></tr>
		 <tr><td>Anonymous allowed to post</td><td><input type="checkbox" name="anonym" '.$anonym.'/></td></tr>
		 <tr><td>Max threads per page</td><td><input type="input" name="maxthreads" value="'.$maxthreads.'"/></td></tr>
		 <tr><td>Max pages per thread</td><td><input type="input" name="maxcomments" value="'.$maxcomments.'"/></td></tr>
		 <tr><td>Days a thread is marked as new</td><td><input type="input" name="new" value="'.$daysnew.'"/></td></tr>
		 <tr><td>Show license</td><td><input type="checkbox" name="license" '.$license.'/></td></tr>
		 <tr><td></td><td><input type="submit" name="frmSubmit" value="'.wfMsg('wikiforum-save').'"/></td></tr>
		</table></form>';
		
		return $output;
	}
	
	function createConfigFile($content)
	{
		global $wikiconf;
		
		if(file_exists(dirname(__FILE__) . "/"."WikiForum.config.php")) rename(dirname(__FILE__) . "/"."WikiForum.config.php", dirname(__FILE__) . "/"."WikiForum.config.backup_".time().".php");
	    if(!$handle = fopen(dirname(__FILE__) . "/WikiForum.config.php", "w")) $result = false;
		else 
		{
			if (!fwrite($handle, $content)) $result = false;
			else $result = true;
			fclose($handle);
		}
		$this->parseLine("Create config file WikiForum.config.php", $result);
	}
	
	
	function dataConfigFile()
	{
		global $wgRequest;
		global $wikiconf, $smilies;
		
		$dbr = new UniDatabase(DB_SLAVE);
		
		if($wgRequest->getVal('pw1') == $wgRequest->getVal('pw2') && $wgRequest->getVal('pw1') != "********" && strlen($wgRequest->getVal('pw1'))>4)
		{
				$password = md5($wgRequest->getVal('pw1'));
				$this->parseLine("Setting password", true);
		}
		else if(isset($wikiconf['superuserpassword']))
		{
			$password = $wikiconf['superuserpassword'];
			$this->parseLine("Using old password", true);
		}
		else	
		{
			$this->parseLine("Setting password", false);
			return false;
		}
		
		if(file_exists(dirname(__FILE__) . "/icons/folder.png"))
		{
			$this->parseLine("Icons found", true);
		}
		else
		{	
			$this->parseLine("Icons found (Recommended icons: http://www.famfamfam.com/lab/icons/silk/)", false);
			$comment_icon = "/*";
		}
		
		if(!isset($wikiconf['install_date']))
		{
			$temp = $dbr->fetchObject($dbr->query('SELECT Added FROM wikiforum_category ORDER BY Added ASC LIMIT 0, 1'));
			if($temp != false) 	$install_date = $temp->Added;
				else 			$install_date = time();
		}
		else $install_date = $wikiconf['install_date'];
		
	    $output = '<?php
			// superuserpassword, safety check to deleted entry completly
			$wikiconf["superuserpassword"] 	= "'.$password.'";
			
			//Version key of WikiForum 1.2
			$wikiconf["installed"] = "'.$this->m_key["1.3"].'";
			$wikiconf["install_date"] = "'.$install_date.'";

			// if true then a link to WikiForum will also be available in Toolbox
			$wikiconf["link_in_toolbox"] 	= '.$this->getBool($wgRequest->getBool('lit')).';

			// if true then tags <WikiForumList /> and <WikiForumThread /> are available.
			// Definition of <WikiForumList num=value /> 
			// Displays the last modified threads of the complete WikiForum
			// 		num (optional): value defines how many threads shall be shown. If not set: value=5
			// Definition of <WikiForumThread id=value nocomments />
			// Displays the thread which is given in id.
			// 		id (required): value defines the id of the thread which shall be shown.
			// 		nocomments (optional): if set then just the thread text will be shown not the comments.
			$wikiconf["wikiforum_tag"] 		= '.$this->getBool($wgRequest->getBool('tags')).';

			// allow anonymous users to write threads and comments
			$wikiconf["anonymous_allowed"] 	= '.$this->getBool($wgRequest->getBool('anonym')).';

			// number of threads which shall be shown per page of a forum.
			$wikiconf["max_threads_per_page"] 	= '.$wgRequest->getInt('maxthreads').';

			// number of comments which shall be shown per page of a thread.
			$wikiconf["max_comments_per_page"] 	= '.$wgRequest->getInt('maxcomments').';

			// number of days for definition of a thread as new.
			$wikiconf["daydefinition_new"] 	= '.$wgRequest->getInt('new').';

			$wikiconf["showlicense"]			= '.$this->getBool($wgRequest->getBool('license')).';
			';
			
			$specials_array = array("additional_info", "forumname", "disableinfoline");
			
			$output .= '
			//additional configuration parameter, if exists ...';
			foreach($specials_array as $special)
			{
				if(isset($wikiconf[$special])) 
				{
					$output .= '
					$wikiconf["'.$special.'"] = "'.$wikiconf[$special].'";';
				}
			}
			
			$output .= '
			
			// defined icons (standard names of FAMFAMFAM sticky icon set)
			// use here the names of the icons you want to use ';
			$icon_array = array("icon_forum" => "icons/folder.png", "icon_forum_add" => "icons/folder_add.png", "icon_forum_edit" => "icons/folder_edit.png", "icon_forum_delete" => "icons/folder_delete.png", 
							"icon_category_add" => "icons/database_add.png", "icon_category_edit" => "icons/database_edit.png", "icon_category_delete" => "icons/database_delete.png", "icon_thread" => "icons/note.png",
							"icon_thread_new" => "icons/new.png", "icon_thread_closed" => "icons/lock.png", "icon_thread_add" => "icons/note_add.png", "icon_thread_close" => "icons/lock_add.png", "icon_thread_reopen" =>
							"icons/lock_open.png", "icon_thread_edit" => "icons/note_edit.png", "icon_thread_delete" => "icons/note_delete.png", "icon_comment_add" => "icons/comment_add.png", "icon_comment_edit" =>
							"icons/comment_edit.png", "icon_comment_delete" => "icons/comment_delete.png", "icon_sort_up" => "icons/bullet_arrow_up.png", "icon_sort_down" => "icons/bullet_arrow_down.png", "icon_failure" =>
							"icons/exclamation.png", "icon_admin_view" => "icons/tux.png", "icon_normal_view" => "icons/application_xp.png", "icon_sticky" => "icons/tag_blue.png", "icon_sticky_add" => "icons/tag_blue_add.png",
							"icon_sticky_delete" => "icons/tag_blue_delete.png", "icon_sortkey_up" => "icons/arrow_up.png", "icon_sortkey_down" => "icons/arrow_down.png", "icon_search" => "icons/zoom.png", "icon_move_thread" =>
							"icons/note_go.png", "icon_paste_thread" => "icons/paste_plain.png", "icon_quote" => "icons/comments_add.png", "icon_statistics" => "icons/chart_pie.png");

			foreach($icon_array as $key => $icon)
			{
				if(isset($wikiconf[$key])) 	
				{
					if(!file_exists(dirname(__FILE__) . "/".$wikiconf[$key])) $output .= '//';
					$output .= '$wikiconf["'.$key.'"] = "'.$wikiconf[$key].'";
					';
				}
				else
				{
					if(!file_exists(dirname(__FILE__) . "/".$icon)) $output .= '//';
					$output .= '$wikiconf["'.$key.'"] = "'.$icon.'";
					';
				}
			}	
			
			$output .= '
			
			//example for definition of smilies
			// use instead of ">" the "&gt;" and instead of "<" the "&lt;"
			/*
			$smilies[":)"]						= "icons/emoticon_grin.png";
			$smilies["&gt;D"]					= "icons/emoticon_evilgrin.png";
			//*/';
			if(isset($smilies) && is_array($smilies))
			{
				foreach($smilies as $key => $icon)
				{
					$output .= '
					$smilies["'.$key.'"] = "'.$icon.'";';
				}
			}
			$output .= '
?>';
		return $output;
	}
	
	function getBool($value)
	{
		if($value === true) return "true";
			else return "false";
	}
}
?>