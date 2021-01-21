<?php
namespace Insane\Treasurer;

// use Insane\Treasurer\Concerns\ManagesCustomer;
// use Insane\Treasurer\Concerns\ManagesInvoices;
// use Insane\Treasurer\Concerns\ManagesPaymentMethods;
use Insane\Treasurer\Concerns\ManagesSubscriptions;
// use Insane\Treasurer\Concerns\PerformsCharges;

trait Billable
{
    // use ManagesCustomer;
    // use ManagesInvoices;
    // use ManagesPaymentMethods;
    use ManagesSubscriptions;
    // use PerformsCharges;
}
