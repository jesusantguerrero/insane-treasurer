<?php

namespace Insane\Treasurer;


// Used to process plans

use Exception;
use Insane\Treasurer\Models\Plan as ModelsPlan;
use PayPal\Api\Agreement;
use PayPal\Api\AgreementStateDescriptor;
use PayPal\Api\Currency;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\Plan;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Api\Payer;
use PayPal\Common\PayPalModel;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Exception\PayPalConnectionException;

class PaypalService {
    private $apiContext;
    private $plan_id;
    private $client_id;
    private $secret;

    // Create a new instance with our paypal credentials
    public function __construct()
    {
        $this->setSettings();
        // Set the Paypal API Context/Credentials
        $this->apiContext = new ApiContext(new OAuthTokenCredential($this->client_id, $this->secret));
        $this->apiContext->setConfig(config('paypal.settings'));
    }

    public function createPlan($planData, $payments){
        // Create a new billing plan
        $plan = new Plan();
        $plan->setName($planData['name'])->setDescription($planData['description'])->setType('infinite');

        // Set billing plan definitions
        $paymentDefinitions = [];
        foreach ($payments as $payment) {
            $paymentDefinitions[] = $this->createPayment($payment);
        }

        // Set merchant preferences
        $merchantPreferences = $this->createMerchantPreferences();

        $plan->setPaymentDefinitions($paymentDefinitions);
        $plan->setMerchantPreferences($merchantPreferences);

        //create the plan
        try {
            $createdPlan = $plan->create($this->apiContext);
            try {
                $patch = new Patch();
                $value = new PayPalModel(json_encode(["state" => "ACTIVE"]));
                $patch->setOp('replace')->setPath('/')->setValue($value);
                $patchRequest = new PatchRequest();
                $patchRequest->addPatch($patch);
                $createdPlan->update($patchRequest, $this->apiContext);
                $updatedPlan = Plan::get($createdPlan->getId(), $this->apiContext);
                return $updatedPlan;
            } catch (PayPalConnectionException $ex) {
                throw new Exception($ex->getCode(), $ex->getData());
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }
        } catch (PayPalConnectionException $ex) {
            throw new Exception($ex->getCode(), $ex->getData());
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

    }

    public function getPlans() {
        return Plan::get($this->apiContext);
    }

    public function executeAgreement($token) {
        $agreement = new \PayPal\Api\Agreement();
        try {
            return $agreement->execute($token, $this->apiContext);
        } catch (PayPalConnectionException $ex) {
            throw new Exception($ex->getData(), $ex->getCode());
        }
    }

    public function getBillings() {
        $agreement = Agreement::get("I-08GYJKVH5XKR", $this->apiContext);
        return $agreement;
    }

    public function createAgreement($planId) {
        $dailyPlan = ModelsPlan::where('paypal_plan_id', $planId)->get();
        if (!count($dailyPlan)) {
            throw new Exception("This plan doesn't exists");

        }
        $agreement = new Agreement();
        $agreement->setName("Daily App:" . "Subscription Agreement")
          ->setDescription($dailyPlan[0]->name)
          ->setStartDate(\Carbon\Carbon::now()->addMinutes(5)->toIso8601String());

        // Set plan id
        $plan = new Plan();
        $plan->setId($planId);
        $agreement->setPlan($plan);

        // Add payer type
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        $agreement->setPayer($payer);

        try {
          // Create agreement
          $agreement = $agreement->create($this->apiContext);
          // Extract approval URL to redirect user
          return  $agreement->getApprovalLink();
        } catch (PayPalConnectionException $ex) {
          throw new Exception($ex->getData(),  $ex->getCode());
        }
    }

    private function createMerchantPreferences() {
        return (new MerchantPreferences())
          ->setReturnUrl(config('app.url')."/subscriptions/paypal-return")
          ->setCancelUrl(config('app.url')."/subscriptions/paypal-return")
          ->setAutoBillAmount('yes')
          ->setInitialFailAmountAction('CONTINUE')
          ->setMaxFailAttempts('0');
    }

    private function createPayment($payment) {
        return (new PaymentDefinition())
          ->setName($payment['name'])
          ->setType('REGULAR')
          ->setFrequency('Month')
          ->setFrequencyInterval('1')
          ->setCycles('0')
          ->setAmount(new Currency(array('value' => $payment['value'], 'currency' => 'USD')));
    }

    private function setSettings() {
        // Detect if we are running in live mode or sandbox
           if(config('paypal.settings.mode') == 'live'){
            $this->client_id = config('paypal.live_client_id');
            $this->secret = config('paypal.live_secret');
        } else {
            $this->plan_id = getenv('PAYPAL_SANDBOX_PLAN_ID');
            $this->client_id = config('paypal.sandbox_client_id');
            $this->secret = config('paypal.sandbox_secret');
        }
    }

    static function getSettings() {
        // Detect if we are running in live mode or sandbox
           $mode = config('paypal.settings.mode') == 'live' ? 'live' : 'sandbox';
            $settings = [
                "client_id" => config("paypal.{$mode}_client_id"),
                "secret" => config("paypal.{$mode}_secret")
            ];
            return $settings;
    }

    static function getApiContext() {
        $settings = self::getSettings();
        // Set the Paypal API Context/Credentials
        $apiContext = new ApiContext(new OAuthTokenCredential($settings['client_id'], $settings['secret']));
        $apiContext->setConfig(config('paypal.settings'));
        return $apiContext;
    }

    static function getAgreement($id) {
        $apiContext = self::getApiContext();
        return Agreement::get($id, $apiContext);
    }

    static function cancelAgreement($id) {
        $apiContext = self::getApiContext();
        $agreement = Agreement::get($id, $apiContext);
        $agreement->cancel(new AgreementStateDescriptor(json_encode(["note" => "Test"])), $apiContext);
        return Agreement::get($id, $apiContext);
    }

    static function suspendAgreement($id) {
        $apiContext = self::getApiContext();
        $agreement = Agreement::get($id, $apiContext);
        $agreement->suspend(new AgreementStateDescriptor(json_encode(["note" => "Test"])), $apiContext);
        return Agreement::get($id, $apiContext);
    }

    static function reactivateAgreement($id) {
        $apiContext = self::getApiContext();
        $agreement = Agreement::get($id, $apiContext);
        $agreement->reActivate(new AgreementStateDescriptor(json_encode(["note" => "Test"])), $apiContext);
        return Agreement::get($id, $apiContext);
    }

}
