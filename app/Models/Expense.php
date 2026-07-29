<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Legacy table `expense` — column names are intentionally kept as-is so the
 * CodeIgniter app can keep running against the same database.
 */
class Expense extends Model
{
    protected $table = 'expense';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];
}
