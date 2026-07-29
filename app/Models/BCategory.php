<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Legacy table `b_category` — column names are intentionally kept as-is so the
 * CodeIgniter app can keep running against the same database.
 */
class BCategory extends Model
{
    protected $table = 'b_category';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];
}
