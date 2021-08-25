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
# * FILE: /sponsors/billing/authorize_webhook.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------
include("../../conf/loadconfig.inc.php");
include(EDIRECTORY_ROOT."/conf/payment_authorize.inc.php");

# ----------------------------------------------------------------------------------------------------
# VALIDATE FEATURE
# ----------------------------------------------------------------------------------------------------
if (AUTHORIZEPAYMENT_FEATURE != "on" || RECURRING_FEATURE != "on") {
    exit;
}

# ----------------------------------------------------------------------------------------------------
# Function to get one property of the content response
# ----------------------------------------------------------------------------------------------------
function parse_property($propertyName, $content)
{
    $propertyValue = substring_between($content, '<'.$propertyName.'>', '</'.$propertyName.'>');

    return $propertyValue;
}
# ----------------------------------------------------------------------------------------------------
# Helper function for parsing response
# ----------------------------------------------------------------------------------------------------
function substring_between($haystack, $start, $end)
{
    if (string_strpos($haystack, $start) === false || string_strpos($haystack, $end) === false) {
        return false;
    } else {
        $start_position = string_strpos($haystack, $start) + string_strlen($start);
        $end_position = string_strpos($haystack, $end);

        return string_substr($haystack, $start_position, $end_position - $start_position);
    }
}



if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $rawInput = @file_get_contents("php://input");
    $authorizePost = json_decode($rawInput);
    /* subscription charge */
    if ($authorizePost->eventType == "net.authorize.payment.authcapture.created" && $authorizePost->payload->entityName == "transaction") {

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        $db = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);

        $subscriptionID = $authorizePost->payload->id;

        //Update Log Payment date
        $eventDate = new \DateTime($authorizePost->eventDate);
        $sql = "SELECT * FROM Payment_Log WHERE transaction_id = '".$subscriptionID."' AND system_type = 'authorize' AND DATE_FORMAT(transaction_datetime, '%Y-%m-%d') != ".db_formatDate($eventDate->format("Y-m-d"))." ORDER BY transaction_datetime DESC limit 1";
        $r = $db->query($sql);

        if (mysqli_num_rows($r) > 0) {

            $row = mysqli_fetch_assoc($r);
            $paymentLogObj = new PaymentLog();
            $paymentLogObj->makeFromRow($row);
            $transactionLog["transaction_datetime"] = $eventDate->format("Y-m-d H:m:s");
            $paymentLogObj->MakeFromRow($transactionLog);


            //Renew items
            $modules = ["listing", "event", "classified", "article", "banner"];
            $x_login = PAYMENT_AUTHORIZE_LOGIN;
            $x_tran_key = PAYMENT_AUTHORIZE_TXNKEY;
            $content =
                "<?xml version=\"1.0\" encoding=\"utf-8\"?>".
                "<ARBGetSubscriptionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">".
                    "<merchantAuthentication>".
                        "<name>".$x_login."</name>".
                        "<transactionKey>".$x_tran_key."</transactionKey>".
                    "</merchantAuthentication>".
                    "<subscriptionId>".$subscriptionID."</subscriptionId>".
                "</ARBGetSubscriptionRequest>";

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, AUTHORIZE_POST_URL);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            if (RECURRING_FEATURE == "on") {
                curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: text/xml"]);
                curl_setopt($ch, CURLOPT_HEADER, 1);
            } else {
                $agent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)";
                $ref = "http://".$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"];
                curl_setopt($ch, CURLOPT_NOPROGRESS, 1);
                curl_setopt($ch, CURLOPT_VERBOSE, 1);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                curl_setopt($ch, CURLOPT_TIMEOUT, 120);
                curl_setopt($ch, CURLOPT_USERAGENT, $agent);
                curl_setopt($ch, CURLOPT_REFERER, $ref);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            $curl_response = curl_exec($ch);

            $curl_response = str_replace('"', "", $curl_response);

            curl_close($ch);

            $subscriptionInterval = PaymentLog::getParsedProperty("interval", $curl_response);

            $data["renewal"] = ((int)$subscriptionInterval == 1 ? "monthly" : "yearly");

            foreach ($modules as $module) {

                $sql = "SELECT {$module}_id, amount FROM Payment_".ucfirst($module)."_Log WHERE payment_log_id = '".$row["id"]."' ORDER BY id DESC limit 1";
                $result = $db->query($sql);

                if (mysqli_num_rows($result) > 0) {
                    while ($rowLog = mysqli_fetch_assoc($result)) {
                        $data["{$module}_id"][] = $rowLog["{$module}_id"];
                        $data["{$module}_price"][] = $rowLog["amount"];
                    }
                    Payments::renewItem($paymentLogObj, $data, $module);
                }
            }
            $paymentLogObj->Save();
            $paymentLogObj->sendNotification();

        }

    }
}
