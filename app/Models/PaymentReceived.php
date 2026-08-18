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

    /**
     * `status` mirrors material.status: 0 awaiting an admin, 1 live, 2
     * rejected. The column is new, so rows the legacy app writes have the
     * default of 1 and count immediately.
     */
    public const PENDING = 0;
    public const LIVE = 1;
    public const REJECTED = 2;

    /** Only payments that actually count towards a site's balance. */
    public function scopeLive($query)
    {
        return $query->where('status', self::LIVE);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::PENDING);
    }

    public function site()
    {
        return $this->belongsTo(Site::class, 'proj_id');
    }
}
