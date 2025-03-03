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
        'classification',
        'resource',
        'sector',
        'subsector',
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
}
