<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Emiel de Grijs <emiel@silverfactory.net>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(PATH_typo3conf."ext/feedforward/pi1/class.tx_feedforward_feeditem.php");

class tx_feedforward_feed {
	
	/* Data fields */
	var $title;
	var $link;
	var $description;
	var $language;
	var $image = array();
	var $generator;
	var $docs;
	var $lastBuildDate;

	/* Control fields */
	var $session = null;			//CURL session
	var $errorMessage = "";			//Message in case of an error
	var $feedAddress = "";			//Supplied address of the feed
	var $feedType = FT_UNKNOWN;		//Type of feed (Atom, RSS or unknown)
	var $feedBody = "";				//Retrieved content of the feed
	var $feedSize = 0;				//Size of the retrieved feed content
	var $feedContentType = null;	//Content type of the retrieved feed
    var $stack = array();
	var $cache_dir = '';			//Cache directory
	var $cache_time = '';			//Cache timeout/TTL
    	
	function tx_feedforward_feed() {
		if (!extension_loaded("curl")) {
			$this->setErrorMessage("No CURL library loaded. Contact your webhost administrator.");
			return;
		}
		$this->session = curl_init();
		if (!$this->session) {
			$this->setErrorMessage("Could not initialize CURL session. Contact your webhost administrator.");
			return;
		}
	}

	function open($rss_url, $parsepref = 0) {
		$this->feedAddress = $rss_url;
		if ($this->isValid()) {
			// If CACHE ENABLED
			if ($this->cache_dir != '') {
				$cache_file = $this->cache_dir . '/rsscache_' . md5($rss_url);
				$timedif = @(time() - filemtime($cache_file));
				if ($timedif < $this->cache_time) {
					// cached file is fresh enough, return cached array
					$rss_result = unserialize(join('', file($cache_file)));
					$this->feedBody			= $rss_result['feedBody'];
					$this->feedContentType	= $rss_result['feedContentType'];
					$this->feedSize			= $rss_result['feedSize'];

					// set 'cached' to 1 only if cached file is correct
					if ($rss_result) $rss_result['cached'] = 1;
				} else {
					// cached file is too old, create new
					$this->setOptions($rss_url);
					$this->feedBody = curl_exec($this->session);
					$this->feedContentType = curl_getinfo($this->session, CURLINFO_CONTENT_TYPE);
					$this->feedSize = round(strlen($this->session)/1024,1);
					if ((strlen($this->feedBody) == 0) or ($this->feedContentType == null)) {
						$this->setErrorMessage("No feed available.");
					}
					$rss_result['feedBody']			= $this->feedBody;
					$rss_result['feedContentType']	= $this->feedContentType;
					$rss_result['feedSize']			= $this->feedSize;
					
					$serialized = serialize($rss_result);
					if ($f = @fopen($cache_file, 'w')) {
						if (fwrite ($f, $serialized, strlen($serialized)) === FALSE) {
							//trigger_error("Could not write to cache", E_USER_WARNING);
						}
						fclose($f);
					} else {
						//trigger_error("Could not open cache for writing", E_USER_WARNING);
					}
					if ($rss_result) $rss_result['cached'] = 0;
				}
			}
			// If CACHE DISABLED >> load and parse the file directly
			else {
				$this->setOptions($rss_url);
				$this->feedBody = curl_exec($this->session);
				$this->feedContentType = curl_getinfo($this->session, CURLINFO_CONTENT_TYPE);
				$this->feedSize = round(strlen($this->session)/1024,1);
				if ((strlen($this->feedBody) == 0) or ($this->feedContentType == null)) {
					$this->setErrorMessage("No feed available.");
				}
			}
		}
		
		require_once(PATH_typo3conf."ext/feedforward/pi1/class.tx_feedforward_parser.php");
		$feed = new tx_feedforward_parser($this->feedAddress, $parsepref);
		$feed->open($this->feedBody);
		$this->stack = $feed->getItems();
	}
	
	function getItems() {
		return $this->stack;
	}
	
	function show() {
		echo $this->feedBody;
	}
	
	/*******************************
	* Private processing routines  *
	*******************************/
	function setOptions($feed) {
		curl_setopt($this->session, CURLOPT_URL, $feed);
		curl_setopt($this->session, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->session, CURLOPT_FAILONERROR, 1);
	}

	/***************************
	* Error handling routines  *
	***************************/
	function setErrorMessage($msg = "") {
		$this->errorMessage = $msg;
	}
	
	function isValid() {
		if (strlen($this->errorMessage) == 0) {
			return true;
		} else {
			return false;
		}
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/feedforward/pi1/class.tx_feedforward_feed.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/feedforward/pi1/class.tx_feedforward_feed.php']);
}

?>