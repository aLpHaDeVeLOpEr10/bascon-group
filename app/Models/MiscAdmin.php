<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Legacy table `misc_admin` — column names are intentionally kept as-is so the
 * CodeIgniter app can keep running against the same database.
 */
class MiscAdmin extends Model
{
    protected $table = 'misc_admin';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];
}
