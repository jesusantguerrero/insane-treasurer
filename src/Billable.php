<?php
namespace Insane\Paypal;

// use Insane\Paypal\Concerns\ManagesCustomer;
// use Insane\Paypal\Concerns\ManagesInvoices;
// use Insane\Paypal\Concerns\ManagesPaymentMethods;
use Insane\Paypal\Concerns\ManagesSubscriptions;
// use Insane\Paypal\Concerns\PerformsCharges;

trait Billable
{
    // use ManagesCustomer;
    // use ManagesInvoices;
    // use ManagesPaymentMethods;
    use ManagesSubscriptions;
    // use PerformsCharges;
}
