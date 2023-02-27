<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\DispatchGuide;
use App\Models\DispatchGuideItem;
use App\Models\StockLot;
use Illuminate\Http\Request;

class DispatchGuideController extends ApiController
{
    public function getById(Request $request)
    {
        $dispatchguide = DispatchGuide::where('id', $request->id)->first();

        $sumKg = DispatchGuideItem::where('dispatch_guide_id', $dispatchguide->id)->sum('kgs');

        $sumKgBins = StockLot::join('lot_dispatch_guides', 'stock_lots.lot_dispatch_guide_id', '=', 'lot_dispatch_guides.id')->where('lot_dispatch_guides.dispatch_guide_id', $dispatchguide->id)->where('quantity_type_id', 2)->sum('kg_amount');

        $sumKgBoxes = StockLot::join('lot_dispatch_guides', 'stock_lots.lot_dispatch_guide_id', '=', 'lot_dispatch_guides.id')->where('lot_dispatch_guides.dispatch_guide_id', $dispatchguide->id)->where('quantity_type_id', 1)->sum('kg_amount');

        $sumKgTotal = StockLot::join('lot_dispatch_guides', 'stock_lots.lot_dispatch_guide_id', '=', 'lot_dispatch_guides.id')->where('lot_dispatch_guides.dispatch_guide_id', $dispatchguide->id)->sum('kg_amount');

        $stock = (integer) $sumKg - (integer) $sumKgTotal;

        $data = [
            'id' => $dispatchguide->id,
            'number' => $dispatchguide->number,
            'kg' => number_format($sumKg, 3, '.', ''),
            'kgBins' => number_format($sumKgBins, 3, '.', ''),
            'kgBoxes' => number_format($sumKgBoxes, 3, '.', ''),
            'sumKgTotal' => number_format($sumKgTotal, 3, '.', ''),
            'stock' => number_format($stock, 3, '.', '')
        ];
        return $this->json($request, $data, 200);
    }
}
