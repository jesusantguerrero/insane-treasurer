<?php

namespace Insane\Treasurer\Concerns;

use Illuminate\Http\Request;
use Insane\Treasurer\PaypalServiceV2;
use Insane\Treasurer\Invoice;

trait ManagesInvoices
{



    /**
     * get transactions invoice.
     *
     * @param  string  $name
     * @return \Insane\Treasurer\Models\Subscription|null
     */
    public function downloadInvoice($transactionId = null, $data, $filename = 'Invoice')
    {
        $paypalService = new PaypalServiceV2();
        $transactions = $paypalService->subscriptionTransactions($this->subscription()->agreement_id);
        foreach ($transactions->transactions as $transactionData) {
            if ($transactionData->id == $transactionId) {
                $transaction = $transactionData;
                break;
            }
        }
        $invoice = new Invoice($this, $transaction);
        return $invoice->downloadAs($filename, $data);
    }
}
