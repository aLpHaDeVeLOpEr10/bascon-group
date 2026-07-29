<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Legacy table `labour` — column names are intentionally kept as-is so the
 * CodeIgniter app can keep running against the same database.
 */
class Labour extends Model
{
    protected $table = 'labour';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];

    public function site()
    {
        return $this->belongsTo(Site::class, 'project_id');
    }
}
