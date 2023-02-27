<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\CountryRestriction;
use App\Models\DispatchGuide;
use App\Models\DispatchGuideItem;
use App\Models\StockLot;
use Illuminate\Http\Request;

class CountryRestrictionController extends ApiController
{
    public function getById(Request $request)
    {
        $countries = CountryRestriction::where('CodMer', $request->id)->get();
        return $this->json($request, $countries, 200);
    }
}
