<?php

namespace App\Http\Controllers\Public;

use App\Enums\PatientStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Services\PatientService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PatientController extends Controller
{
    public function __construct(
        protected readonly PatientService $patientService
    ) {
    }

    /**
     * Возвращает итоговый результат пациента по коду
     */
    public function checkResult(): void
    {
        $patient = Patient::where('uniq_code', request('code'))->first();

        if (! $patient) {
            throw ValidationException::withMessages([
                'code' => 'Введите корректный код.',
            ]);
        }

        if ($patient->status !== PatientStatusEnum::CHECKED) {
            throw ValidationException::withMessages([
                'code' => 'Ваше заключение еще не готово.',
            ]);
        }

        abort(409, '', ['X-Inertia-Location' => url()->route('public.patients.show', $patient->hashid)]);
    }

    /**
     * Карточка пациента в виде PDF
     */
    public function show(string $hash): Response
    {
        $patient = Patient::where(DB::raw('BINARY `hashid`'), $hash)
            ->with('doctor', 'medicalClinic', 'location', 'caseNumbers')
            ->firstOrFail();

        return Pdf::loadView(
            'pdf.patient', compact('patient')
        )->stream();
    }
}
