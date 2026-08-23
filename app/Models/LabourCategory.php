<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The catalogue of labour type names — the third of the trio alongside
 * `Category` (civil) and `BCategory` (finishing).
 *
 * Unlike those two it is not legacy: see the migration for why labour never
 * had one. `Labour` remains the assignment of a type to a project, and refers
 * to the name as a string rather than by key.
 */
class LabourCategory extends Model
{
    protected $table = 'labour_category';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];
}
