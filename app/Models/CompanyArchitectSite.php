<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Legacy table `Company_architect_site` — column names are intentionally kept as-is so the
 * CodeIgniter app can keep running against the same database.
 */
class CompanyArchitectSite extends Model
{
    protected $table = 'Company_architect_site';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];
}
