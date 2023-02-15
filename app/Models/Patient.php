<?php

namespace App\Models;

use App\Models\Traits\CurrentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, CurrentUser, SoftDeletes;

    protected $fillable = [
        'case_numbers',
        'status',
        'name',
        'birthday',
        'gender',
        'sampling_date',
        'sample_receipt_date',
        'anamnes',
        'categories',
        'doctor_id',
        'microscopic_description',
        'diagnosis',
        'note',
        'created_by'
    ];

    protected $casts = [
        'case_numbers' => 'array',
        'categories' => 'array',
        'birthday' => 'date',
        'sampling_date' => 'datetime',
        'sample_receipt_date' => 'datetime',
    ];

    public const STATUS_CHECKING = 1;
    public const STATUS_CHECKED = 2;

    public function scopeFilter($q)
    {
        $q->when(
            request('query'),
            fn($q, $search) => $q->where('name', 'LIKE', '%' . $search . '%')
                ->orWhereRaw("JSON_SEARCH(case_numbers, 'all', ?) IS NOT NULL", ["%{$search}%"])
        );
    }

    public function getCategoriesFormattedAttribute()
    {
        return array_map(fn($c) => sprintf('%s (%s)', $c['code'], $c['description']), $this->categories);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function photos()
    {
        return $this->morphMany(Photo::class, 'photoable');
    }

    public function generateCaseNumbers(): array
    {
        $caseNumbers = [];

        foreach ($this->categories as $category) {
            $caseNumbers[] = sprintf(
                'D%s/%s %s',
                substr(date('Y'), -2),
                sprintf("%02d", $this->id),
                $category['code']
            );
        }

        $this->update(['case_numbers' => $caseNumbers]);

        return $caseNumbers;
    }
}
