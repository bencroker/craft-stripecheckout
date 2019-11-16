<?php
/**
 * Stripe Checkout plugin for Craft CMS 3.x
 *
 * Bringing the power of Stripe Checkout to your Craft templates.
 *
 * @link      https://github.com/jalendport/craft-stripecheckout
 * @copyright Copyright (c) 2018 Jalen Davenport
 */

namespace jalendport\stripecheckout\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class ChargeQuery extends ElementQuery
{
    public $stripeId;

    public $email;

    public $live;

    public $chargeStatus;

    public $paid;

    public $refunded;

    public $amount;

    public $amountRefunded;

    public $currency;

    public $description;

    public $failureCode;

    public $failureMessage;

    public $data;

    public function stripeId($value)
    {
        $this->stripeId = $value;

        return $this;
    }

    public function email($value)
    {
        $this->email = $value;

        return $this;
    }

    public function live($value)
    {
        $this->live = $value;

        return $this;
    }

    public function chargeStatus($value)
    {
        $this->chargeStatus = $value;

        return $this;
    }

    public function paid($value)
    {
        $this->paid = $value;

        return $this;
    }

    public function refunded($value)
    {
        $this->refunded = $value;

        return $this;
    }

    public function amount($value)
    {
        $this->amount = $value;

        return $this;
    }

    public function amountRefunded($value)
    {
        $this->amountRefunded = $value;

        return $this;
    }

    public function currency($value)
    {
        $this->currency = $value;

        return $this;
    }

    public function description($value)
    {
        $this->description = $value;

        return $this;
    }

    public function failureCode($value)
    {
        $this->failureCode = $value;

        return $this;
    }

    public function failureMessage($value)
    {
        $this->failureMessage = $value;

        return $this;
    }

    public function data($value)
    {
        $this->data = $value;

        return $this;
    }

    protected function beforePrepare(): bool
    {
        // join in the products table
        $this->joinElementTable('checkout_charges');

        // select the columns
        $this->query->select([
            'checkout_charges.stripeId',
            'checkout_charges.email',
            'checkout_charges.live',
            'checkout_charges.chargeStatus',
            'checkout_charges.paid',
            'checkout_charges.refunded',
            'checkout_charges.amount',
            'checkout_charges.amountRefunded',
            'checkout_charges.currency',
            'checkout_charges.description',
            'checkout_charges.failureMessage',
            'checkout_charges.failureCode',
            'checkout_charges.data',
        ]);

        if ($this->stripeId) {
            $this->subQuery->andWhere(Db::parseParam('checkout_charges.stripeId', $this->stripeId));
        }

        if ($this->email) {
            $this->subQuery->andWhere(Db::parseParam('checkout_charges.email', $this->email));
        }

        if ($this->live) {
            $this->subQuery->andWhere(Db::parseParam('checkout_charges.live', $this->live));
        }

        if ($this->chargeStatus) {
            $this->subQuery->andWhere(Db::parseParam('checkout_charges.chargeStatus', $this->chargeStatus));
        }

        if ($this->paid) {
            $this->subQuery->andWhere(Db::parseParam('checkout_charges.paid', $this->paid));
        }

        if ($this->refunded) {
            $this->subQuery->andWhere(Db::parseParam('checkout_charges.refunded', $this->refunded));
        }

        if ($this->amount) {
            $this->subQuery->andWhere(Db::parseParam('checkout_charges.amount', $this->amount));
        }

        if ($this->amountRefunded) {
            $this->subQuery->andWhere(Db::parseParam('checkout_charges.amountRefunded', $this->amountRefunded));
        }

        if ($this->currency) {
            $this->subQuery->andWhere(Db::parseParam('checkout_charges.currency', $this->currency));
        }

        if ($this->description) {
            $this->subQuery->andWhere(Db::parseParam('checkout_charges.description', $this->description));
        }

        if ($this->failureCode) {
            $this->subQuery->andWhere(Db::parseParam('checkout_charges.failureCode', $this->failureCode));
        }

        return parent::beforePrepare();
    }
}
