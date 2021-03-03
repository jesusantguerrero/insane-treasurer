<?php

namespace Insane\Treasurer;

use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Laravel\Cashier\Exceptions\InvalidInvoice;
use Symfony\Component\HttpFoundation\Response;

class Invoice
{
    /**
     * The Stripe model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $owner;

    /**
     * The Stripe invoice instance.
     *
     * @var \Stripe\Invoice
     */
    protected $invoice;

    /**
     * The Stripe invoice line items.
     *
     * @var \Stripe\Collection|\Stripe\InvoiceLineItem[]
     */
    protected $items;

    /**
     * The taxes applied to the invoice.
     *
     * @var \Laravel\Cashier\Tax[]
     */
    protected $taxes;

    /**
     * Create a new invoice instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $owner
     * @param  \Stripe\Invoice  $invoice
     * @return void
     *
     * @throws \Laravel\Cashier\Exceptions\InvalidInvoice
     */
    public function __construct($owner, $invoice)
    {
        //  @todo
        // if ($owner->customer_id !== $transaction->) {
        //     throw InvalidInvoice::invalidOwner($invoice, $owner);
        // }

        $this->owner = $owner;
        $this->invoice = (object) $invoice;
    }

    /**
     * Get a Carbon date for the invoice.
     *
     * @param  \DateTimeZone|string  $timezone
     * @return \Carbon\Carbon
     */
    public function date($timezone = null)
    {
        $carbon = Carbon::createFromTimestampUTC($this->invoice->time ?? $this->invoice->date);

        return $timezone ? $carbon->setTimezone($timezone) : $carbon;
    }

    /**
     * Get the total amount that was paid (or will be paid).
     *
     * @return string
     */
    public function total()
    {
        return $this->formatAmount($this->rawTotal());
    }

    /**
     * Get the raw total amount that was paid (or will be paid).
     *
     * @return int
     */
    public function rawTotal()
    {
        return $this->invoice->total + $this->rawStartingBalance();
    }

    /**
     * Get the total of the invoice (before discounts).
     *
     * @return string
     */
    public function subtotal()
    {
        return $this->formatAmount($this->invoice->subtotal);
    }


    /**
     * Get the total tax amount.
     *
     * @return string
     */
    public function tax()
    {
        return $this->formatAmount($this->invoice->tax);
    }

    /**
     * Determine if the invoice has tax applied.
     * @todo
     * @return bool
     */
    public function hasTax()
    {
        $lineItems = $this->invoiceItems() + $this->subscriptions();

        return collect($lineItems);
    }

    /**
     * @todo
     * Get the taxes applied to the invoice.
     *
     * @return Tax[]
     */
    public function taxes()
    {
        if (! is_null($this->taxes)) {
            return $this->taxes;
        }
    }

    /**
     * Get all of the "invoice item" line items.
     *
     * @return \Laravel\Cashier\InvoiceLineItem[]
     */
    public function invoiceItems()
    {
        return $this->invoiceLineItemsByType('invoiceitem');
    }

    /**
     * Get all of the "subscription" line items.
     *
     * @return \Laravel\Cashier\InvoiceLineItem[]
     */
    public function subscriptions()
    {
        return $this->invoiceLineItemsByType('subscription');
    }

    /**
     * Get all of the invoice items by a given type.
     *
     * @param  string  $type
     * @return \Laravel\Cashier\InvoiceLineItem[]
     */
    public function invoiceLineItemsByType($type)
    {
        if (is_null($this->items)) {
            $this->items = new Collection([$this->invoice]);
        }

        return $this->items->filter(function () use ($type) {
            return $type == "subscription";
        })->map(function ($item) {
            return new $item;
        })->all();
    }

    /**
     * Format the given amount into a displayable currency.
     *
     * @param  int  $amount
     * @return string
     */
    protected function formatAmount($amount)
    {
        return Cashier::formatAmount($amount, $this->invoice->currency);
    }

    /**
     * Get the View instance for the invoice.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\View\View
     */
    public function view(array $data)
    {
        return View::make('receipt', array_merge($data, [
            'invoice' => $this,
            'owner' => $this->owner,
            'user' => $this->owner,
        ]));
    }

    /**
     * Capture the invoice as a PDF and return the raw bytes.
     *
     * @param  array  $data
     * @return string
     */
    public function pdf(array $data)
    {
        if (! defined('DOMPDF_ENABLE_AUTOLOAD')) {
            define('DOMPDF_ENABLE_AUTOLOAD', false);
        }

        $options = new Options;
        $options->setChroot(base_path());

        $dompdf = new Dompdf($options);
        $dompdf->setPaper(config('cashier.paper', 'letter'));
        $dompdf->loadHtml($this->view($data)->render());
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Create an invoice download response.
     *
     * @param  array  $data
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function download(array $data)
    {
        $filename = "Subscription Payment".'_'.$this->date()->month.'_'.$this->date()->year;
        return $this->downloadAs($filename, $data);
    }

    /**
     * Create an invoice download response with a specific filename.
     *
     * @param  string  $filename
     * @param  array  $data
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadAs($filename="invoice", array $data)
    {
        return new Response($this->pdf($data), 200, [
            'Content-Description' => 'File Transfer',
            'Content-Disposition' => 'attachment; filename="'.$filename.'.pdf"',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Type' => 'application/pdf',
            'X-Vapor-Base64-Encode' => 'True',
        ]);
    }


}
