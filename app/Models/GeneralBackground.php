<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class GeneralBackground extends Model
{

    use HasFactory;

    protected $connection = 'sqlsrv';

    protected $dateFormat ='Y-d-m H:i:s.v';

    protected $table = 'general_background';

    protected $fillable = [
        'dispatch_guide_id',
        'place_id',
        'cage',
        'warranty_statement',
        'harvest_date',
        'file',
        'created_by',
        'updated_by',
    ];

    public function dispatchGuide()
    {
        return $this->belongsTo('App\Models\DispatchGuide', 'dispatch_guide_id');
    }

    public function place()
    {
        return $this->belongsTo('App\Models\Place', 'place_id');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo('App\Models\User', 'updated_by');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = is_object(Auth::guard(config('app.guards.web'))->user()) ? Auth::guard(config('app.guards.web'))->user()->id : 1;
            $model->updated_by = is_object(Auth::guard(config('app.guards.web'))->user()) ? Auth::guard(config('app.guards.web'))->user()->id : 1;
        });

        static::updating(function ($model) {
            $model->updated_by = is_object(Auth::guard(config('app.guards.web'))->user()) ? Auth::guard(config('app.guards.web'))->user()->id : 1;
        });
    }

}
