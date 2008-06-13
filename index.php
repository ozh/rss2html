<?php

function r2h_form() {
	echo <<<HTML
	<form method="post" action="">
	<input type="hidden" name="parse" value="1" />
	<input type="text" name="url" value="http://example.com/feed/" size="40" />
	<input type="submit" name="go" value="Parse"/>
	</form>
	<small>Enter your feed URL. If you are using a feed redirection service (like Feedburner) enter the <b>real non-redirected</b> URL of your feed (ie. yourblog.com/wp-feed.php)</small>
HTML;
}

function r2h_parse($url) {
	include "./simplepie.php";
	$rssurl = $url . '?&'.str_replace(array('0.',' '), '', microtime());

	// Create lastRSS object
	$feed = new SimplePie();
	$feed->enable_cache(false);
	$feed->set_feed_url($rssurl);
	$feed->init();
	$feed->handle_content_type();
	
	if ( $feed->error ) {
		r2h_header();
		$feed->error = str_replace($rssurl, $url, $feed->error);
		echo "<h3>Error: ".$feed->error."</h3>";
		r2h_retry();
		die();
	}
	
	//echo "<pre>".htmlentities(print_r($feed,true));die('ok');
	
	// Head:
	$title = $feed->get_title();
	$link = $feed->get_link();
	$description = $feed->get_description();
	$encoding = ($feed->get_encoding()) ? $feed->get_encoding() : 'UTF-8';
	$number = $feed->get_item_quantity();
	
	r2h_header($encoding);
	
	echo <<<HEAD
	<div id="title">
	<h3><a href="$link">$title</a></h3>
	<h4>$description</h4>
	Found $number items
	</div>
HEAD;
	
	$i=0;
	$items = $feed->get_items();
	foreach($items as $item) {
		$i++;
		$title = $item->get_title();
		$link = $item->get_permalink();
		$content = $item->get_content();
		echo <<<ITEM
		<div class="postlabel">#$i</div>
		<div class="post">
		<h5><a href="$link">$title</a><h5>
		<div class="content">$content</div>
		</div>
ITEM;
	
	}

	r2h_retry();

}

function r2h_retry() {
	echo '</div>
	<div id="retry"><a href="">Retry?</a></div>';

}

function r2h_header($encoding = 'utf-8') {
	echo <<<HEAD
<html>
<head>
<title>RSS to HTML</title>
<meta http-equiv="Content-Type" content="text/html; charset=$encoding" />
<style>
body {margin:0;padding:0;background:#cfebf7;font-size:12pt;font-family:sans-serif}
h1 {background:#07273e;margin:0;padding:0.2em 0.5em;color:#fff}
h2 {background:#14568a;margin:0;padding:0.2em 0.5em;color:#fff}
#wrap {padding:1em}
h3, h4, h5 {margin:0.2em 0}
.postlabel {background:#66a;color:#fff;width:3em;text-align:center;
-moz-border-radius-topleft:10px;-moz-border-radius-topright:10px;
font-size:80%;font-weight:bolder;color:#cfebf7}
.post {border:2px solid #66a;padding:1em;margin-bottom:1em;background:#fff;-moz-border-radius-bottomleft:10px;-moz-border-radius-bottomright:10px;}
#retry {background:#336; padding:0.2em;font-size:200%;text-align:center}
#retry a, #footer a {color:#fff}
#title {background:#fff; -moz-border-radius:10px;border:2px solid #66a;padding:0.3em 1em;margin-bottom:1em;}
a, a:visited {color:blue}
a:hover {color:red}
#footer {background:#14568a;color:#fff;padding:0.5em 1em;text-align:right;font-size:90%}
</style>
</head>
<body>
<h1>RSS to HTML</h1>
<h2>A simple feed viewer, turning your RSS into plain HTML</h2>
<div id="wrap">
HEAD;
}

// Let's go:
if ($_POST['parse'] == 1) {
	r2h_parse($_POST['url']);
} else {
	r2h_header();
	r2h_form();
}

?>
</div>
<div id="footer">This simple script outputs your RSS feed as plain HTML.<br/>Nothing is cached so your feed is always fetched to reflect any change you'd make.<br/>Not to be used for feed validation, content-type or encoding testing, or anything sensitive.<br/>Please don't abuse this tool.<br/>Make quickly with SimplePie<br/><a href="http://planetozh.com">Ozh</a></div>
<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
_uacct = "UA-55088-4";
__utmSetVar("NotBlog");
urchinTracker();
</script>
</body>
</html>
