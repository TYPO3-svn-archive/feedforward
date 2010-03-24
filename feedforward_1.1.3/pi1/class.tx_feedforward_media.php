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


class tx_feedforward_media {

	var $title;
	var $description;
	var $credit;
	
	var $titleHtml = false;
	var $descriptionHtml = false;

	var $content = array();
	var $thumbnails = array();
	
	public function setTitle($title) {
		$this->title = $title;
	}
	
	public function getTitle() {
		return $this->title;
	}

	public function setDescription($description) {
		$this->description = $description;
	}
	
	public function getDescription() {
		return $this->description;
	}

	public function setTitleHtml($titleHtml) {
		$this->titleHtml = $titleHtml;
	}
	
	public function isTitleHtml() {
		return $this->titleHtml;
	}

	public function setDescriptionHtml($descriptionHtml) {
		$this->descriptionHtml = $descriptionHtml;
	}
	
	public function isDescriptionHtml() {
		return $this->descriptionHtml;
	}

	public function addContent($link, $width, $height, $type, $medium) {
		//$item = array("link" => $link, "width" => str_pad($width, 5, "0", STR_PAD_LEFT), "height" => str_pad($height, 5, "0", STR_PAD_LEFT), "type" => $type, "medium" => $medium);
		$item = array("link" => $link, "width" => $width, "height" => $height, "type" => $type, "medium" => $medium);
		array_push($this->content, $item);
	}
	
	public function addThumbnail($link, $width, $height) {
		$type = "";
		$medium = "";
		//$item = array("link" => $link, "width" => str_pad($width, 5, "0", STR_PAD_LEFT), "height" => str_pad($height, 5, "0", STR_PAD_LEFT), "type" => $type, "medium" => $medium);
		$item = array("link" => $link, "width" => $width, "height" => $height, "type" => $type, "medium" => $medium);
		array_push($this->thumbnails, $item);
	}

	public function setCredit($credit) {
		$this->credit = $credit;
	}
	
	public function getCredit() {
		return $this->credit;
	}
	
	public function sortContent() {
		foreach($this->content as $key => $row) {
			$height[$key] = $row['height'];
			$width[$key] = $row['width'];
			array_multisort($height, SORT_DESC, $width, SORT_DESC, $this->content);
		}
	}

	public function sortThumbnails() {
		foreach($this->thumbnails as $key => $row) {
			$height[$key] = $row['height'];
			$width[$key] = $row['width'];
			array_multisort($height, SORT_DESC, $width, SORT_DESC, $this->thumbnails);
		}
	}

	public function retrieveMedia($preferred_size) {
		//$preferred_size_cnv = str_pad($preferred_size, 5, "0", STR_PAD_LEFT);
		//$mediaarray = array_merge($this->content, $this->thumbnails);
		$mediaarray = $this->thumbnails;
		foreach($mediaarray as $key => $row) {
			$height[$key] = $row['height'];
			$width[$key] = $row['width'];
		}
		array_multisort($height, SORT_DESC, SORT_NUMERIC, $width, SORT_DESC, SORT_NUMERIC, $mediaarray);
		//var_dump($mediaarray);
		//var_dump("<br>\n<br>\n");
		$lastIndex = 0;
		$lastSize = 64000;
		
		
		foreach($mediaarray as $media) {
			if ($media['height'] > $media['width']) {
				$currentSize = $media['height'];
			} else {
				$currentSize = $media['width'];
			}

			if (intval($preferred_size) > intval($currentSize)) {
				if (($lastSize-$preferred_size) <= ($preferred_size-$currentSize)) {
				}
			} else {
				$lastSize = $currentSize;
			}

		}

		foreach($mediaarray as $media) {
			if ($media['height'] > $media['width']) {
				if (intval($preferred_size_cnv) > intval($media['height'])) {
					return $mediaarray[$lastIndex-1];
				} else {
					$lastSize = $media['height'];
				}
			} else {
				if (intval($preferred_size_cnv) > intval($media['width'])) {
					return $mediaarray[$lastIndex-1];
				} else {
					$lastSize = $media['width'];
				}
			}
			$lastIndex++;
		}
		return $mediaarray[$lastIndex-1];
	}

}

?>