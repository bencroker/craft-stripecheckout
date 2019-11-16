<?php
/**
 * Stripe Checkout plugin for Craft CMS 3.x
 *
 * Bringing the power of Stripe Checkout to your Craft templates.
 *
 * @link      https://github.com/jalendport/craft-stripecheckout
 * @copyright Copyright (c) 2018 Jalen Davenport
 */

namespace jalendport\stripecheckout\services;

use jalendport\stripecheckout\StripeCheckout;
use jalendport\stripecheckout\elements\Charge;
use jalendport\stripecheckout\elements\db\ChargeQuery;
use jalendport\stripecheckout\events\ChargeEvent;

use Craft;
use craft\base\Component;
use craft\helpers\Json;

use yii\base\Exception;
use yii\web\NotFoundHttpException;

class ChargeService extends Component
{
    // Constants
    // =========================================================================

    const EVENT_BEFORE_CHARGE = 'beforeCharge';

    const EVENT_AFTER_CHARGE = 'afterCharge';

    // Public Methods
    // =========================================================================

    public function createCharge($request)
    {
        // Extra stripe charge request params, can be set with the BEFORE_CHARGE event
        $additional = [];

        // Trigger beforeCharge event
        $event = new ChargeEvent([
            'request' => $request,
        ]);
        $self = new static;
        $self->trigger(self::EVENT_BEFORE_CHARGE, $event);

        $response = $this->createStripeCharge($request, $additional);

        if ((!isset($response['charge'])) or (isset($response['message']))) {
            return $response;
        }

        $charge = $this->insertCharge($response['charge']);

        if (!$charge) {
            throw new Exception('Couldn’t create the charge element.');
        }

        // Trigger afterCharge event
        $event = new ChargeEvent([
            'request' => $request,
            'charge'  => $charge,
        ]);
        $self = new static;
        $self->trigger(self::EVENT_AFTER_CHARGE, $event);

        return $charge;
    }

    public function createStripeCharge($request, $additional = [])
    {
        $secretKey = StripeCheckout::getInstance()->settingsService->getSecretKey();

        \Stripe\Stripe::setApiKey($secretKey);

        $response = [];

        try {
            $response['charge'] = \Stripe\Charge::create([
                'source'        => $request['token'],
                'receipt_email' => $request['email'],
                'amount'        => $request['options']['amount'],
                'currency'      => $request['options']['currency'],
                'description'   => $request['options']['description'],
                'shipping'      => $request['shipping'],
                'metadata'      => $request['metadata'],
            ], $additional);
        } catch (\Stripe\Error\Base $e) {
            Craft::$app->getErrorHandler()->logException($e);

            $body = $e->getJsonBody();
            $response = $body['error'];

            $response['message'] = $e->getMessage();
        } catch (Exception $e) {
            Craft::$app->getErrorHandler()->logException($e);
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public function insertCharge($stripeCharge = null)
    {
        if ($stripeCharge) {
            // Update charge if it already exists
            $exists = $this->getChargeByStripeId($stripeCharge->id);

            if ($exists) {
                $res = $this->updateCharge($stripeCharge);

                if ($res) {
                    return true;
                }
            } else {
                $charge = new Charge();

                $charge->stripeId       = $stripeCharge->id ?? null;
                $charge->email          = $stripeCharge->receipt_email ?? null;
                $charge->live           = $stripeCharge->livemode ?? null;
                $charge->chargeStatus   = $stripeCharge->status ?? null;
                $charge->paid           = $stripeCharge->paid ?? null;
                $charge->refunded       = $stripeCharge->refunded ?? null;
                $charge->amount         = $stripeCharge->amount ?? null;
                $charge->amountRefunded = $stripeCharge->amount_refunded ?? null;
                $charge->currency       = $stripeCharge->currency ?? null;
                $charge->description    = $stripeCharge->description ?? null;
                $charge->failureCode    = $stripeCharge->failure_code ?? null;
                $charge->failureMessage = $stripeCharge->failure_message ?? null;
                $charge->data           = $stripeCharge ? Json::encode($stripeCharge) : null;

                $res = Craft::$app->getElements()->saveElement($charge, true, false);

                if ($res) {
                    return $charge;
                }
            }
        }

        return null;
    }

    public function updateCharge($stripeCharge = null)
    {
        if ($stripeCharge) {
            $charge = $this->getChargeByStripeId($stripeCharge->id);

            if (!$charge) {
                return null;
            }

            $charge->email          = $stripeCharge->receipt_email ?? null;
            $charge->live           = $stripeCharge->livemode ?? null;
            $charge->chargeStatus   = $stripeCharge->status ?? null;
            $charge->paid           = $stripeCharge->paid ?? null;
            $charge->refunded       = $stripeCharge->refunded ?? null;
            $charge->amount         = $stripeCharge->amount ?? null;
            $charge->amountRefunded = $stripeCharge->amount_refunded ?? null;
            $charge->currency       = $stripeCharge->currency ?? null;
            $charge->description    = $stripeCharge->description ?? null;
            $charge->failureCode    = $stripeCharge->failure_code ?? null;
            $charge->failureMessage = $stripeCharge->failure_message ?? null;
            $charge->data           = $stripeCharge ? Json::encode($stripeCharge) : null;

            $res = Craft::$app->getElements()->saveElement($charge, true, false);

            if ($res) {
                return $charge;
            }
        }

        return null;
    }

    public function getChargeById($id = null)
    {
        if ($id) {
            $query = new ChargeQuery(Charge::class);
            $query->id = $id;

            return $query->one();
        }

        return null;
    }

    public function getChargeByStripeId($id = null)
    {
        if ($id) {
            $query = new ChargeQuery(Charge::class);
            $query->stripeId = $id;

            return $query->one();
        }

        return null;
    }
}
