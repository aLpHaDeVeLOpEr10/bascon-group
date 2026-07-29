<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Legacy table `setting` — a single row driving the approval workflow.
 *
 * civil_status / finish_status:
 *   0 = instant   — new entries are written with status 1 (immediately live)
 *   1 = approval  — new entries are written with status 0 (pending admin)
 *
 * The CodeIgniter model ran `UPDATE setting SET ...` with no WHERE clause
 * (Admin_model::insert_setting), so the table must always hold exactly one row.
 * current() enforces that.
 */
class Setting extends Model
{
    protected $table = 'setting';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'civil_status' => 'integer',
        'finish_status' => 'integer',
    ];

    public static function current(): self
    {
        return static::query()->orderBy('id')->firstOr(function () {
            return static::create(['civil_status' => 0, 'finish_status' => 0]);
        });
    }

    /** Status a new civil material row should be created with. */
    public function civilEntryStatus(): int
    {
        return $this->civil_status == 0 ? 1 : 0;
    }

    /** Status a new finishing material row should be created with. */
    public function finishEntryStatus(): int
    {
        return $this->finish_status == 0 ? 1 : 0;
    }
}
