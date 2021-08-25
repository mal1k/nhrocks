<?php

	/*==================================================================*\
	######################################################################
	#                                                                    #
	# Copyright 2018 Arca Solutions, Inc. All Rights Reserved.           #
	#                                                                    #
	# This file may not be redistributed in whole or part.               #
	# eDirectory is licensed on a per-domain basis.                      #
	#                                                                    #
	# ---------------- eDirectory IS NOT FREE SOFTWARE ----------------- #
	#                                                                    #
	# http://www.edirectory.com | http://www.edirectory.com/license.html #
	######################################################################
	\*==================================================================*/

	# ----------------------------------------------------------------------------------------------------
	# * FILE: /classes/class_gallery.php
	# ----------------------------------------------------------------------------------------------------

	class Gallery extends Handle {

		var $id;
		var $account_id;
		var $title;
		var $entered;
		var $updated;
		var $image;

        public function __construct($var = '', $domain_id = false, $main_image = false) {
			if (is_numeric($var) && ($var)) {
				$dbMain = db_getDBObject(DEFAULT_DB, true);
				if ($domain_id){
					$dbObj = db_getDBObjectByDomainID($domain_id, $dbMain);
				} else if (defined("SELECTED_DOMAIN_ID")) {
					$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
				} else {
					$dbObj = db_getDBObject();
				}

				unset($dbMain);
				$sql = "SELECT * FROM Gallery WHERE id = $var";
				$row = mysqli_fetch_array($dbObj->query($sql));
				$sql = "SELECT * FROM Gallery_Image WHERE gallery_id = $var ".(!$main_image ? "AND image_default <> 'y'" : "")." ORDER BY ".($main_image ? "image_default DESC, " : "")."id";
				$r = $dbObj->query($sql);
				$i = 0;
				while ($row_aux = mysqli_fetch_array($r)) {
                    unset($imageAux);
                    $imageAux = new Image($row_aux['image_id']);

                    if ($imageAux->imageExists()) {
                        $image[$i]['id'] = $row_aux['id'];
                        $image[$i]['image_id'] = $row_aux['image_id'];
                        $image[$i]['image_caption'] = $row_aux['image_caption'];
                        $image[$i]['alt_caption'] = $row_aux['alt_caption'];
                        $image[$i]['image_default'] = $row_aux['image_default'];
                        $image[$i]['order'] = $row_aux['order'];
                        $sql = "SELECT * FROM Image WHERE id = $row_aux[image_id]";
                        $row_aux = mysqli_fetch_array($dbObj->query($sql));
                        $image[$i]['width'] = $row_aux['width'];
                        $image[$i]['height'] = $row_aux['height'];
                        $i++;
                    }
				}
				$this->makeFromRow($row, $image);
			} else {
                if (!is_array($var)) {
                    $var = array();
                }
				$this->makeFromRow($var);
			}
		}

		function getAllImages($gallery_id, $domain_id = false) {
			if (is_numeric($gallery_id) && ($gallery_id)) {
				$dbMain = db_getDBObject(DEFAULT_DB, true);
				if ($domain_id){
					$dbObj = db_getDBObjectByDomainID($domain_id, $dbMain);
				} else if (defined("SELECTED_DOMAIN_ID")) {
					$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
				} else {
					$dbObj = db_getDBObject();
				}

				unset($dbMain);
				$sql = "SELECT * FROM Gallery WHERE id = $gallery_id";
				$row = mysqli_fetch_array($dbObj->query($sql));
				$sql = "SELECT * FROM Gallery_Image WHERE gallery_id = $gallery_id AND image_id IS NOT NULL ORDER BY image_default DESC, id";

				$r = $dbObj->query($sql);
				$i = 0;
				while ($row_aux = mysqli_fetch_array($r)) {
					$image[$i]['id'] = $row_aux['id'];
					$image[$i]['image_id'] = $row_aux['image_id'];
					$image[$i]['image_caption'] = $row_aux['image_caption'];
					$image[$i]['alt_caption'] = $row_aux['alt_caption'];
					$image[$i]['image_default'] = $row_aux['image_default'];
					$image[$i]['order'] = $row_aux['order'];
					$sql = "SELECT * FROM Image WHERE id = $row_aux[image_id]";
					$row_aux = mysqli_fetch_array($dbObj->query($sql));
					$image[$i]['width'] = $row_aux['width'];
					$image[$i]['height'] = $row_aux['height'];
					$i++;
				}
				return $image;
			} else {
				return false;
			}
		}

		function getImagesCount() {
			return count($this->image);
		}

		function makeFromRow($row='', $image=null) {
			$this->image = $image;
			$row['id'] ? $this->id = $row['id'] : $this->id = 0;
			$row['account_id'] ? $this->account_id = $row['account_id'] : $this->account_id = 0;
			$row['entered'] ? $this->entered = $row['entered'] : $this->entered = 0;
			$row['updated'] ? $this->updated = $row['updated'] : $this->updated = 0;
			$row['title'] ? $this->title = $row['title'] : $this->title = 'NO NAME';
		}

		function Save() {
			$this->prepareToSave();
			$dbMain = db_getDBObject(DEFAULT_DB, true);
			if (defined("SELECTED_DOMAIN_ID")) {
				$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
			} else {
				$dbObj = db_getDBObject();
			}
			unset($dbMain);
			if ($this->id) {
				$sql = "UPDATE Gallery SET"
					. " title = $this->title,"
					. " account_id = $this->account_id,"
					. " updated = NOW()"
					. " WHERE id = $this->id";
				$dbObj->query($sql);
			} else {
				$sql = "INSERT INTO Gallery"
					. " (title,"
					. " account_id,"
					. " entered,"
					. " updated)"
					. " VALUES"
					. " ($this->title, "
					. " $this->account_id, "
					. " NOW(), "
					. " NOW())";
				$dbObj->query($sql);
				$this->id = ((is_null($___mysqli_res = mysqli_insert_id($dbObj->link_id))) ? false : $___mysqli_res);
			}
			$this->prepareToUse();
		}

		function Delete($domain_id = false) {
			$dbMain = db_getDBObject(DEFAULT_DB, true);
			if ($domain_id) {
				$dbObj = db_getDBObjectByDomainID($domain_id, $dbMain);
			} else {
				if (defined("SELECTED_DOMAIN_ID")) {
					$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
				} else {
					$dbObj = db_getDBObject();
				}
				unset($dbMain);
			}

			$sql = "SELECT * FROM Gallery_Image WHERE gallery_id = $this->id";
			$r = $dbObj->query($sql);
			while ($row = mysqli_fetch_array($r)) {
				/* Set images objects */
			    $imageObj = new Image($row['image_id']);

                /* Remove foreing key */
                $sql = sprintf("UPDATE Gallery_Image SET image_id = NULL 
						WHERE image_id = '%d'", $row['image_id']);
                $dbObj->query($sql);

                /* Delete images */
                $imageObj->Delete($domain_id);
			}

			$sql = "DELETE FROM Gallery_Image WHERE gallery_id = $this->id";
			$dbObj->query($sql);
			$sql = "DELETE FROM Gallery WHERE id = $this->id";
			$dbObj->query($sql);
			$sql = "DELETE FROM Gallery_Item WHERE gallery_id = $this->id";
			$dbObj->query($sql);
		}

		// like prepareToSave but only used by AddImage and EditImage
		function getGalleryToSave($vars='') {
			if($vars) {
				foreach($vars as $key => $value)
                    if (is_string($value))
                        if ((!strstr($value, "\'")) && (!strstr($value, "\\\"")) && (!strstr($value, "\\")))
                            $vars[$key] = addslashes($value);
				$result = $vars;
			} else $result = 0;
			return $result;
		}

		// like prepareToUse but only used by AddImage and EditImage
		function getGalleryToUse($vars='') {
			if($vars) {
				foreach($vars as $key => $value) $vars[$key] = stripslashes($value);
				$result = $vars;
			} else $result = 0;
			return $result;
		}

		function AddImage($row, $domain_id = false) {
			$dbMain = db_getDBObject(DEFAULT_DB, true);
			if ($domain_id){
				$dbObj = db_getDBObjectByDomainID($domain_id, $dbMain);
			} else if (defined("SELECTED_DOMAIN_ID")) {
				$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
			} else {
				$dbObj = db_getDBObject();
			}

			unset($dbMain);
			$row = $this->getGalleryToSave($row);
            $row[image_id] = $row[image_id] == 0 ? 'NULL' : $row[image_id];
            $sql = "INSERT INTO Gallery_Image"
				. " (gallery_id,
					image_id,
					image_caption,
					alt_caption,
					image_default)"
				. " VALUES"
				. " ($this->id,"
				. " $row[image_id],"
				. " '$row[image_caption]',"
				. " '$row[alt_caption]',"
				. " '$row[image_default]')";
			$dbObj->query($sql);
			$row = $this->getGalleryToUse($row);
		}

		function EditImage($row) {
			$dbMain = db_getDBObject(DEFAULT_DB, true);
			if (defined("SELECTED_DOMAIN_ID")) {
				$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
			} else {
				$dbObj = db_getDBObject();
			}
			unset($dbMain);
			$row = $this->getGalleryToSave($row);
            $row[image_id] = $row[image_id] == 0 ? 'NULL' : $row[image_id];
			$sql = "UPDATE Gallery_Image SET"
					. " gallery_id = $this->id,"
					. " image_id = $row[image_id],"
					. " image_caption = '$row[image_caption]',"
					. " alt_caption = '$row[alt_caption]',"
					. " image_default = '$row[image_default]'"
					. " WHERE id = $row[id]";
			$dbObj->query($sql);
			$row = $this->getGalleryToUse($row);
		}

		function DeleteImage($id) {
			$dbMain = db_getDBObject(DEFAULT_DB, true);
			if (defined("SELECTED_DOMAIN_ID")) {
				$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
			} else {
				$dbObj = db_getDBObject();
			}
//			$dbMain->close();
			unset($dbMain);
			$sql = "SELECT * FROM Gallery_Image WHERE image_id = $id AND gallery_id = $this->id";
			$row = mysqli_fetch_array($dbObj->query($sql));

			$sql = "DELETE FROM Gallery_Image WHERE image_id = $id";
			$dbObj->query($sql);

			$image = new Image($row["image_id"]);
			$image->Delete();
		}

		function deletePerAccount($account_id = 0, $domain_id = false) {
			if (is_numeric($account_id) && $account_id > 0) {
				$dbMain = db_getDBObject(DEFAULT_DB, true);
				if ($domain_id) {
					$dbObj = db_getDBObjectByDomainID($domain_id, $dbMain);
				} else {
					if (defined("SELECTED_DOMAIN_ID")) {
						$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
					} else {
						$dbObj = db_getDBObject();
					}
					unset($dbMain);
				}
				$sql = "SELECT * FROM Gallery WHERE account_id = $account_id";
				$result = $dbObj->query($sql);
				while ($row = mysqli_fetch_array($result)) {
					$this->makeFromRow($row);
					$this->Delete($domain_id);
				}
			}
		}

		function getItemTitle () {
			$dbMain = db_getDBObject(DEFAULT_DB, true);
			if ($domain_id) {
				$dbObj = db_getDBObjectByDomainID($domain_id, $dbMain);
			} else {
				if (defined("SELECTED_DOMAIN_ID")) {
					$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
				} else {
					$dbObj = db_getDBObject();
				}
				unset($dbMain);
			}

			$sqlGI = "SELECT `item_id`, `item_type` FROM `Gallery_Item` WHERE `gallery_id` = ".db_formatNumber($this->id);
			$resGI = $dbObj->Query($sqlGI);
			if (mysqli_num_rows($resGI) > 0) {
				$rowGI = mysqli_fetch_assoc($resGI);
				$sqlI = "SELECT `title` FROM `".string_ucwords($rowGI["item_type"])."` WHERE `id` = ".db_formatNumber($rowGI["item_id"]);
				$resI = $dbObj->Query($sqlI);
				if (mysqli_num_rows($resI) > 0) {
					$rowI = mysqli_fetch_assoc($resI);
					$this->title = $rowI["title"];
				}
			}
		}

        function CleanDefaultImage ($domain_id) {
			$dbMain = db_getDBObject(DEFAULT_DB, true);
			if ($domain_id) {
				$dbObj = db_getDBObjectByDomainID($domain_id, $dbMain);
			} else {
				if (defined("SELECTED_DOMAIN_ID")) {
					$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
				} else {
					$dbObj = db_getDBObject();
				}
				unset($dbMain);
			}

			$sqlGI = "SELECT `item_id`, `item_type` FROM `Gallery_Item` WHERE `gallery_id` = ".db_formatNumber($this->id);
			$resGI = $dbObj->Query($sqlGI);
			if (mysqli_num_rows($resGI) > 0) {
				$rowGI = mysqli_fetch_assoc($resGI);
                $itemStr = string_ucwords($rowGI["item_type"]);
                $itemObj = new $itemStr($rowGI["item_id"]);
                $itemObj->setString("image_id", 'NULL');
                $itemObj->save();
			}
		}
	}
