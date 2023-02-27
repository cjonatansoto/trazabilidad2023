<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TraceabilityMonitoring extends Model
{

    use HasFactory;

    protected $connection = 'sqlsrv';

    protected $dateFormat ='Y-d-m H:i:s.v';

    protected $table = 'traceability_monitoring';

    protected $fillable = [
        'loaded_boxes',
        'kg_loaded',
        'lots_declared',
        'boxes_in_dj',
        'kg_in_dj',
        'boxes_in_neppex',
        'kg_in_neppex',
        'boxes_unallocated',
        'kg_unallocated',
    ];

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

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo('App\Models\User', 'updated_by');
    }

}
