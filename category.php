<?php
@session_start();
error_reporting(E_ALL^E_NOTICE);
define("HTML_OUTPUT", !isset($argv));
require_once('setup.php');
$api = new WikipediaBot();
if (!isset($argv)) $argv=[]; // When run as a webpage, this does not get set
$argument["cat"] = NULL;
foreach ($argv as $arg) {
  if (substr($arg, 0, 2) == "--") {
    $argument[substr($arg, 2)] = 1;
    unset($oArg);
  } elseif (substr($arg, 0, 1) == "-") {
    $oArg = substr($arg, 1);
  } else {
    if (!isset($oArg)) report_error('Unexpected text: ' . $arg);
    switch ($oArg) {
      case "P": case "A": case "T":
        $argument["pages"][] = $arg;
        break;
      default:
      $argument[$oArg][] = $arg;
    }
  }
}

$SLOW_MODE = FALSE;
if (isset($_REQUEST["slow"]) || isset($argument["slow"])) {
  $SLOW_MODE = TRUE;
}

$category = trim($argument["cat"] ? $argument["cat"][0] : $_REQUEST["cat"]);
if (strtolower(substr($category, 0, 9)) == 'category:') $category = trim(substr($category, 9));

if (HTML_OUTPUT) {
?>
<!DOCTYPE html>
<html>
  <body>
  <head>
  <title>Citation bot: Category mode</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" type="text/css" href="css/results.css" />
  </head>
  <body>
    <pre>
<?php
}

$edit_summary_end = "| Activated by [[User:$api->get_the_user()]] | [[Category:$category]].";

if ($category) {
  $attempts = 0;
  $pages_in_category = $api->category_members($category);
  if (!is_array($pages_in_category) || empty($pages_in_category)) {
    echo('Category appears to be empty');
    html_echo(' </pre></body></html>', "\n");
    exit(0);
  }
  shuffle($pages_in_category);
  $page = new Page();
  #$pages_in_category = array('User:DOI bot/Zandbox');
  foreach ($pages_in_category as $page_title) {
    // $page->expand_text will take care of this notice if we are in HTML mode.
    html_echo('', "\n\n\n*** Processing page '" . echoable($page_title) . "' : " . date("H:i:s") . "\n");
    if ($page->get_text_from($page_title, $api) && $page->expand_text()) {
      report_phase("Writing to " . echoable($page_title) . '... ');
      while (!$page->write($api, $edit_summary_end) && $attempts < 2) ++$attempts;
      // Parsed text can be viewed by diff link; don't clutter page. 
      // echo "\n\n"; safely_echo($page->parsed_text());
      if ($attempts < 3 ) {
        html_echo(
        " | <a href=" . WIKI_ROOT . "?title=" . urlencode($page_title) . "&diff=prev&oldid="
        . $api->get_last_revision($page_title) . ">diff</a>" .
        " | <a href=" . WIKI_ROOT . "?title=" . urlencode($page_title) . "&action=history>history</a>", ".");
      } else {
         report_warning("Write failed.");
      }
    } else {
      report_phase($page->parsed_text() ? 'No changes required.' : 'Blank page');
      echo "\n\n    # # # ";
    }
  }
  echo ("\n Done all " . count($pages_in_category) . " pages in Category:$category. \n");
} else {
  echo ("You must specify a category.  Try appending ?cat=Blah+blah to the URL, or -cat Category_name at the command line.");
}
html_echo(' # # #</pre></body></html>', "\n");
exit(0);
