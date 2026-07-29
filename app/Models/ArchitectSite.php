<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Legacy table `architect_site` — architecture-only projects, separate from
 * `sites`. Its label column is `name` (not `project_name` as in `sites`).
 */
class ArchitectSite extends Model
{
    protected $table = 'architect_site';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];

    /** Mirrors Admin_setting.php:592 — "name - sector - phase". */
    public function getDisplayNameAttribute(): string
    {
        return $this->name.' - '.$this->sector.' - '.$this->phase;
    }

    public function details()
    {
        return $this->hasMany(ArchitectDetail::class, 'proj_id');
    }
}
