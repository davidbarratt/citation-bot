<?php

/* junior_test - tests a name for a Junior appellation
 *  Input: $name - the name to be tested
 * Output: array ($name without Jr, if $name ends in Jr, Jr)
 */
function junior_test($name) {
  $junior = (substr($name, -3) == " Jr")?" Jr":FALSE;
  if ($junior) {
    $name = substr($name, 0, -3);
  } else {
    $junior = (substr($name, -4) == " Jr.")?" Jr.":FALSE;
    if ($junior) {
      $name = substr($name, 0, -4);
    }
  }
  if (substr($name, -1) == ",") {
    $name = substr($name, 0, -1);
  }
  return array($name, $junior);
}

function de_wikify($string){
  return str_replace(Array("[", "]", "'''", "''", "&"), Array("", "", "'", "'", ""), preg_replace(Array("~<[^>]*>~", "~\&[\w\d]{2,7};~", "~\[\[[^\|\]]*\|([^\]]*)\]\]~"), Array("", "", "$1"),  $string));
}

function truncate_publisher($p){
  return preg_replace("~\s+(group|inc|ltd|publishing)\.?\s*$~i", "", $p);
}

function format_surname($surname) {
  if ($surname == '-') return '';
  if (preg_match('~^\S\.?$~u', $surname)) return mb_strtoupper($surname); // Just a single initial, with or without period
  $surname = mb_convert_case(trim(mb_ereg_replace("-", " - ", $surname)), MB_CASE_LOWER);
  if (mb_substr($surname, 0, 2) == "o'") {
        return "O'" . format_surname_2(mb_substr($surname, 2));
  } elseif (mb_substr($surname, 0, 2) == "mc") {
        return "Mc" . format_surname_2(mb_substr($surname, 2));
  } elseif (mb_substr($surname, 0, 3) == "mac" && strlen($surname) > 5 && !mb_strpos($surname, "-") && mb_substr($surname, 3, 1) != "h") {
        return "Mac" . format_surname_2(mb_substr($surname, 3));
  } elseif (mb_substr($surname, 0, 1) == "&") {
        return "&" . format_surname_2(mb_substr($surname, 1));
  } else {
        return format_surname_2($surname); // Case of surname
  }
}
function format_surname_2($surname) {
  $ret = preg_replace_callback("~(\p{L})(\p{L}+)~u", 
        function($matches) {
                return mb_strtoupper($matches[1]) . mb_strtolower($matches[2]);
        },
    mb_ereg_replace(" - ", "-", $surname));
  $ret = str_ireplace(array('Von ', 'Und ', 'De La '), array('von ', 'und ', 'de la '), $ret);
  $ret = preg_replace_callback('~;\w~', function($matches) {return strtolower($matches[0]);}, $ret);
  return $ret;
}

function format_forename($forename){
  if ($forename == '-') return '';
  return str_replace(array(" ."), "", trim(preg_replace_callback("~(\p{L})(\p{L}{3,})~u",  function(
            $matches) {
            return mb_strtoupper($matches[1]) . mb_strtolower($matches[2]);}
         , $forename)));
}

/* format_initials
 * Returns a string of initals, formatted for Cite Doi output
 *
 * $str: A series of initials, in any format.  NOTE! Do not pass a forename here!
 *
 */
function format_initials($str) {
  $str = trim($str);
        if ($str == "") return FALSE;
        $end = (substr($str, strlen($str)-1) == ";") ? ";" : '';
        preg_match_all("~\w~", $str, $match);
        return mb_strtoupper(implode(".",$match[0]) . ".") . $end;
}

function is_initials($str){
        $str = trim($str);
        if (!$str) return FALSE;
        if (strlen(str_replace(array("-", ".", ";"), "", $str)) >3) return FALSE;
        if (strlen(str_replace(array("-", ".", ";"), "", $str)) ==1) return TRUE;
        if (mb_strtoupper($str) != $str) return FALSE;
        return TRUE;
}

/*
 * author_is_human
 * Runs some tests to see if the full name of a single author is unlikely to be the name of a person.
 */
function author_is_human($author) {
  $author = trim($author);
  $chars = count_chars($author);
  if ($chars[ord(":")] > 0 || $chars[ord(" ")] > 3 || strlen($author) > 33
    || substr(strtolower($author), 0, 4) === "the " 
    || (str_ireplace(NON_HUMAN_AUTHORS, '', $author) != $author)  // This is the use a replace to see if a substring is present trick
    || preg_match("~[A-Z]{3}~", $author)
    || substr(strtolower($author),-4) === " inc"
    || substr(strtolower($author),-5) === " inc."
    || substr_count($author, ' ') > 3 // Even if human, hard to format
  ) {
    return FALSE;
  }
  return TRUE;
}

// Returns the author's name formatted as Surname, F.I.
function format_author($author){
  
  // Requires an author who is formatted as SURNAME, FORENAME or SURNAME FORENAME or FORENAME SURNAME. Substitute initials for forenames if nec.
  $surname = '';
  // Google and Zotero sometimes have these
  $author = preg_replace("~ ?\((?i)sir(?-i)\.?\)~", "", html_entity_decode($author, ENT_COMPAT | ENT_HTML401, 'UTF-8'));

  $ends_with_period = (substr(trim($author), -1) === ".");
  
  $author = preg_replace("~(^[;,.\s]+|[;,.\s]+$)~", "", trim($author)); //Housekeeping
  $author = preg_replace("~^[aA]nd ~", "", trim($author)); // Just in case it has been split from a Smith; Jones; and Western
  if ($author == "") {
      return FALSE;
  }

  $auth = explode(",", $author);
  if (isset($auth[1])) {
    /* Possibilities:
    Smith, A. B.
    */
    $surname = $auth[0];
    $fore = $auth[1];
  }
  //Otherwise we've got no handy comma to separate; we'll have to use spaces and periods.
  else {
    $auth = explode(".", $author);
    if (isset($auth[1])){
      /* Possibilities are:
      M.A. Smith
      Smith M.A.
      Smith MA.
      Martin A. Smith
      MA Smith.
      Martin Smith.
      */
      $countAuth = count($auth);
      if ($ends_with_period) {
        $i = array();
        // it ends in a .
        if (is_initials($auth[$countAuth-1])) {
          // it's Conway Morris S.C.
          foreach (explode(" ", $auth[0]) as $bit){
            if (is_initials($bit)) $i[] = format_initials($bit); else $surname .= "$bit ";
          }
          unset($auth[0]);
          foreach ($auth as $bit){
            if (is_initials($bit)) {
              $i[] = format_initials($bit) . '.';
            } else {
              $i[] = $bit;
            }
          }
        } else {
          foreach ($auth as $A) {
            if (is_initials($A)) {
                $i[] = format_initials($A) . '.';
            } else {
                $i[] = $A;
            }
          }
        }
        $fore = mb_strtoupper(implode(" ", $i));
      } else {
        // it ends with the surname
        $surname = $auth[$countAuth-1];
        unset($auth[$countAuth-1]);
        $fore = implode(".", $auth);
      }
    } else {
      // We have no punctuation! Let's delimit with spaces.
      $chunks = array_reverse(explode(" ", $author));
      $i = array();
      foreach ($chunks as $chunk){
        if (!$surname && !is_initials($chunk)) $surname = $chunk;
        else array_unshift($i, is_initials($chunk)?format_initials($chunk):$chunk);
      }
      $fore = implode(" ", $i);
    }
  }
  // Special cases when code cannot fully determine things, or if the name is only Smith
  if (trim($surname) == '') { // get this with A. B. C.
    $full_name = format_forename($fore);
  } elseif (trim($fore) == '') {  // Get this with just Smith
    $full_name = format_surname($surname);
  } else {
    $full_name = format_surname($surname) . ", " . format_forename($fore);
  }
  $full_name = str_replace("..", ".", $full_name);  // Sometimes add period after period
  $full_name = str_replace(".", ". ", $full_name);  // Add spaces after all periods
  $full_name = str_replace(["   ", "  "], [" ", " "], $full_name); // Remove extra spaces
  return trim($full_name);
}

function format_multiple_authors($authors, $returnAsArray = FALSE){
  $authors = html_entity_decode($authors, ENT_COMPAT | ENT_HTML401, "UTF-8");

  $return = array();
  ## Split the citation into an author by author account
  $authors = preg_replace(array("~\band\b~i", "~[\d\+\*]+~"), ";", $authors); //Remove "and" and affiliation symbols

  $authors = str_replace(array("&nbsp;", "(", ")"), array(" "), $authors); //Remove spaces and weird puntcuation
  $authors = str_replace(array(".,", "&", "  "), ";", $authors); //Remove "and"
  if (preg_match("~[,;]$~", trim($authors))) $authors = substr(trim($authors), 0, strlen(trim($authors))-1); // remove trailing punctuation

  $authors = trim($authors);
  if ($authors == "") {
    return FALSE;
  }

  $authors = explode(";", $authors);
  #dbg(array("IN"=>$authors));
  $savedChunk = '';
  if (isset($authors[1])) {
    foreach ($authors as $A){
      if (trim($A) != "") $return[] = format_author($A);
    }
  } else {
    //Use commas as delimiters
    $chunks = explode(",", $authors[0]);
    foreach ($chunks as $chunk){
      $chunk = trim($chunk);
      if ($chunk == '') continue; // Odd things with extra commas
      $bits = explode(" ", $chunk);
      $bitts = array();
      foreach ($bits as $bit){
        if ($bit) $bitts[] = $bit;
      }
      $bits = $bitts; unset($bitts);
      #dbg($bits, '$BITS');
      if ((isset($bits[1]) && $bits[1]) || $savedChunk) {
        $return[] = format_author($savedChunk .  ($savedChunk?", ":"") . $chunk);
        $savedChunk = '';
      } else {
        $savedChunk = $chunk;// could be first author, or an author with no initials, or a surname with initials to follow.
      }
    }
  }
  if ($savedChunk) $return[0] = $bits[0];
  $return = implode("; ", $return);
  $frags = explode(" ", $return);
  $return = array();
  foreach ($frags as $frag){
    $return[] = is_initials($frag)?format_initials($frag):$frag;
  }
  $returnString = preg_replace("~;$~", "", trim(implode(" ", $return)));
  if ($returnAsArray){
    $authors = explode ( "; ", $returnString);
    return $authors;
  } else {
    return $returnString;
  }
}

function straighten_quotes($str) {
  $str = preg_replace('~&#821[679];|&#39;|&#x201[89];|[\x{FF07}\x{2018}-\x{201B}`]|&[rl]s?[b]?quo;~u', "'", $str);
  if((mb_strpos($str, '&rsaquo;') !== FALSE && mb_strpos($str, '&[lsaquo;')  !== FALSE) ||
     (mb_strpos($str, '\x{2039}') !== FALSE && mb_strpos($str, '\x{203A}') !== FALSE) ||
     (mb_strpos($str, '‹')        !== FALSE && mb_strpos($str, '›')        !== FALSE)) { // Only replace single angle quotes if some of both
     $str = preg_replace('~&[lr]saquo;|[\x{2039}\x{203A}]|[‹›]~u', "'", $str);           // Websites tiles: Jobs ›› Iowa ›› Cows ›› Ames
  }	
  $str = preg_replace('~&#822[013];|[\x{201C}-\x{201F}]|&[rlb][d]?quo;~u', '"', $str);
  if((mb_strpos($str, '&raquo;')  !== FALSE && mb_strpos($str, '&laquo;')  !== FALSE) ||
     (mb_strpos($str, '\x{00AB}') !== FALSE && mb_strpos($str, '\x{00AB}') !== FALSE) ||
     (mb_strpos($str, '«')        !== FALSE && mb_strpos($str, '»')        !== FALSE)) { // Only replace double angle quotes if some of both
     $str = preg_replace('~&[lr]aquo;|[\x{00AB}\x{00BB}]|[«»]~u', '"', $str);            // Websites tiles: Jobs » Iowa » Cows » Ames
  }
  return $str;
}

function can_safely_modify_dashes($value) {
   return((stripos($value, "http") === FALSE)
       && (strpos($value, "[//") === FALSE)
       && (stripos($value, 'CITATION_BOT_PLACEHOLDER_COMMENT') === FALSE)
       && (strpos($value, "(") === FALSE)
       && (substr_count($value, '-') + substr_count($value, '–') < 3) // This line helps us ignore with 1-5–1-6 stuff
       && (preg_match('~^[a-zA-Z]+[0-9]*.[0-9]+$~u',$value) !== 1)); // A-3, A3-5 etc.  Use "." for generic dash
}

function titles_are_similar($title1, $title2) {
  return !titles_are_dissimilar($title1, $title2);
}

function titles_are_dissimilar($inTitle, $dbTitle) {
        // Reduce punctuation
        $inTitle = straighten_quotes(str_replace(array(" ", "\n", "\r", "-", "—"), "", mb_strtolower((string) $inTitle)));
        $dbTitle = straighten_quotes(str_replace(array(" ", "\n", "\r", "-", "—"), "", mb_strtolower((string) $dbTitle)));
        $inTitle = str_replace(array("'", '"'), "", $inTitle); // Drop normal quotes
        $dbTitle = str_replace(array("'", '"'), "", $dbTitle);
        // Strip trailing periods
        $inTitle = trim(rtrim($inTitle, '.'));
        $dbTitle = trim(rtrim($dbTitle, '.'));
        // Strip trailing (Third Edition)
        $inTitle = preg_replace('~\([^\s\(\)]+ Edition\)^~iu', '', $inTitle);
        $dbTitle = preg_replace('~\([^\s\(\)]+ Edition\)^~iu', '', $dbTitle);
        // Strip trailing Online
        $inTitle = preg_replace('~ Online$~iu', '', $inTitle);
        $dbTitle = preg_replace('~ Online$~iu', '', $dbTitle);
        // Strip leading the
        $inTitle = preg_replace('~^The ~iu', '', $inTitle);
        $dbTitle = preg_replace('~^The ~iu', '', $dbTitle);
        return ((strlen($inTitle) > 254 || strlen($dbTitle) > 254)
              ? (strlen($inTitle) != strlen($dbTitle)
                || similar_text($inTitle, $dbTitle) / strlen($inTitle) < 0.98)
              : levenshtein($inTitle, $dbTitle) > 3
        );
}
