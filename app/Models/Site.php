<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Legacy table `sites` — the construction projects.
 *
 * NOTE: `total_price` is deliberately NOT cast to a number. Real rows contain
 * values such as '20% of profit', '12%' and '8%' alongside plain amounts.
 *
 * Child tables are split between two foreign key names: `project_id`
 * (material, b_material, labour_instalment, labour) and `proj_id`
 * (misc, return_payment, construction_detail, payments_recieved,
 * architect_detail_company). Neither is enforced by a DB constraint.
 */
class Site extends Model
{
    protected $table = 'sites';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];

    /**
     * The label the legacy app builds for a project, e.g. "307-W - Phase 08".
     * Mirrors Construction.php:443 and Admin_setting.php:638.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->project_name.' - '.$this->sector.' - '.$this->phase;
    }

    /**
     * A second, differently-punctuated label used when stamping proj_name onto
     * material rows — Construction.php:557 uses "name-block - phase".
     */
    public function getStampNameAttribute(): string
    {
        return $this->project_name.'-'.$this->sector.' - '.$this->phase;
    }

    public function materials()
    {
        return $this->hasMany(Material::class, 'project_id');
    }

    public function bMaterials()
    {
        return $this->hasMany(BMaterial::class, 'project_id');
    }

    public function labourInstalments()
    {
        return $this->hasMany(LabourInstalment::class, 'project_id');
    }

    public function labourTypes()
    {
        return $this->hasMany(Labour::class, 'project_id');
    }

    public function miscEntries()
    {
        return $this->hasMany(Misc::class, 'proj_id');
    }

    public function returnPayments()
    {
        return $this->hasMany(ReturnPayment::class, 'proj_id');
    }

    public function constructionDetails()
    {
        return $this->hasMany(ConstructionDetail::class, 'proj_id');
    }

    public function paymentsReceived()
    {
        return $this->hasMany(PaymentReceived::class, 'proj_id');
    }

    public function architectDetailsCompany()
    {
        return $this->hasMany(ArchitectDetailCompany::class, 'proj_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'project_id');
    }
}
