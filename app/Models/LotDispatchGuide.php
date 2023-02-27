<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class LotDispatchGuide extends Model
{

    use HasFactory;

    protected $dateFormat ='Y-d-m H:i:s.v';

    protected $connection = 'sqlsrv';

    protected $table = 'lot_dispatch_guides';

    protected $fillable = [
        'lot_id',
        'dispatch_guide_id',
        'loaded_by',
        'created_at',
        'updated_at'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->loaded_by = is_object(Auth::guard(config('app.guards.web'))->user()) ? Auth::guard(config('app.guards.web'))->user()->id : 1;
            $model->loaded_by = is_object(Auth::guard(config('app.guards.web'))->user()) ? Auth::guard(config('app.guards.web'))->user()->id : 1;
        });

    }
}
