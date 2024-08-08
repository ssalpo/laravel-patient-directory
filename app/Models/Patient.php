<?php

namespace App\Models;

use App\Enums\PatientStatusEnum;
use App\Models\Traits\CurrentUser;
use Hashids\Hashids;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class Patient extends Model
{
    use CurrentUser, HasFactory, SoftDeletes;

    protected $fillable = [
        'hashid',
        'case_numbers',
        'status',
        'name',
        'phone',
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
        'print_date',
        'created_by',
        'comment',
        'uniq_code',
        'place_of_residence',
        'location_id',
        'medical_clinic_id',
        'shared_to_id',
    ];

    protected $casts = [
        'case_numbers' => 'array',
        'categories' => 'array',
        'birthday' => 'date',
        'print_date' => 'date',
        'sampling_date' => 'datetime',
        'sample_receipt_date' => 'datetime',
        'status' => PatientStatusEnum::class,
    ];

    protected static function booted(): void
    {
        static::created(function (Patient $patient) {
            $patient->update(['hashid' => (new Hashids())->encode($patient->id)]);
        });
    }

    public function scopeFilter(Builder $q, array $data): void
    {
        $q->when(
            Arr::get($data, 'query'),
            fn ($q, $search) => $q->where('name', 'LIKE', '%'.$search.'%')
                ->orWhere('diagnosis', 'LIKE', '%'.$search.'%')
                ->orWhereRaw("JSON_SEARCH(case_numbers, 'all', ?) IS NOT NULL", ["%{$search}%"])
        );

        $q->when(
            Arr::get($data, 'status'),
            fn ($q, $status) => $q->whereStatus($status)
        );
    }

    public function isReportReady(): bool
    {
        return $this->status === PatientStatusEnum::CHECKED;
    }

    public function scopeMyByPermission(Builder $q, string $field = 'created_by'): void
    {
        $q->when(
            auth()->user()?->hasPermissionTo('read_shared_patients') &&
            ! auth()->user()?->hasPermissionTo('share_patients'),
            fn ($q) => $q->my($field)->orWhere('shared_to_id', auth()->id())
        );
    }

    public function getCategoriesFormattedAttribute(): array
    {
        return array_map(function ($c) {
            $biopsy = $c['biopsyCustomValue'] ?? $c['biopsy'] ?? '';

            return sprintf('%s (%s)', $c['code'], ($biopsy ? $biopsy.', ' : '').$c['description']);
        }, $this->categories ?? []);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function medicalClinic(): BelongsTo
    {
        return $this->belongsTo(MedicalClinic::class);
    }

    public function caseNumbers(): HasMany
    {
        return $this->hasMany(PatientCaseNumber::class);
    }
}
