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

require_once(PATH_tslib.'class.tslib_pibase.php');
define("L_WORDS", 0);
define("L_CHARS", 1);
define("SORT_ASC", 0);
define("SORT_DESC", 1);

/**
 * Plugin 'Remote RSS feed' for the 'feedforward' extension.
 *
 * @author	Emiel de Grijs <emiel@silverfactory.net>
 * @package	TYPO3
 * @subpackage	tx_feedforward
 */
class tx_feedforward_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_feedforward_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_feedforward_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'feedforward';	// The extension key.
	var $pi_checkCHash = true;
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		
		// Init and get the flexform data of the plugin
		$this->pi_initPIflexForm();

		// Assign the flexform data to a local variable for easier access
		$piFlexForm = $this->cObj->data['pi_flexform'];

		// Retrieve source text with markers from the PlugIn FlexForm
		$feedurl = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'feedurl', 'sGeneral'); // Get from FlexForm	
		$maxitems = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'maxitems', 'sGeneral'); // Get from FlexForm	
		$itemlength = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'itemlength', 'sGeneral'); // Get from FlexForm	
		$length_unit = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'length_unit', 'sGeneral'); // Get from FlexForm	
		$sorting_pref = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'sorting_pref', 'sGeneral'); // Get from FlexForm	
		$parsepref = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'parsepref', 'sGeneral'); // Get from FlexForm	
		$cache_time = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'cache_time', 'sGeneral'); // Get from FlexForm	
		$cache_dir = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'cache_dir', 'sGeneral'); // Get from FlexForm	
		if (!$cache_dir) $cache_dir = t3lib_extMgm::extPath($this->extKey).'./temp';
		
		$show_linktitle = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'link_title', 'sPublishing'); // Get from FlexForm	
		$titledate = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'titledate', 'sPublishing'); // Get from FlexForm	
		$date_format = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'date_format', 'sPublishing'); // Get from FlexForm	
		$show_subtitle = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'show_subtitle', 'sPublishing'); // Get from FlexForm	
		$suppress_date_subtitle = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'suppress_date_subtitle', 'sPublishing'); // Get from FlexForm	
		$suppress_author_subtitle = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'suppress_author_subtitle', 'sPublishing'); // Get from FlexForm	
		$suppress_site_subtitle = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'suppress_site_subtitle', 'sPublishing'); // Get from FlexForm	
		$show_content = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'show_content', 'sPublishing'); // Get from FlexForm	
		$show_readmore = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'show_readmore', 'sPublishing'); // Get from FlexForm	
		$published_text = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'published_text', 'sPublishing'); // Get from FlexForm	
		$on_text = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'on_text', 'sPublishing'); // Get from FlexForm	
		$by_text = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'by_text', 'sPublishing'); // Get from FlexForm	
		$at_text = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'at_text', 'sPublishing'); // Get from FlexForm	
		$readmore_text = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'readmore_text', 'sPublishing'); // Get from FlexForm	

		$css_header = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'css_header', 'sStyle'); // Get from FlexForm	
		$css_subtitle = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'css_subtitle', 'sStyle'); // Get from FlexForm	
		$css_body = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'css_body', 'sStyle'); // Get from FlexForm	
		$css_readmore = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'css_readmore', 'sStyle'); // Get from FlexForm	
		$bg_uneven_rows = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'bg_uneven', 'sStyle'); // Get from FlexForm	
		$bg_even_rows = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'bg_even', 'sStyle'); // Get from FlexForm	
		$fg_uneven_rows = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'fg_uneven', 'sStyle'); // Get from FlexForm	
		$fg_even_rows = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'fg_even', 'sStyle'); // Get from FlexForm	
		
		$prefer_media = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'prefer_media', 'sMedia'); // Get from FlexForm
		$preferred_size = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'preferred_size', 'sMedia'); // Get from FlexForm
		$preftoosmall = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'preftoosmall', 'sMedia'); // Get from FlexForm
		$preftoobig = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'preftoobig', 'sMedia'); // Get from FlexForm
		$mediainfo1 = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'mediainfo1', 'sMedia'); // Get from FlexForm
		$mediainfo2 = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'mediainfo2', 'sMedia'); // Get from FlexForm
		$mediainfo3 = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'mediainfo3', 'sMedia'); // Get from FlexForm
		
		// Set CSS styles to <p> if not configured
		if (strlen(trim($css_header)) == 0) $css_header = "p";
		if (strlen(trim($css_subtitle)) == 0) $css_subtitle = "p";
		if (strlen(trim($css_body)) == 0) $css_body = "p";
		if (strlen(trim($css_readmore)) == 0) $css_readmore = "";
				
		if (strlen(trim($published_text)) == 0) $published_text = "published";
		if (strlen(trim($on_text)) == 0) $on_text = "on";
		if (strlen(trim($by_text)) == 0) $by_text = "by";
		if (strlen(trim($at_text)) == 0) $at_text = "at";
		//ini_set("display_errors", true);

		// Fetch, parse and combine the feeds
		require_once(PATH_typo3conf."ext/feedforward/pi1/class.tx_feedforward_feed.php");
		$content = '';
		$feeds = explode("\n", $feedurl);
		$feeditems_combined = array();
		foreach ($feeds as $remotefeed) {
			$feed = new tx_feedforward_feed();
			$feed->cache_dir = $cache_dir;
			$feed->cache_time = $cache_time;
			$feed->open($remotefeed, $parsepref);
			$feeditems_combined = array_merge($feeditems_combined, $feed->getItems());
		}

		// Sort the fetched feed items
		if (count($feeditems_combined) > 1)
			if ($sorting_pref == SORT_ASC) {
				usort($feeditems_combined, "sortItemsAsc");
			} else {
				usort($feeditems_combined, "sortItemsDesc");
			}
			
		$current_item = 0;
		foreach ($feeditems_combined as $item) {
			$current_item++;
			
			if ((strlen($maxitems) > 0) && ($current_item > $maxitems)) break;
			
			if (($prefer_media == 0) || (!isset($item->media))) {
				$style_override = "";
				if ($current_item % 2 == 0) {
					if (strlen($bg_even_rows) > 0) $style_override .= "background-color:".$bg_even_rows.";";
					if (strlen($fg_even_rows) > 0) $style_override .= "color:".$fg_even_rows.";";
				} else {
					if (strlen($bg_uneven_rows) > 0) $style_override .= "background-color:".$bg_uneven_rows.";";
					if (strlen($fg_uneven_rows) > 0) $style_override .= "color:".$fg_uneven_rows.";";
				}
				
				if (strlen($item->getPubDate()) > 0) {
					if (strlen($date_format) > 0) {
						$articledate = date($date_format, strtotime($item->getPubDate()));
					} else {
						$articledate = date("D, j M Y", strtotime($item->getPubDate()));
					}
				}
				
				switch ($titledate) {
					case 0: $title = $item->getTitle();
							break;
					case 1: $title = $item->getTitle()." (".$articledate.")";
							break;
					case 2: $title = $articledate.", ".$item->getTitle();
							break;
					default:$title = $item->getTitle();
				}
				if ($show_linktitle == 0) {
					$content = $content . addContent($title, $css_header, $style_override);
				} else {
					$elementStyle = "";
					if (strlen($style_override) >0) 
						$elementStyle = " style=\"".$style_override."\"";
					$content = $content . addContent("<a ".$elementStyle." href=\"".$item->getLink()."\" target=\"_blank\">".$title."</a>", $css_header, $style_override);
				}
	
				if ($show_subtitle == 1) {
					$urlInfo = parse_url(trim($item->getLink()));
					
					if (strlen($item->getPubDate()) > 0 && $suppress_date_subtitle == 0) {
						$pubDate = " ".$on_text." " . $articledate;
					} else {
						$pubDate = "";
					}
					
					if (strlen($item->getAuthor()) > 0 && $suppress_author_subtitle == 0) {
						$pubAuth = " ".$by_text." " . $item->getAuthor();
					} else {
						$pubAuth = "";
					}
		
					if (strlen($item->getLink()) > 0 && $suppress_site_subtitle == 0) {
						$pubHost = " ".$at_text." ".$urlInfo['host'];
					} else {
						$pubHost = "";
					}
					if ($suppress_date_subtitle != 1 || $suppress_author_subtitle != 1 || $suppress_site_subtitle != 1)
						$content = $content . "\n".addContent($published_text . $pubAuth . $pubDate . $pubHost, $css_subtitle, $style_override);
				}
				
				if ($show_content == 1) {
					$pub_description = html_entity_decode($item->getDescription());
					//$pub_description = $item->getDescription();
					//$pub_description = limit_words($pub_description, $item_length);
					if (strlen($itemlength) > 0) {
						switch ($length_unit) {
							case L_WORDS	: $pub_description = limit_words($pub_description, $itemlength);
											  break;
							case L_CHARS	: $pub_description = limit_chars($pub_description, $itemlength);
											  break;
						}
					}
					$content = $content . "\n".addContent($pub_description."<br>", $css_body, $style_override);
				}
				if ($show_readmore == 1) {
					if ($css_readmore != "") {
						$elementClass = " class=\"".$css_readmore."\"";
					} else {
						$elementClass = "";
					}
					$elementStyle = "";
					if (strlen($style_override) >0) 
						$elementStyle = " style=\"".$style_override."\"";
					$content = $content . addContent("<a".$elementClass.$elementStyle." href=\"".$item->getLink()."\" target=\"_blank\">".$readmore_text."</a>", $css_body, $style_override);
				}
	
				if ($show_subtitle == 1 || $show_content == 1 || $show_readmore == 1) {
					$content = $content .  "\n<br>";
				}
			} else {
				if (isset($item->media)) {
					$mediainfo = "";
					$mediainfo = $mediainfo . insertMediaInfo($item, $mediainfo1);
					$mediainfo = $mediainfo . insertMediaInfo($item, $mediainfo2);
					$mediainfo = $mediainfo . insertMediaInfo($item, $mediainfo3);

					//$item->media->sortThumbnails();
					$mediaobj = $item->media->retrieveMedia($preferred_size);
					//$mediathumb = "<img src='".$item->media->thumbnails[0]['link']."' width=".$item->media->thumbnails[0]['height']." height=".$item->media->thumbnails[0]['width']."/>";
					$mediathumb = "<img src='".$mediaobj['link']."'/>";
					//$content = $content . print_r($mediaobj, true) .  "\n<br>";
					$content = $content . $mediathumb .  "\n<br>";
					$content = $content . $mediainfo .  "\n<br>";
					
				} 
				$content = $content .  "\n<br>";
			}
		}

		return $this->pi_wrapInBaseClass($content);
	}
	
}

function sortItemsDesc($a, $b) {
	$tsa = strtotime($a->getPubDate());
	$tsb = strtotime($b->getPubDate());
	if ($tsa == $tsb) {
		return 0;
	}
	return ($tsa > $tsb) ? -1 : 1;
}

function sortItemsAsc($a, $b) {
	$tsa = strtotime($a->getPubDate());
	$tsb = strtotime($b->getPubDate());
	if ($tsa == $tsb) {
		return 0;
	}
	return ($tsa < $tsb) ? -1 : 1;
}

function addContent($additionalContent, $cssRef = "", $style_override = "") {
	$cnt = "";
	if (strlen($cssRef) > 0) {
		if (strlen($style_override) > 0) {
			$cnt = $cnt . "\n<".$cssRef." style=\"".$style_override."\">";
		} else {
			$cnt = $cnt . "\n<".$cssRef.">";
		}
	}
	$cnt = $cnt . $additionalContent;
	if (strlen($cssRef) > 0) {
		$cnt = $cnt . "</".$cssRef.">";
	}
	return $cnt;
}

function limit_words($text, $limit) {
	$text = strip_tags($text);
	$words = str_word_count($text, 2);
	$pos = array_keys($words);
	if (count($words) > $limit) {
		$text = substr($text, 0, $pos[$limit]) . ' ...';
	}
	return $text;
}

function limit_chars($text, $limit) {
	$text = strip_tags($text);
	return substr($text, 0, $limit) . ' ...';
}

function insertMediaInfo($item, $type) {
	switch($type) {
		case "Title"		: return "<span class=\"tx-feedforward-pi1-media-title\">Title: " . $item->media->getTitle(). "</span><br/>";
		case "Description"	: return "<span class=\"tx-feedforward-pi1-media-description\">Description: " . $item->media->getDescription(). "</span><br/>";
		case "Credit"		: return "<span class=\"tx-feedforward-pi1-media-credit\">Credit: " . $item->media->getCredit(). "</span><br/>";
		default: return "<br/>";
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/feedforward/pi1/class.tx_feedforward_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/feedforward/pi1/class.tx_feedforward_pi1.php']);
}

?>