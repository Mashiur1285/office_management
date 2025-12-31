<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobSector extends Model
{
    protected $fillable = [
        'name',
        'parent_id',
        'description',
    ];

    /**
     * Get the parent job sector
     */
    public function parent()
    {
        return $this->belongsTo(JobSector::class, 'parent_id');
    }

    /**
     * Get the sub-categories/children
     */
    public function children()
    {
        return $this->hasMany(JobSector::class, 'parent_id');
    }

    /**
     * Get clients with this job sector
     */
    public function clients()
    {
        return $this->hasMany(Client::class, 'job_sector', 'name');
    }

    /**
     * Get full name with parent if exists
     */
    public function getFullNameAttribute()
    {
        if ($this->parent) {
            return $this->parent->name . ' - ' . $this->name;
        }
        return $this->name;
    }
}
