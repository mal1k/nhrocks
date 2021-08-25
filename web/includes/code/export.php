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
	# * FILE: /includes/code/export.php
	# ----------------------------------------------------------------------------------------------------

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		extract($_POST);

        if ($ajax_action){ //charset fix
            header('Content-Type: text/html; charset='.EDIR_CHARSET, TRUE);
            header('Accept-Encoding: gzip, deflate');
            header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Cache-Control: post-check=0, pre-check', FALSE);
            header('Pragma: no-cache');
        }

		if ($ajax_action === 'generate_data') {
			$exportFilePath = EDIRECTORY_ROOT.'/custom/domain_'.SELECTED_DOMAIN_ID.'/export_files';
            if (!is_dir($exportFilePath)) {
                if (!mkdir($exportFilePath) && !is_dir($exportFilePath)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $exportFilePath));
                }

                echo 'error';
                exit;
            }

			if (defined(string_strtoupper($item_type).'_LIMIT')) {
				$_POST['item_limit'] = constant(string_strtoupper($item_type).'_LIMIT');
			} else {
				$_POST['item_limit'] = 10000;
			}

			if ($_POST['item_type'] === 'Listing') {
				$_POST['fields_excluded'] = 'image_id, discount_id, video_snippet, custom_checkbox0, custom_checkbox1, custom_checkbox2, custom_checkbox3, custom_checkbox4, custom_checkbox5, custom_checkbox6, custom_checkbox7, custom_checkbox8, custom_checkbox9, custom_dropdown0, custom_dropdown1, custom_dropdown2, custom_dropdown3, custom_dropdown4, custom_dropdown5, custom_dropdown6, custom_dropdown7, custom_dropdown8, custom_dropdown9, custom_text0, custom_text1, custom_text2, custom_text3, custom_text4, custom_text5, custom_text6, custom_text7, custom_text8, custom_text9, custom_short_desc0, custom_short_desc1, custom_short_desc2, custom_short_desc3, custom_short_desc4, custom_short_desc5, custom_short_desc6, custom_short_desc7, custom_short_desc8, custom_short_desc9, custom_long_desc0, custom_long_desc1, custom_long_desc2, custom_long_desc3, custom_long_desc4, custom_long_desc5, custom_long_desc6, custom_long_desc7, custom_long_desc8, custom_long_desc9, listingtemplate_id, importID';
			} else if ($_POST['item_type'] === 'Account') {
				$_POST['fields_excluded'] = 'account_id, updated, entered, password, importID, complementary_info';
			} else if ($_POST['item_type'] === 'Banner') {
				$_POST['fields_excluded'] = 'image_id, discount_id, target_window';
			} else if ($_POST['item_type'] === 'Event' || $_POST['item_type'] === 'Classified' || $_POST['item_type'] === 'Article') {
				$_POST['fields_excluded'] = 'discount_id, image_id';
			} else {
				$_POST['fields_excluded'] = '';
			}

			$_POST['export_from'] = 'browser';

			$exportObj = new Export($_POST);
			echo $exportObj->execute();
		} else if ($ajax_action === 'schedule_export') {
			$exportFilePath = EDIRECTORY_ROOT.'/custom/domain_'.SELECTED_DOMAIN_ID.'/export_files';
			if (!is_dir($exportFilePath)) {
				echo 2;
				exit;
			}

			$dbMain = db_getDBObject(DEFAULT_DB, true);

			$sqlExport = "SELECT finished FROM Control_Export_Listing WHERE type = 'csv - data' AND domain_id = $domain_id";

			$resExport = $dbMain->query($sqlExport);
			$rowExport = mysqli_fetch_assoc($resExport);
			if($rowExport['finished'] === 'Y'){
				$sqlUpdate =	"UPDATE Control_Export_Listing SET
									last_run_date = NOW(),
									scheduled = 'Y',
									running_cron = 'N',
									finished = 'N',
									filename = '$file_name',
									total_listing_exported = 0,
									last_listing_id = 0
								WHERE type = 'csv - data' AND domain_id = $domain_id";
				$dbMain->query($sqlUpdate);

				if ($dbMain->mysql_error) {
                    $return = 2;
                }
				else if (mysqli_affected_rows($dbMain->link_id)) {
                    $return = 0;
                }
			} else {
				$return = 1;
			}
			echo $return;
		} else if ($ajax_action === 'check_progress') {
			$fileName = 'export_'.str_replace('.zip', '', $file_name).'.progress';
			$filePath = EDIRECTORY_ROOT."/custom/domain_$domain_id/export_files/$fileName";
			if (file_exists($filePath)) {
				if (!$handle = fopen($filePath, 'r')) {
					$return = 'error';
				} else {
					$progress = fgets($handle);
					if (!fclose($handle)) {
						$return = 'error';
					} else {
						$last_progress = str_replace('%', '', $last_progress);
						if ($progress < $last_progress) {
                            $progress = $last_progress;
                        }
						$return = 'progress - '.$progress;

					}
				}
			} else {
				$return = 'waiting';
			}
			echo $return;
		}
		exit;
	}

    if (isset($_GET['download']) && $_GET['download']) {
        $exportFilePath = EDIRECTORY_ROOT.'/custom/domain_'.SELECTED_DOMAIN_ID.'/export_files';
        $fileName = $exportFilePath.'/'.$_GET['download'];

        $zipObj = new Zip();
        if ($_GET['action'] !== 'cron') {
    $_GET['action'] = false;
    }
        if ($zipObj->loadZipFromFile($fileName, $_GET['action'])) {
            $zipObj->sendZip($_GET['download']);
        }
        exit;
    }

    $exportFilePath = EDIRECTORY_ROOT.'/custom/domain_'.SELECTED_DOMAIN_ID.'/export_files';
	$errorExportFolder = false;
	if (!is_dir($exportFilePath)) {
        $errorExportFolder = true;
    }

	/**
	 * Scheduled Listing Export
	 */
	$dbMain = db_getDBObject(DEFAULT_DB, true);
	$sqlExport = 'SELECT finished, filename FROM Control_Export_Listing WHERE domain_id = '.SELECTED_DOMAIN_ID." AND type= 'csv - data'";
	$resExport = $dbMain->query($sqlExport);
	if(mysqli_num_rows($resExport)){
		$export = mysqli_fetch_assoc($resExport);
	}

	if($export['finished'] === 'N' && LISTING_SCALABILITY_OPTIMIZATION === 'on'){
		$exportFile = $export['filename'];
	} else {
		$exportFile = md5(uniqid(rand(), true)).'.zip';
		if(LISTING_SCALABILITY_OPTIMIZATION === 'on'){
			$exportedFileName = $export['filename'];
			$exportedFilePath = $exportFilePath.'/'.$exportedFileName;
			if (!$exportedFileName || !file_exists($exportedFilePath)) {
				$exportedFileName = '';
				$exportedFilePath = '';
			}
		}
	}

	/*
	 * Check if export is running - Listing
	 */
	$sql = 'SELECT finished, filename FROM Control_Export_Listing WHERE domain_id = '.SELECTED_DOMAIN_ID." AND type= 'csv'";
	$result = $dbMain->query($sql);
	if (mysqli_num_rows($result)) {
		$aux_export_running = mysqli_fetch_assoc($result);
		$aux_download_file_name = $aux_export_running['filename'];
	}
	if ($aux_export_running['finished'] === 'N' && LISTING_SCALABILITY_OPTIMIZATION === 'on') {
		$exportFileListing = $aux_export_running['filename'];
	} else {
        $exportFileListing = 'export_Listing_'.md5(uniqid(rand(), true)).'.csv';
		if (LISTING_SCALABILITY_OPTIMIZATION === 'on') {
			$old_export_file = $aux_export_running['filename'];
		}
	}

    /*
	 * Check if export is running - Event
	 */
    $sql = 'SELECT finished, filename FROM Control_Export_Event WHERE domain_id = '.SELECTED_DOMAIN_ID." AND type= 'csv'";
	$result = $dbMain->query($sql);
	if (mysqli_num_rows($result)) {
		$aux_export_runningEvent = mysqli_fetch_assoc($result);
		$aux_download_file_nameEvent = $aux_export_runningEvent['filename'];
	}
	if ($aux_export_runningEvent['finished'] === 'N' && EVENT_SCALABILITY_OPTIMIZATION === 'on') {
		$exportFileEvent = $aux_export_runningEvent['filename'];
	} else {
        $exportFileEvent = 'export_Event_'.md5(uniqid(rand(), true)).'.csv';
		if (EVENT_SCALABILITY_OPTIMIZATION === 'on') {
			$old_export_fileEvent = $aux_export_runningEvent['filename'];
		}
	}

    /*
     * Download exported files
     */
    $url_redirect = DEFAULT_URL.'/'.SITEMGR_ALIAS.'/content/export/index.php';

    extract($_GET);
    extract($_POST);

    if ($action === 'downFile' && $file && $displayName) {
        $filePath = EDIRECTORY_ROOT.'/custom/domain_'.SELECTED_DOMAIN_ID.'/import_files/'.$file;
        if (file_exists($filePath)) {
            system_downloadFile($filePath, $displayName, 'csv');
        } else {
            $messageStyle = 'warning';
            $message = system_showText(LANG_SITEMGR_EXPORT_DOWNLOAD_ERROR);
        }
    } elseif ($action === 'deleteFile' && $file) {
        $filePath = EDIRECTORY_ROOT.'/custom/domain_'.SELECTED_DOMAIN_ID.'/import_files/'.$file;
        if (@unlink($filePath)) {
            header('Location: '.$url_redirect.'?message=1');
            exit;
        }

        $messageStyle = 'warning';
        $message = system_showText(LANG_SITEMGR_EXPORT_DELETE_ERROR);
    }

    //Success Message
    if ($message == 1) {
        $messageStyle = 'success';
        $message = system_showText(LANG_SITEMGR_EXPORT_DELETED);
    }

	$exportFiles = export_getFileList();
