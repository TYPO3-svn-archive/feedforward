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
require_once(PATH_typo3conf."ext/feedforward/pi1/class.tx_feedforward_media.php");

/* Parser constants */
define("IT_ATOM", 1);
define("IT_RSS", 2);
define("P_DESC", 0);
define("P_CONT", 1);

class tx_feedforward_parser {
	
	/* Data fields to hold general feed information*/
	var $title;
	var $link;
	var $description;
	var $language;
	var $image = array();
	var $generator;
	var $docs;
	var $lastBuildDate;
	var $mediaobj;
	var $mediaEnclosure;
	
	/* Control fields */
	var $session = null;			//CURL session
	var $errorMessage = "";			//Message in case of an error
	var $feedAddress = "";			//Supplied address of the feed
	var $feedBody = "";				//Retrieved content of the feed
    var $feedEncoding = "";
	var $parser;
	var $item = null;
	var $skipElements = 
		array("feed", "rss", "channel");
    var $data;
    var $stack = array();
    var $readingContent = false;
    var $itemType = 0;
    var $parse_preference = 0;
    	
	function tx_feedforward_parser($addr, $parse_preference = 0) {
		$this->feedAddress = $addr;
		$this->parse_preference = $parse_preference;
	}

	function open($body) {
		$this->feedBody = $body;
		$this->parseToArray();
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

	function parseToArray() {
	    $rx = '/<?xml.*encoding=[\'"](.*?)[\'"].*?>/m';

		if (preg_match($rx, $this->feedBody, $m)) {
			$this->feedEncoding = strtoupper($m[1]);
		} else {
			$this->feedEncoding = "UTF-8";
		}
		//echo "<script type=\"text/javascript\" language=\"JavaScript\">alert(\"".$this->feedEncoding."\");</script>";
		$this->parser = xml_parser_create($this->feedEncoding);
        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, 'startElement', 'endElement');
        xml_set_character_data_handler($this->parser, 'dataElement');
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
		//$encoding_list = "ISO-8859-1, ISO-8859-15, UTF-8, cp866, cp1251, cp1252, KOI8-R, BIG5, GB2312, BIG5-HKSCS, Shift_JIS, EUC-JP";
		//$this->feedEncoding = mb_detect_encoding($this->feedBody);
		//if ($this->feedEncoding == "ASCII") $this->feedEncoding = "ISO-8859-1";

		$lines = explode("\n",$this->feedBody);
		foreach ($lines as $val) {
			if (trim($val) == '')
				continue;
			$data = $val . "\n";
			if (!xml_parse($this->parser, $data)) {
				$this->setErrorMessage(sprintf('XML error at line %d column %d',
				xml_get_current_line_number($this->parser),
				xml_get_current_column_number($this->parser)));
			}
		}
	}

    function startElement($parser, $name, $attr) {
    	if ($name == "enclosure") $this->mediaEnclosure = $attr['url'];
		if ($name == "entry" || $name == "item") {
			$this->item = new tx_feedforward_feeditem();
			switch($name) {
				case "entry"	: $this->itemType = IT_ATOM;
								  break;
				case "item"		: $this->itemType = IT_RSS;
								  break;
			}
		}
		if ($this->item != null) {
			if ($name == "content:encoded" || $name == "content" || $name == "description") {
				$this->readingContent = true;
			} else
				if ($name == 'media:group') $this->mediaobj = new tx_feedforward_media();
				switch($name) {
					case "media:content": 	if (isset($this->mediaEnclosure)) {
												$this->mediaobj->addContent($this->mediaEnclosure, $attr['height'], $attr['width'], $attr['type'], $attr['medium']);
											} else {
												$this->mediaobj->addContent($attr['url'], $attr['height'], $attr['width'], $attr['type'], $attr['medium']);
											}
									  		break;
					case "media:thumbnail":	$this->mediaobj->addThumbnail($attr['url'], $attr['height'], $attr['width']);
									  		break;
					case "media:description": 
											if ($attr['type'] != "plain") {
												$this->mediaobj->setDescriptionHtml(true);
											} else {
												$this->mediaobj->setDescriptionHtml(false);
											}
									  		break;
					case "media:title":		if ($attr['type'] != "plain") {
												$this->mediaobj->setTitleHtml(true);
											} else {
												$this->mediaobj->setTitleHtml(false);
											}
									  		break;
				}
				if ($this->readingContent) {
					$this->item->setDescription($this->item->getDescription().$this->data);
					$this->data = "";
					if ($name == "a") {
						$urlregex = "^(https?|ftp)\:\/\/([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)*(\:[0-9]{2,5})?(\/([a-z0-9+\$_-]\.?)+)*\/?(\?[a-z+&\$_.-][a-z0-9;:@/&%=+\$_.-]*)?(#[a-z_.-][a-z0-9+\$_.-]*)?\$";
						if (eregi($urlregex, trim($attr['href']))) {
						} else {
							$feedAddr = parse_url($this->feedAddress);
							$feedHost = "http://".$feedAddr['host'];
							$attr['href'] = $feedHost . $attr['href'];
						}
					}
					$attribs = "";
					while (list ($key, $val) = each ($attr)) {
						$attribs = $attribs . $key . "=\"" . $val . "\",";
					}
					$this->item->setDescription($this->item->getDescription()."<".$name." ".$attribs.">");
				}
		}
    }

    function endElement($parser, $name)    {
		if ($name == "entry" || $name == "item") {
			array_push($this->stack, $this->item);
			unset($this->mediaEnclosure);
			unset($this->item);
		} else {
			if ($this->item != null) {
				switch ($name) {
					case 'title' :			$this->item->setTitle(htmlentities(html_entity_decode($this->data, ENT_COMPAT, $this->feedEncoding), ENT_COMPAT, $this->feedEncoding));
											break;
					case 'link' :			if ($this->itemType == IT_RSS) $this->item->setLink($this->data);
											break;
					case 'id' :				if ($this->itemType == IT_ATOM) $this->item->setLink($this->data);
											break;
					case 'description' :	if (($this->parse_preference == P_DESC) || (strlen($this->item->getDescription()) == 0))
												$this->item->setDescription($this->item->getDescription().htmlentities(html_entity_decode($this->data, ENT_COMPAT, $this->feedEncoding), ENT_COMPAT, $this->feedEncoding));
											$this->readingContent = false;
											break;
					case 'content' :		if (($this->parse_preference == P_CONT) || (strlen($this->item->getDescription()) == 0))
												$this->item->setDescription($this->item->getDescription().htmlentities(html_entity_decode($this->data, ENT_COMPAT, $this->feedEncoding), ENT_COMPAT, $this->feedEncoding));
											$this->readingContent = false;
											break;
					case 'content:encoded':	if (($this->parse_preference == P_CONT) || (strlen($this->item->getDescription()) == 0))
												$this->item->setDescription($this->item->getDescription().htmlspecialchars_decode(htmlentities(html_entity_decode($this->data, ENT_COMPAT, $this->feedEncoding), ENT_COMPAT, $this->feedEncoding)));
											$this->readingContent = false;
											break;
					case 'category' :		$this->item->addCategory($this->data);
											break;
					case 'author' :			$this->item->setAuthor($this->data);
											break;
					case 'published' :		$this->item->setPubDate($this->data);
											break;
					case 'pubDate' :		$this->item->setPubDate($this->data);
											break;
					case 'media:group' :	$this->item->addMedia($this->mediaobj);
											break;
					case 'media:title':		$this->mediaobj->setTitle($this->data);
											break;
					case 'media:description':$this->mediaobj->setDescription($this->data);
											break;
					case 'media:credit' :	$this->mediaobj->setCredit($this->data);
											break;

					default :				if ($this->readingContent) {
										    	$cnt = $this->item->getDescription().htmlentities(html_entity_decode($this->data, ENT_COMPAT, $this->feedEncoding), ENT_COMPAT, $this->feedEncoding)."</".$name.">";
										    	$this->item->setDescription($cnt);
											}
				}
			} else {
				if (!in_array($name, $this->skipElements)) {
					switch ($name) {
						case 'title' :			$this->title = $this->data;
												break;
						case 'link' :			$this->link = $this->data;
												break;
						case 'id' :				$this->link = $this->data;
												break;
						case 'content' :		$this->description = $this->data;
												break;
						case 'content:encoded':	$this->description = $this->data;
												break;
						case 'language' :		$this->language = $this->data;
												break;
						case 'image' :			break;
						case 'generator' :		$this->generator = $this->data;
												break;
						case 'docs' :			$this->docs = $this->data;
												break;
						case 'lastBuildDate' :	$this->lastBuildDate = $this->data;
												break;
					}
				}
			}
		}
		$this->data = "";
    }

    function dataElement($parser, $data) {
    	$this->data = $this->data . htmlentities(html_entity_decode($data, ENT_COMPAT, $this->feedEncoding), ENT_COMPAT, $this->feedEncoding);
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

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/feedforward/pi1/class.tx_feedforward_parser.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/feedforward/pi1/class.tx_feedforward_parser.php']);
}

?>