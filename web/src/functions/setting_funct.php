<?

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
# * FILE: /functions/setting_funct.php
# ----------------------------------------------------------------------------------------------------

function setting_new($name, $value)
{
    if ($name) {
        $settingObj = new Setting($name);
        if (!$settingObj->getString("name")) {
            $settingObj->setString("name", $name);
            $settingObj->setString("value", $value);
            $settingObj->Save($update = false);

            return true;
        }
    }

    return false;
}

function setting_get($name, &$value = null)
{
    if ($name) {
        unset($array_settings);
        $array_settings = setting_getSettingInformation($name);
        if ((is_array($array_settings)) && ($_SERVER['REQUEST_METHOD'] != "POST")) {
            return $value = $array_settings["value"];
        } else {
            $settingObj = new Setting($name);
            if ($settingObj->getString("name")) {
                return $value = $settingObj->getString("value");
            }
        }
    }
    $value = "";

    return false;
}

/**
 * Set a value for a setting or create if don't exists
 * @param $name
 * @param $value
 * @return bool
 */
function setting_set($name, $value)
{
    if (!$name) {
        return false;
    }

    $settingObj = new Setting($name);

    if (!$settingObj->getString("name")) {
        $settingObj->setString("name", $name);
        $settingObj->setString("value", $value);
        $settingObj->Save($update = false);

        return true;
    }

    $settingObj->setString("value", $value);
    $settingObj->Save();
    setting_constants();

    return true;
}

function setting_delete($name)
{
    if ($name) {
        $settingObj = new Setting($name);

        return $settingObj->Delete();
    }

    return false;
}

/*
 * Function to create a constant with table of setting Information
 */
function setting_constants()
{
    if (defined('SETTING_INFORMATION')) {
        return false;
    }
    unset($settingObj, $array_setting);

    $settingObj = new Setting();
    $array_setting = $settingObj->convertTableToArray();

    if (is_array($array_setting)) {
        define("SETTING_INFORMATION", serialize($array_setting));
    }

}

/*
 * Function to get information about language
 */
function setting_getSettingInformation($index)
{

    if (!defined('SETTING_INFORMATION')) {
        setting_constants();
    }

    $aux_setting_information = unserialize(SETTING_INFORMATION);
    $array_setting_information = $aux_setting_information[$index];

    if (is_array($array_setting_information)) {
        return $array_setting_information;
    } else {
        return false;
    }

}
