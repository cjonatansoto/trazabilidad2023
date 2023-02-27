<?php

namespace App\Http\Controllers;

use App\Models\DeclaredJurisdiction;
use App\Models\DispatchGuide;
use App\Models\DJ;
use App\Models\LotDispatchGuide;
use App\Models\NeppexControl;
use App\Models\User;
use App\Models\ViewLot;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class DashboardController extends Controller
{

    public function index()
    {
        $users = User::count();
        $neppex = NeppexControl::count();
        $dispatchguide = DispatchGuide::count();
        $lots = LotDispatchGuide::count();
        $declaredjurisdiction = DJ::count();

        return view('dashboard.index', [
            'users' => $users,
            'neppex' => $neppex,
            'dispatchguide' => $dispatchguide,
            'lots' => $lots,
            'declaredjurisdiction' => $declaredjurisdiction
        ]);
    }

}
