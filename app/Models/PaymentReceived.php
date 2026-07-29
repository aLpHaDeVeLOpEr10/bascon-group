<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Legacy table `payments_recieved` — column names are intentionally kept as-is so the
 * CodeIgniter app can keep running against the same database.
 */
class PaymentReceived extends Model
{
    protected $table = 'payments_recieved';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];

    public function site()
    {
        return $this->belongsTo(Site::class, 'proj_id');
    }
}
