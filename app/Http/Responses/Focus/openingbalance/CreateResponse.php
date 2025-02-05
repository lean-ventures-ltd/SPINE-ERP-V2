<?php

namespace App\Http\Responses\Focus\openingbalance;

use App\Models\customfield\Customfield;
use Illuminate\Contracts\Support\Responsable;

class CreateResponse implements Responsable
{
    /**
     * To Response
     *
     * @param \App\Http\Requests\Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function toResponse($request)
    {
        return view('focus.openingbalances.create')->with(product_helper());
    }
}