<?php
/**
 * Erudite skin
 * Based off The Erudite skin for Wordpress.
 *
 * @file
 * @ingroup Skins
 */

// initialize
if( !defined( 'MEDIAWIKI' ) ){
	die( "This is a skins file for mediawiki and should not be viewed directly.\n" );
}

require_once( dirname( dirname( __FILE__ ) ) . '/includes/SkinTemplate.php');
 
// inherit main code from SkinTemplate, set the CSS and template filter
class SkinErudite extends SkinTemplate {
	function initPage( OutputPage $out ) {
		parent::initPage( $out );
		$this->skinname  = 'Erudite';
		$this->stylename = 'Erudite';
		$this->template  = 'EruditeTemplate';
		$this->useHeadElement = false;
	}
	function setupSkinUserCss( OutputPage $out ) {
		global $wgHandheldStyle;
		parent::setupSkinUserCss( $out );
	}
	
	function getMTitle() {
		return $this->mTitle;
	}
}

class EruditeTemplate extends QuickTemplate {
	var $skin;
	function getCategories() {
		$catlinks=$this->getCategoryLinks();
		if(!empty($catlinks)) {
			return "<ul id='catlinks'>{$catlinks}</ul>";
		}
	}
 
	function getCategoryLinks() {
		global $wgOut, $wgUser, $wgTitle, $wgUseCategoryBrowser;
		global $wgContLang;
 
		if(count($wgOut->mCategoryLinks) == 0) return '';
 
		$skin = $wgUser->getSkin();
 
		# separator
		$sep = "";
 
		// use Unicode bidi embedding override characters,
		// to make sure links don't smash each other up in ugly ways
		$dir = $wgContLang->isRTL() ? 'rtl' : 'ltr';
		$embed = "<li dir='$dir'>";
		$pop = '</li>';
		$cats = $wgOut->mCategoryLinks['normal'];
		//$t = $embed . implode ( "{$pop} {$sep} {$embed}" , $wgOut->mCategoryLinks ) . $pop;
 
		$msg = wfMsgExt('pagecategories', array('parsemag', 'escape'), count($wgOut->mCategoryLinks));
		$s = $embed . $skin->makeLinkObj(Title::newFromText(wfMsgForContent('pagecategorieslink')), $msg) . $pop;
		while (list($key, $val) = each($cats)) {
			$s .= $embed . $val . $pop;
		}
 
		# optional 'dmoz-like' category browser - will be shown under the list
		# of categories an article belongs to
		if($wgUseCategoryBrowser) {
			$s .= '<br /><hr />';
	 
			# get a big array of the parents tree
			$parenttree = $wgTitle->getParentCategoryTree();
			# Skin object passed by reference because it can not be
			# accessed under the method subfunction drawCategoryBrowser
			$tempout = explode("\n", Skin::drawCategoryBrowser($parenttree, $this));
			# clean out bogus first entry and sort them
			unset($tempout[0]);
			asort($tempout);
			# output one per line
			$s .= implode("<br />\n", $tempout);
		}
 		return $s;
	}
	
	/**
	 * Template filter callback for this skin.
	 * Takes an associative array of data set from a SkinTemplate-based
	 * class, and a wrapper for MediaWiki's localization database, and
	 * outputs a formatted page.
	 */
	public function execute() {
		global $wgRequest, $wgSitename;
		
		$this->skin = $this->data['skin'];
		$mTitle = $this->skin->getMTitle();
 
		// suppress warnings to prevent notices about missing indexes in $this->data
		wfSuppressWarnings();

		$matches = Array();
		$c = preg_match('/\<p\>(.+?)\<\/p\>/s', $this->data['bodytext'], $matches);
		$description = trim(preg_replace('/\"/', '\'', strip_tags($matches[0])));
 
		//$this->html( 'headelement' );
		?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 

				<pre><?php //print_r($this->data); ?></pre>
				<pre><? //php print_r($this); ?></pre>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US" class="no-js"> 
<head profile="http://gmpg.org/xfn/11"> 
	<title><?php $this->html('title'); ?> &#8211; <?php echo($wgSitename); ?></title> 
	<meta property="og:title" value="<?php $this->html('title'); ?>">
	<meta property="og:site_name" content="<?php echo($wgSitename); ?>"/>
	<meta property="og:type" content="article"/>
	<meta property="og:description" content="<?php echo($description); ?>"/>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	
	<?php if ($this->data['content_actions']['edit']) { ?>
		<link rel="alternate" type="application/x-wiki" title="Edit" href="<?php echo($this->data['content_actions']['edit']['href']); ?>" />
	<?php } ?> 
	
	<link rel="shortcut icon" href="/favicon.ico" /> 
	<link rel="search" type="application/opensearchdescription+xml" href="<?php $this->text('scriptpath'); ?>/opensearch_desc.php" title="<?php echo($wgSitename); ?>" /> 
	<link rel="copyright" href="<?php $this->text('scriptpath'); ?>/index.php/Copyright_Notice" /> 
	<link rel="alternate" type="application/atom+xml" title="<?php echo($wgSitename); ?> Atom feed" href="<?php $this->text('scriptpath'); ?>/index.php?title=Special:RecentChanges&amp;feed=atom" />
	
	<link rel="stylesheet" href="<?php $this->text('stylepath' ) ?>/common/shared.css" media="screen" /> 
	<link rel="stylesheet" href="<?php $this->text('stylepath' ) ?>/common/commonPrint.css" media="print" /> 
	<link rel="stylesheet" type="text/css" href="<?php $this->text('stylepath' ) ?>/erudite/erudite.css" /> 
	<!--[if lte IE 6]> <link rel="stylesheet" href="<?php $this->text('stylepath' ) ?>/erudite/ie6.css" type="text/css"> 
	<style type="text/css" media="screen">
		.hr {behavior: url(<?php $this->text('stylepath' ) ?>/erudite/iepngfix.htc); }
	</style> <![endif]--> 
	<!--[if lte IE 7 ]> <link rel="stylesheet" href="<?php $this->text('stylepath' ) ?>/erudite/ie7.css" type="text/css"> <![endif]--> 
	<!--[if gte IE 8 ]> <link rel="stylesheet" href="<?php $this->text('stylepath' ) ?>/erudite/ie8.css" type="text/css"> <![endif]--> 
	<link rel="stylesheet" type="text/css" href="<?php $this->text('stylepath' ) ?>/erudite/wiki-style.css" /> 
	
	<script type='text/javascript' src='<?php $this->text('stylepath' ) ?>/erudite/jquery/jquery.js?<?php echo $GLOBALS['wgStyleVersion'] ?>'></script>
	<script type="text/javascript">
// <![CDATA[
		var erdt = {
			More: '<span></span> Keep Reading',
			Less: '<span></span> Put Away',
			Info: '&#x2193; Further Information',
			MenuShow: '<span></span> Show Menu',
			MenuHide: '<span></span> Hide Menu',
			DisableKeepReading: false,
			DisableHide: true 
		};
		(function(H){H.className=H.className.replace(/\bno-js\b/,'js')})(document.documentElement)
// ]]>
	</script>
	<?php print Skin::makeGlobalVariablesScript( $this->data ); ?>
	<script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('stylepath' ) ?>/common/wikibits.js?<?php echo $GLOBALS['wgStyleVersion'] ?>"><!-- wikibits js --></script>
	<script src="<?php $this->text('stylepath' ) ?>/common/edit.js?<?php echo $GLOBALS['wgStyleVersion'] ?>"></script> 
	<script src="<?php $this->text('stylepath' ) ?>/common/ajax.js?<?php echo $GLOBALS['wgStyleVersion'] ?>"></script> 
	<script src="<?php $this->text('stylepath' ) ?>/common/ajaxwatch.js?<?php echo $GLOBALS['wgStyleVersion'] ?>"></script> 
	 
   </head> 
<body class="single single-post"> 
 
<div id="wrapper" class="hfeed"> 
	
	<div id="header-wrap"> 
		<div id="header" role="banner"> 
			<h1 id="blog-title"><span><a href="<?php $this->text('scriptpath'); ?>/" title="<?php echo($wgSitename); ?>" rel="home"><?php echo($wgSitename); ?></a></span></h1> 
			<div id="blog-description"><?php $this->msg('tagline') ?></div> 
		</div><!--  #header --> 
 
		<div id="access" role="navigation"> 
			<div class="skip-link"><a href="#content" title="Skip to content">Skip to content</a></div> 
			<div id="menu">

			<ul id="menu-urs" class="menu">
				<?php foreach( $this->data['sidebar']['navigation'] as $key => $val ) { ?>
					<li id="menu-item-<?php echo Sanitizer::escapeId( $val['id'] ) ?>" class="menu-item menu-item-type-post_type menu-item">
						<a href="<?php echo htmlspecialchars( $val['href'] ) ?>"><?php echo htmlspecialchars( $val['text'] ) ?></a>
					</li>
				<?php } ?>
			</ul>
		</div> 
		</div><!-- #access --> 
 
	</div><!--  #header-wrap -->
	<?php if( $this->data['newtalk'] ) { ?>
		<div id="newtalk"><?php $this->html('newtalk') ?></div>
	<?php } ?>
	<div id="container"> 
		<div id="content" role="main">
			<div id="content-container" class="post type-post hentry category-submissions"> 
				<h2 class="entry-title"><?php $this->html('title'); ?></h2> 
				<?php if ($this->data['subtitle']) { ?>
					<span class="entry-sub-title"><?php $this->html('subtitle') ?></span><br/><br/>
				<?php } ?>
				<div class="entry-content"> 
				
				<!-- INSERT WIKI STUFF HERE -->
				<pre><?php //print_r($this->data); ?></pre>
				<pre><? //php print_r($this); ?></pre>
				<?php $this->html('bodytext') ?>
				
				<?php if ( $this->data['dataAfterContent'] ): ?>
                	<!-- dataAfterContent -->
					<?php $this->html( 'dataAfterContent' ); ?>
					<!-- /dataAfterContent -->
				<?php endif; ?>
				

				<br/><br/>
				</div>
				<!-- META -->
				<div class="entry-meta">
					<?php if ($this->data['loggedin'] == 1 
						&& $this->data['content_actions']['edit'] 
						&& $mTitle->quickUserCan('edit')
						&& $mTitle->quickUserCan('create')
						&& $action != "edit" ) { ?>
						<span class="author vcard"><a href="<?php echo($this->data['content_actions']['edit']['href']); ?>">Edit</a></span>
						<span class="meta-sep">|</span>
					<?php } ?>  
					<?php if ($this->data['loggedin'] == 1
							&& $this->data['content_actions']['talk']) { ?>
						<span class="author vcard"><a href="<?php echo($this->data['content_actions']['talk']['href']); ?>">Discuss</a></span>
						<span class="meta-sep">|</span>
					<?php } ?>
					<?php if ($this->data['content_actions']['watch']) { ?>
						<span class="author vcard"><a href="<?php echo($this->data['content_actions']['watch']['href']); ?>">Watch</a></span>
						<span class="meta-sep">|</span>
					<?php } ?>
					<?php if ($this->data['content_actions']['protect']
							|| $this->data['content_actions']['move']
							|| $this->data['content_actions']['delete'] ) { ?>
						<?php $sep = false; ?>
						<span class="author vcard">
						<?php if ($this->data['content_actions']['protect']) { ?>
							<?php $sep = true; ?>
							<a href="<?php echo($this->data['content_actions']['protect']['href']); ?>">Protect</a>
						<?php } ?>
						<?php if ($this->data['content_actions']['move']) { ?>
							<?php if ($sep == true) { ?> | <?php } ?>
							<?php $sep = true; ?>
							<a href="<?php echo($this->data['content_actions']['move']['href']); ?>">Move</a>
						<?php } ?>
						<?php if ($this->data['content_actions']['delete']) { ?>
							<?php if ($sep == true) { ?> | <?php } ?>
							<a href="<?php echo($this->data['content_actions']['delete']['href']); ?>">Delete</a>
						<?php } ?>
						</span>
						<span class="meta-sep">|</span>
					<?php } ?>
					<?php if ($this->data['nav_urls']['whatlinkshere']) { ?>
						<span class="entry-date"><a href="<?php echo($this->data['nav_urls']['whatlinkshere']['href']); ?>">What Links Here</a></span>
						<span class="meta-sep">|</span>
					<?php } ?>
					<?php if ($this->data['lastmod']) { ?>
						<span class="entry-date"><abbr class="published"><?php echo($this->data['lastmod']); ?></abbr></span>
						<span class="meta-sep">|</span>
					<?php } ?>
				</div> 
				<!-- END META -->
			</div><!-- .post --> 
 
			<div id="nav-below" class="navigation"> 
				<?php //if( $this->data['catlinks'] ) { $this->html('catlinks'); } ?>
				<?php echo($this->getCategories()); ?>
			</div> 
 
		</div><!-- #content --> 
	</div><!-- #container --> 
 
 
	<div id="footer-wrap"> 
		<div id="footer-wrap-inner"> 
			<div id="primary" class="footer"> 
				<ul class="xoxo"> 
		
			<li id="nav_menu-3" class="widget widget_nav_menu"> 
				<h3 class="widgettitle"><?php $this->msg('toolbox') ?></h3> 

				<div class="menu-bottom-menu-container">
					<ul id="menu-bottom-menu" class="menu">
					<?php foreach( array( 'mytalk', 'preferences', 'watchlist', 'mycontris', 'logout', 'anonlogin' ) as $special ) { ?>
						<?php if($this->data['personal_urls']) { ?>
							<li class="menu-item menu-item-type-post_type menu-item">
							<a href="<?php echo htmlspecialchars( $this->data['personal_urls'][$special]['href'] ) ?>"><?php echo htmlspecialchars( $this->data['personal_urls'][$special]['text'] ) ?></a>
							</li>
						<?php } ?>
					<?php } ?>

					</ul>
				</div> 
			</li> 
				</ul> 
			</div><!-- #primary .sidebar --> 
 
		<div id="secondary" class="footer"> 
			<ul class="xoxo"> 
	
			<li id="rss-just-better-3" class="widget rssjustbetter"> 
				<h3 class="widgettitle"><?php $this->msg('newpages'); ?></h3> 
				<ul id="newestPages"> </ul> 
 
			</li> 
			</ul> 
		</div><!-- #secondary .sidebar --> 
 
		<div id="ternary" class="footer"> 
			<ul class="xoxo"> 
	
			<li id="meta-2" class="widget widget_meta"> 
				<h3 class="widgettitle"><?php $this->msg('navigation') ?></h3> 
			<ul> 
			<?php if( $this->data['notspecialpage'] ) { ?>
        			<li>
                			<a href="<?php echo htmlspecialchars($this->data['nav_urls']['whatlinkshere']['href']) ?>"><?php $this->msg('whatlinkshere') ?></a>
        			</li>
			<?php } ?>
			<?php if( $this->data['nav_urls']['recentchangeslinked'] ) { ?>
        			<li>
                			<a href="<?php echo htmlspecialchars($this->data['nav_urls']['recentchangeslinked']['href']) ?>"><?php $this->msg('recentchangeslinked') ?></a>
        			</li>
			<? } ?>
			<?php if( isset( $this->data['nav_urls']['trackbacklink'] ) ) { ?>
        			<li>
                			<a href="<?php echo htmlspecialchars($this->data['nav_urls']['trackbacklink']['href']) ?>"><?php $this->msg('trackbacklink') ?></a>
        			</li>
			<?php } ?>
			<?php if( isset( $this->data['nav_urls']['specialpages'] ) ) { ?>
        			<li>
                			<a href="<?php echo htmlspecialchars($this->data['nav_urls']['specialpages']['href']) ?>"><?php $this->msg('specialpages') ?></a>
        			</li>
			<?php } ?>

			</ul> 
 
			</li> 
 
			<li id="search-2" class="widget widget_search"> 
				<h3 class="widgettitle"><?php $this->msg('search') ?></h3> 

<form action="<?php $this->text('searchaction') ?>" id="searchform">
	<div><label class="screen-reader-text" for="s">Search for:</label> 
	<input id="s" name="search" type="text" 
			<?php if( isset( $this->data['search'] ) ) { ?> 
			value="<?php $this->text('search') ?>"
			<?php } ?> 
	/>
	<input type='submit' name="fulltext" class="searchButton" id="searchsubmit" value="<?php $this->msg('searchbutton') ?>" />
	</div> 
	</form> 
			</li> 
			</ul> 
		</div><!-- #ternary .sidebar --> 
			<div id="footer"> 
			</div><!-- #footer --> 
		</div><!-- #footer-wrap-inner --> 
	</div><!-- #footer-wrap --> 
 
</div><!-- #wrapper .hfeed --> 
<script type="text/javascript">
jQuery(window).load(function() {
  jQuery.getJSON(wgScriptPath + '/api.php?action=query&format=json&list=recentchanges&rctype=new&rclimit=5&rcnamespace=0', function(data) {
  	var container = jQuery('#newestPages');
  	container.empty();
  	
  	var results = data['query']['recentchanges'];
  	var resultlen = results.length;
  	for (i = 0; i < resultlen; i++) {
  		var result = results[i];
  		var timestamp = result['timestamp'];
  		var title = result["title"];
  		var encodedTitle = encodeURI(title);
  		var year = timestamp.substring(2,4);
  		var month = timestamp.substring(5,7);
  		var day = timestamp.substring(8,10);
		var path = wgArticlePath.replace('\$1', encodedTitle);
  		
  		var formatdate = month + "." + day + "." + year;
  		//alert(result['title']);
  		container.append('<li>' + formatdate + ' - <a href="' + wgServer + path + '">' + title + '</a></li>'); 
  	}
  });
});
</script>
</script>
<script type='text/javascript' src='<?php $this->text('stylepath' ) ?>/erudite/js/jquery.scrollTo-min.js?<?php echo $GLOBALS['wgStyleVersion'] ?>'></script> 

<script type='text/javascript' src='<?php $this->text('stylepath' ) ?>/erudite/js/common.js?<?php echo $GLOBALS['wgStyleVersion'] ?>'></script> 
</body> 
</html> 
		<?php
	}
}

?>
