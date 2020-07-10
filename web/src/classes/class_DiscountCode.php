<?php

/**
 * @copyright Copyright 2018 Arca Solutions, Inc.
 * @author Arca Solutions, Inc.
 * @version 8.0.00
 */
class DiscountCode extends Handle
{

    /**
     * @var integer
     */
    public $id;
    /**
     * @var float
     */
    public $amount;
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $status;
    /**
     * @var date
     */
    public $expire_date;
    /**
     * @var string
     */
    public $recurring;
    /**
     * @var string
     */
    public $listing;
    /**
     * @var string
     */
    public $event;
    /**
     * @var string
     */
    public $banner;
    /**
     * @var string
     */
    public $classified;
    /**
     * @var string
     */
    public $article;

    /**
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @param string|array $var
     */
    public function __construct($var)
    {
        if (is_array($var)) {
            $this->makeFromRow($var);

            return;
        }

        if (empty($var)) {
            $this->makeFromRow();

            return;
        }

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $db = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $db = db_getDBObject();
        }

        $sql = 'SELECT * FROM Discount_Code WHERE id = BINARY '.db_formatString($var);
        $row = mysqli_fetch_array($db->query($sql));
        if (is_array($row)) {
            $this->makeFromRow($row);
            return;
        }
        $this->makeFromRow();
    }

    /**
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @param array $row
     */
    public function makeFromRow(array $row = [])
    {
        $this->x_id = $row['x_id'] ?: 0;
        $this->id = $row['id'] ?: ($this->id ?: '');
        $this->amount = $row['amount'] ?: ($this->amount ?: 0);
        $this->type = $row['type'] ?: ($this->type ?: 'monetary value');
        $this->status = $row['status'] ?: ($this->status ?: 'A');
        $this->expire_date = $row['expire_date'] ?: ($this->expire_date ?: 0);
        $this->recurring = $row['recurring'] ?: ($this->recurring ?: 'no');
        $this->listing = $row['listing'] ?: '';
        $this->event = $row['event'] ?: '';
        $this->banner = $row['banner'] ?: '';
        $this->classified = $row['classified'] ?: '';
        $this->article = $row['article'] ?: '';

        if($this->type === 'percentage') {
            $this->amount = (int)$this->amount;
        }
    }

    /**
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     */
    public function Save()
    {
        $this->prepareToSave();

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }

        if ($this->x_id) {
            $sql = 'UPDATE Discount_Code SET'
                ." id = $this->id,"
                ." amount = $this->amount,"
                ." type = $this->type,"
                ." status = $this->status,"
                ." expire_date = $this->expire_date,"
                ." listing = $this->listing,"
                ." event = $this->event,"
                ." banner = $this->banner,"
                ." classified = $this->classified,"
                ." article = $this->article,"
                ." recurring = $this->recurring"
                ." WHERE id = $this->x_id";
            $dbObj->query($sql);
        } else {
            $sql = 'INSERT INTO Discount_Code'
                .' (id, amount, type, status, expire_date, listing, event, banner, classified, article, recurring)'
                .' VALUES'
                ." ($this->id, $this->amount, $this->type, $this->status, $this->expire_date, $this->listing, $this->event, $this->banner, $this->classified, $this->article, $this->recurring)";
            $dbObj->query($sql);
        }

        $this->prepareToUse();

        $this->updateStripe($this->id, 'createcoupons');
    }

    public function updateStripe($id, $action)
    {

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }
        unset($dbMain);

        //Update coupons on Stripe
        $sql = "SELECT * FROM Setting WHERE name LIKE 'payment_stripe%'";
        $result = $dbObj->query($sql);

        while ($row = mysqli_fetch_assoc($result)) {
            switch ($row['name']) {
                case 'payment_stripe_apikey'            :
                    $stripekey = crypt_decrypt($row['value']);
                    break;
                case 'payment_stripe_status'            :
                    $stripestatus = $row['value'];
                    break;
            }
        }

        if ($stripestatus === 'on') {
            $data = ['id' => $id];
            StripeInterface::StripeRequest($action, $stripekey, $data);
        }

    }

    /**
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     */
    public function Delete()
    {

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }

        unset($dbMain);
        $sql = "DELETE FROM Discount_Code WHERE id = '$this->id'";
        $dbObj->query($sql);
        unset($dbObj);

        //Update coupons on Stripe
        $this->updateStripe($this->id, 'deletecoupon');

    }

}
