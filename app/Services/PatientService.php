<?php

namespace App\Services;

use App\Enums\PatientStatusEnum;
use App\Jobs\AddUniqCodeForPatient;
use App\Models\Patient;
use Facades\App\Services\PatientCaseNumberService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PatientService
{
    /**
     * Добавляет нового пациента
     */
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $patient = Patient::create($data);

            PatientCaseNumberService::generate(
                $patient->id,
                $patient->created_at->format('Y'),
                array_column($patient->categories, 'code')
            );

            $this->uploadPhotos($patient, $data['photos']);

            AddUniqCodeForPatient::dispatch($patient);

            return $patient;
        });
    }

    /**
     * Обновляет данные пациента
     */
    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $patient = Patient::findOrFail($id);

            $patient->update($data);

            PatientCaseNumberService::generate(
                $patient->id,
                $patient->created_at->format('Y'),
                array_column($patient->categories, 'code')
            );

            $this->uploadPhotos($patient, $data['photos']);

            return $patient;
        });
    }

    /**
     * Возвращает статистику по приему пациентов сгруппированный по дням
     */
    public function dailyStatistics(?int $sharedUserId = null): Collection
    {
        return Patient::select(
            DB::raw('CAST(sampling_date AS DATE) s_date'),
            DB::raw('COUNT(*) AS total')
        )
            ->when($sharedUserId, fn ($q) => $q->where('shared_to_id', $sharedUserId))
            ->orderBy('s_date', 'DESC')
            ->groupBy('s_date')
            ->get();
    }

    /**
     * Сохраняет ответ итогового результата диагноза
     */
    public function saveReport(int $id, array $data): Patient
    {
        $patient = Patient::findOrFail($id);

        $patient->update(
            Arr::only($data, [
                'microscopic_description',
                'diagnosis',
                'note',
            ])
        );

        return $patient;
    }

    /**
     * Сохраняет комментарий по итоговому результату диагноза
     */
    public function saveComment(int $id, string $comment): Patient
    {
        $patient = Patient::findOrFail($id);

        $patient->update(['comment' => $comment]);

        return $patient;
    }

    /**
     * Меняет статус результата на Проверено
     */
    public function markAsChecked(int $id)
    {
        return Patient::findOrFail($id)->update([
            'status' => PatientStatusEnum::CHECKED,
        ]);
    }

    /**
     * Сохраняет дату печати карточки клиента
     */
    public function changePrintDate(int $id, string $printDate): bool
    {
        return Patient::findOrFail($id)->update([
            'print_date' => $printDate,
        ]);
    }

    /**
     * Удаляет загруженное фото пациента по ID фотографии
     */
    public function deletePhoto(int $id, int $photoId): void
    {
        $patient = Patient::findOrFail($id);

        DB::transaction(function () use ($patient, $photoId) {
            $patient->photos()
                ->whereId($photoId)
                ->firstOrFail()
                ->delete();

            Storage::disk('public')->delete($photo->url ?? '');
        });
    }

    /**
     * Генерирует уникальный код доступа для пациента
     */
    public function generateUniqAccessCode(int $id): int
    {
        do {
            $code = random_int(100000, 999999);
        } while (Patient::where('uniq_code', $code)->exists());

        Patient::findOrFail($id)->update(['uniq_code' => $code]);

        return $code;
    }

    /**
     * Загружает фотографии пациентов
     */
    private function uploadPhotos(Patient $patient, array $photos): void
    {
        foreach ($photos as $photo) {
            $patient->photos()->create([
                'url' => $photo?->store('photos', 'public'),
            ]);
        }
    }

    /**
     * Прикреплят пациента к другим пользователям
     */
    public function sharePatient(int $patientId, int $userId): void
    {
        Patient::findOrFail($patientId)->update([
            'shared_to_id' => $userId,
        ]);
    }

    /**
     * Открепляет пациента от других пользователей
     */
    public function clearShared(int $patientId): void
    {
        Patient::findOrFail($patientId)->update([
            'shared_to_id' => null,
        ]);
    }
}
