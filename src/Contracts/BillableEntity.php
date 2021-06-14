<?php

namespace Insane\Treasurer\Contracts;

use Illuminate\Http\Request;

interface BillableEntity
{

    public function resolve(Request $request);
}
