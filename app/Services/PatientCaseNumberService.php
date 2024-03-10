<?php

namespace App\Services;

use App\Models\PatientCaseNumber;

class PatientCaseNumberService
{
    /**
     * Генерирует номера кейсов пациента
     */
    public function generate(int $patientId, int $year, array $codes): array
    {
        return \DB::transaction(function () use ($patientId, $year, $codes) {
            $result = [];

            $currentNumber = PatientCaseNumber::getLastCaseNumberByPatientId($patientId) ?? PatientCaseNumber::currentYearMaxNumber($year) + 1;

            $cleared = $this->clearIfDiffCodesExists($patientId, $year, $codes);

            if (! $cleared) {
                return $result;
            }

            foreach ($codes as $code) {
                $result[] = PatientCaseNumber::create([
                    'patient_id' => $patientId,
                    'year' => $year,
                    'code' => $code,
                    'number' => $currentNumber,
                    'formatted' => $this->format($year, $currentNumber, $code),
                ]);
            }

            return $result;
        });
    }

    /**
     * Очищает все коды, если есть разница в структуре кодов
     */
    public function clearIfDiffCodesExists(int $patientId, int $year, array $codes): bool
    {
        if (! PatientCaseNumber::hasAnyDiffInStructure($patientId, $year, $codes)) {
            return false;
        }

        PatientCaseNumber::wherePatientId($patientId)->delete();

        return true;
    }

    public function format(int $year, int $number, string $code): string
    {
        $prefix = PatientCaseNumber::BASE_PREFIX;

        return sprintf($prefix.'%s/%s %s', substr($year, -2), sprintf('%02d', $number), $code);
    }
}
