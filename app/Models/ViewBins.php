<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ViewBins extends Model
{

    protected $connection = 'bdsystem';

    use HasFactory;

    protected $table = 'v_bins_detalle';

}
