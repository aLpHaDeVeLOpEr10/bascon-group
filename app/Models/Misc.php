<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Legacy table `misc` — column names are intentionally kept as-is so the
 * CodeIgniter app can keep running against the same database.
 */
class Misc extends Model
{
    protected $table = 'misc';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];

    public function site()
    {
        return $this->belongsTo(Site::class, 'proj_id');
    }
}
