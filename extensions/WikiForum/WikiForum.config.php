<?php
			// superuserpassword, safety check to deleted entry completly
			$wikiconf["superuserpassword"] 	= "fbdef9dd6951be0b84ecd37b7807c57e";
			
			//Version key of WikiForum 1.2
			$wikiconf["installed"] = "3e3daa044205bb26a0925b9da4b67abf";
			$wikiconf["install_date"] = "1342318516";

			// if true then a link to WikiForum will also be available in Toolbox
			$wikiconf["link_in_toolbox"] 	= false;

			// if true then tags <WikiForumList /> and <WikiForumThread /> are available.
			// Definition of <WikiForumList num=value /> 
			// Displays the last modified threads of the complete WikiForum
			// 		num (optional): value defines how many threads shall be shown. If not set: value=5
			// Definition of <WikiForumThread id=value nocomments />
			// Displays the thread which is given in id.
			// 		id (required): value defines the id of the thread which shall be shown.
			// 		nocomments (optional): if set then just the thread text will be shown not the comments.
			$wikiconf["wikiforum_tag"] 		= true;

			// allow anonymous users to write threads and comments
			$wikiconf["anonymous_allowed"] 	= false;

			// number of threads which shall be shown per page of a forum.
			$wikiconf["max_threads_per_page"] 	= 20;

			// number of comments which shall be shown per page of a thread.
			$wikiconf["max_comments_per_page"] 	= 10;

			// number of days for definition of a thread as new.
			$wikiconf["daydefinition_new"] 	= 3;

			$wikiconf["showlicense"]			= false;
			
			//additional configuration parameter, if exists ...
					$wikiconf["forumname"] = "OpenROV Forums";
			
			// defined icons (standard names of FAMFAMFAM sticky icon set)
			// use here the names of the icons you want to use $wikiconf["icon_forum"] = "icons/folder.png";
					$wikiconf["icon_forum_add"] = "icons/folder_add.png";
					$wikiconf["icon_forum_edit"] = "icons/folder_edit.png";
					$wikiconf["icon_forum_delete"] = "icons/folder_delete.png";
					$wikiconf["icon_category_add"] = "icons/database_add.png";
					$wikiconf["icon_category_edit"] = "icons/database_edit.png";
					$wikiconf["icon_category_delete"] = "icons/database_delete.png";
					$wikiconf["icon_thread"] = "icons/note.png";
					$wikiconf["icon_thread_new"] = "icons/new.png";
					$wikiconf["icon_thread_closed"] = "icons/lock.png";
					$wikiconf["icon_thread_add"] = "icons/note_add.png";
					$wikiconf["icon_thread_close"] = "icons/lock_add.png";
					$wikiconf["icon_thread_reopen"] = "icons/lock_open.png";
					$wikiconf["icon_thread_edit"] = "icons/note_edit.png";
					$wikiconf["icon_thread_delete"] = "icons/note_delete.png";
					$wikiconf["icon_comment_add"] = "icons/comment_add.png";
					$wikiconf["icon_comment_edit"] = "icons/comment_edit.png";
					$wikiconf["icon_comment_delete"] = "icons/comment_delete.png";
					$wikiconf["icon_sort_up"] = "icons/bullet_arrow_up.png";
					$wikiconf["icon_sort_down"] = "icons/bullet_arrow_down.png";
					$wikiconf["icon_failure"] = "icons/exclamation.png";
					$wikiconf["icon_admin_view"] = "icons/tux.png";
					$wikiconf["icon_normal_view"] = "icons/application_xp.png";
					$wikiconf["icon_sticky"] = "icons/tag_blue.png";
					$wikiconf["icon_sticky_add"] = "icons/tag_blue_add.png";
					$wikiconf["icon_sticky_delete"] = "icons/tag_blue_delete.png";
					$wikiconf["icon_sortkey_up"] = "icons/arrow_up.png";
					$wikiconf["icon_sortkey_down"] = "icons/arrow_down.png";
					$wikiconf["icon_search"] = "icons/zoom.png";
					$wikiconf["icon_move_thread"] = "icons/note_go.png";
					$wikiconf["icon_paste_thread"] = "icons/paste_plain.png";
					$wikiconf["icon_quote"] = "icons/comments_add.png";
					$wikiconf["icon_statistics"] = "icons/chart_pie.png";
					
			
			//example for definition of smilies
			// use instead of ">" the "&gt;" and instead of "<" the "&lt;"
			/*
			$smilies[":)"]						= "icons/emoticon_grin.png";
			$smilies["&gt;D"]					= "icons/emoticon_evilgrin.png";
			//*/
?>