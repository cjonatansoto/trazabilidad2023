<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class DispatchGuideType extends Model
{

    use HasFactory;

    protected $connection = 'sqlsrv';

    protected $table = 'dispatch_guide_types';

    protected $dateFormat ='Y-d-m H:i:s.v';

    protected $fillable = [
        'name',
        'visible',
    ];

}
