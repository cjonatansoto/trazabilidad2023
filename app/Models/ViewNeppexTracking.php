<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ViewNeppexTracking extends Model
{
    protected $connection = 'sqlsrv';

    use HasFactory;

    protected $table = 'v_neppex_tracking';

}
