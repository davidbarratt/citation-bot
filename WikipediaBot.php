<?php
require_once(HOME . "credentials/doiBot.login");
use MediaWiki\OAuthClient\Consumer;
use MediaWiki\OAuthClient\Token;
use MediaWiki\OAuthClient\Request;
use MediaWiki\OAuthClient\SignatureMethod\HmacSha1;

class WikipediaBot extends Snoopy {
  
  private $editToken;
  
  public function logged_in() {
    $this->fetch(API_ROOT . 'action=query&assert=user');
    $response = json_decode($this->results);
    if (isset($response->error)) {
      return FALSE;
    } elseif(isset($response->batchcomplete)) {
      return TRUE;
    } else {
      warning("Unexpected response from API: ");
      print_r($response);
      return NULL;
    }
  }
  
  public function log_in() {
    quiet_echo("\n Establishing connection to Wikipedia servers with username " . USERNAME . "... ");
    # $consumer = new Consumer( OAUTH_CONSUMER_TOKEN, OAUTH_SECRET_TOKEN );
    # $accessToken = new Token( $accessToken, $accessSecret );
    # $request = Request::fromConsumerAndToken( $consumer, $accessToken, 'GET', 'https://en.wikipedia.org/w/api.php', $apiParams );
    # $request->signRequest( new HmacSha1(), $consumer, $accessToken );
    # $authorizationHeader = $request->toHeader();
    
    $this->fetch(API_ROOT . 'action=query&meta=tokens&type=login');
    $response = json_decode($this->results);
    if (!isset($response->batchcomplete)) {
      trigger_error("Login to Wikipedia servers failed", E_USER_WARNING);
      return FALSE;
    }
    $submit_vars["format"] = "json";
    $submit_vars["action"] = "login";
    $submit_vars["lgname"] = BOT_LOGIN;
    $submit_vars["lgpassword"] = BOT_PASSWORD;
    $loginToken = $response->query->tokens->logintoken;
    $submit_vars["lgtoken"] = $loginToken;
    $this->submit(API_ROOT, $submit_vars);
    $login_result = json_decode($this->results);
    
    if (isset($login_result->login->result) && $login_result->login->result == "Success") {
      quiet_echo("\n Using account " . htmlspecialchars($login_result->login->lgusername) . ".");
      // Add other cookies, which are necessary to remain logged in.
      $cookie_prefix = "enwiki";
      $this->cookies[$cookie_prefix . "UserName"] = $login_result->login->lgusername;
      $this->cookies[$cookie_prefix . "UserID"] = $login_result->login->lguserid;
      $this->fetch(API_ROOT . 'action=query&meta=tokens');
      $tokenResponse = json_decode($this->results);
      if (isset($tokenResponse->query->tokens->csrftoken)) {
        $this->editToken = $tokenResponse->query->tokens->csrftoken;
        return TRUE;
      } else {        
        trigger_error("Didn't receive edit tokens after login", E_USER_WARNING);
        return FALSE;
      }
    } else {
      echo("\n Could not log in to Wikipedia servers.  Edits will not be committed.\n");
      global $ON;
      $ON = FALSE;
      return FALSE;
    }
  }
  
  public function write_page($page, $text, $editSummary, $lastRevId = NULL) {
    // Check that bot is logged in:
      if (!$this->logged_in() && !$this->log_in()) {
        echo "\n ! LOGGED OUT:  The bot failed to log in to the Wikipedia servers";
        return FALSE;
      }
      
      $this->fetch(API_ROOT . 'action=query&prop=info|revisions&titles=' .
                    urlencode($page));
      $response = json_decode($this->results);
      if (isset($response->warnings)) {
        trigger_error((string) $response->warnings->info->{'*'}, E_USER_WARNING);
      }
      if (!isset($response->batchcomplete)) {
        trigger_error("Write request triggered no response from server", E_USER_WARNING);
        return FALSE;
      }
      
      $myPage = reset($response->query->pages); // reset gives first element in list
      if (!isset($myPage->lastrevid)) {
        trigger_error(" ! Page seems not to exist. Aborting.", E_USER_WARNING);
        return FALSE;
      }
      if (!is_null($lastRevId) && $myPage->lastrevid != $lastRevId) {
        echo "\n ! Possible edit conflict detected. Aborting.";
        return FALSE;
      }
      if (stripos($text, "CITATION_BOT_PLACEHOLDER") != FALSE)  {
        trigger_error("\n ! Placeholder left escaped in text. Aborting.", E_USER_WARNING);
        return FALSE;
      }
      
      // No obvious errors; looks like we're good to go ahead and edit
      $submit_vars = array(
          "action" => "edit",
          "title" => $page,
          "text" => $text,
          "token" => $this->editToken,
          "summary" => $editSummary,
          "minor" => "1",
          "bot" => "1",
          "basetimestamp" => $myPage->touched,
          #"md5"       => hash('md5', $data), // removed because I can't figure out how to make the hash of the UTF-8 encoded string that I send match that generated by the server.
          "watchlist" => "nochange",
          "format" => "json",
      );
      $this->submit(API_ROOT, $submit_vars);
      $result = json_decode($this->results);
      if (isset($result->edit) && $result->edit->result == "Success") {
        // Need to check for this string whereever our behaviour is dependant on the success or failure of the write operation
        if (HTML_OUTPUT) {
          echo "\n <span style='color: #e21'>Written to <a href='" 
          . WIKI_ROOT . "title=" . urlencode($myPage->title) . "'>" 
          . htmlspecialchars($myPage->title) . '</a></span>';
        }
        else echo "\n Written to " . htmlspecialchars($myPage->title) . '.  ';
        return TRUE;
      } elseif (isset($result->edit->result)) {
        echo htmlspecialchars($result->edit->result);
        return TRUE;
      } elseif ($result->error->code) {
        // Return error code
        echo "\n ! Write error: " . htmlspecialchars(strtoupper($result->error->code)) . ": " . str_replace(array("You ", " have "), array("This bot ", " has "), htmlspecialchars($result->error->info));
        return FALSE;
      } else {
        echo "\n ! Unhandled error.  Please copy this output and <a href=http://code.google.com/p/citation-bot/issues/list>report a bug.</a>";
        return FALSE;
      }
  }
  
}
?>
