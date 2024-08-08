<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Location;
use App\Models\MedicalClinic;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AutocompleteController extends Controller
{
    public function roles(): Collection
    {
        return $this->transformCollection(
            items: Role::where(fn ($q) => $this->searchFinder($q, 'readable_name'))->get(),
            textField: 'readable_name',
            idField: 'name',
        );
    }

    public function permissions(): Collection
    {
        return $this->transformCollection(
            items: Permission::where(fn ($q) => $this->searchFinder($q, 'readable_name'))->get(),
            textField: 'readable_name'
        );
    }

    public function doctors(): Collection
    {
        return $this->transformCollection(
            Doctor::where(fn ($q) => $this->searchFinder($q))->orderByDesc('created_at')->get()
        );
    }

    public function medicalClinics(): Collection
    {
        return $this->transformCollection(
            MedicalClinic::where(fn ($q) => $this->searchFinder($q))->get()
        );
    }

    public function locations(): Collection
    {
        return $this->transformCollection(
            items: Location::where(fn ($q) => $this->searchFinder($q, ['id', 'region', 'area']))->get(),
            textField: 'full_address'
        );
    }

    private function searchFinder(Builder $q, array|string $field = ['id', 'name']): void
    {
        $q->when(request('q'), function ($q, $v) use ($field) {
            foreach ((array) $field as $index => $f) {
                $q->{$index > 0 ? 'orWhere' : 'where'}($f, 'like', '%'.$v.'%');
            }
        });
    }

    private function transformCollection(Collection $items, string $textField = 'name', string $idField = 'id'): Collection
    {
        return $items->transform(fn ($m) => [
            'id' => $m->{$idField},
            'text' => $m->{$textField},
        ]);
    }

    public function users(): Collection
    {
        return $this->transformCollection(
            User::where(fn ($q) => $this->searchFinder($q))->orderByDesc('created_at')->get()
        );
    }
}
