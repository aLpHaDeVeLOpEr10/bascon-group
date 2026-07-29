<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Legacy table `category` — column names are intentionally kept as-is so the
 * CodeIgniter app can keep running against the same database.
 */
class Category extends Model
{
    protected $table = 'category';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];
}
