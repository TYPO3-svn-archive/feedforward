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

class tx_feedforward_feeditem {

	var $title;
	var $link;
	var $description;
	var $categories = array();
	var $author;
	var $pubDate;
	var $media;
	
	function tx_feedforward_feeditem() {
	
	}
	
	function setTitle($title) {
		$this->title = $title;
	}
	
	function getTitle() {
		return $this->title;
	}
	
	function setLink($link) {
		$this->link = $link;
	}
	
	function getLink() {
		return $this->link;
	}

	function setDescription($description) {
		$this->description = $description;
	}
	
	function getDescription() {
		return $this->description;
	}

	function setAuthor($author) {
		$this->author = $author;
	}
	
	function getAuthor() {
		return $this->author;
	}

	function setPubDate($pubDate) {
		$this->pubDate = $pubDate;
	}
	
	function getPubDate() {
		return $this->pubDate;
	}
	
	function addCategory($category) {
		array_push($this->categories, $category);
	}
	
	function getCategories() {
		return $this->categories;
	}
	
	function addMedia($mediaobj) {
		$this->media = $mediaobj;
	}
	
	function addUnknown($unknown) {
		array_push($this->unknown, $unknown);
	}
	
	function getUnknown() {
		return $this->unknown;
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/feedforward/pi1/class.tx_feedforward_feeditem.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/feedforward/pi1/class.tx_feedforward_feeditem.php']);
}

?>