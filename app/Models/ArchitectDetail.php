<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Legacy table `architect_detail` — fee instalments against `architect_site`.
 *
 * NOTE: this is the one table whose parent is `architect_site`, not `sites`.
 * 7 of its 23 rows are orphaned (proj_id 10, 11, 16, 19, 21 no longer exist in
 * architect_site), so the relation is nullable and no FK constraint is added.
 *
 * Compare ArchitectDetailCompany, which despite its name points at `sites`.
 */
class ArchitectDetail extends Model
{
    protected $table = 'architect_detail';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];

    public function architectSite()
    {
        return $this->belongsTo(ArchitectSite::class, 'proj_id');
    }
}
