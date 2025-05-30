<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Record extends Model
{
    use HasFactory;
    protected $fillable = [
        'resource_id',
        'sector_id',
        'exhibition_id',
        'title',
        'first_name',
        'last_name',
        'email',
        'mobile_number',
        'gender',
        'country',
        'city',
        'phone',
        'job_title',
        'website',
        'company',
    ];
    protected $casts = [
        'phone' => 'array',
    ];
    public function countryRelation(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country', 'id');
    }

    public function stateRelation(): BelongsTo
    {
        return $this->belongsTo(State::class, 'city', 'id');
    }

    public function getFullNameAttribute()
    {
        return "{$this->title} {$this->first_name} {$this->last_name}";
    }

    public function scopeWithFullName(Builder $query)
    {
        $query->selectRaw("CONCAT(first_name, ' ', last_name) as full_name");
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Association::class, 'resource_id')->where('type', 'resource');
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Association::class, 'sector_id')->where('type', 'sector');
    }

    public function exhibition(): BelongsTo
    {
        return $this->belongsTo(Association::class, 'exhibition_id')->where('type', 'exhibition');
    }

}
