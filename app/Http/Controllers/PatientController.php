<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientCommentRequest;
use App\Http\Requests\PatientReportRequest;
use App\Http\Requests\PatientRequest;
use App\Http\Requests\PrintDateRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use App\Services\PatientService;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PatientController extends Controller
{
    public function __construct(
        protected readonly PatientService $patientService
    ) {
        $this->middleware('can:read_all_patients')->only(['fullRecords']);
        $this->middleware('permission:share_patients|read_shared_patients')->only(['all']);
        $this->middleware('permission:share_patients')->only(['sharePatient']);
        $this->middleware('can:create_patients')->only(['create', 'store']);
        $this->middleware('can:edit_patients,read_all_patients')->only(['edit', 'update']);
        $this->middleware('can:edit_patients')->only('deletePhoto');
        $this->middleware('can:add_report')->only(['saveReport', 'submit']);
        $this->middleware('can:add_comment')->only(['saveComment']);
    }

    /**
     * Возвращает все записи пациентов
     */
    public function all(): Response
    {
        $patientsQuery = Patient::filter(request()?->all())
            ->with('location', 'caseNumbers')
            ->orderBy('created_at', 'DESC');

        if (
            auth()->user()?->hasPermissionTo('read_shared_patients') &&
            ! auth()->user()?->hasPermissionTo('share_patients')
        ) {
            $patientsQuery = $patientsQuery->where('shared_to_id', auth()->id());
        }

        $patients = $patientsQuery->paginate(100)
            ->onEachSide(0)
            ->withQueryString();

        return inertia('Patients/All', [
            'patients' => PatientResource::collection($patients),
        ]);
    }

    /**
     * Показывает записи всех колонок пациента
     */
    public function fullRecords(): Response
    {
        $patients = PatientResource::collection(
            Patient::orderByDesc('created_at')->with('location', 'caseNumbers')->get()
        );

        return inertia('Patients/FullRecords', compact('patients'));
    }

    /**
     * Возвращает статистику по приему пациентов сгруппированный по дням
     */
    public function dailyStatistics(): Response
    {
        $forShared = auth()->user()?->hasPermissionTo('read_shared_patients') && ! auth()->user()?->hasPermissionTo('share_patients');

        $statistics = $this->patientService->dailyStatistics(
            $forShared ? auth()->id() : null
        );

        return inertia('Patients/DailyStatistics', compact('statistics'));
    }

    /**
     * Возвращает пациентов текущего авторизованного пользователя
     */
    public function index(): Response
    {
        $patients = Patient::my('created_by')
            ->with('caseNumbers')
            ->filter(request()?->all())
            ->orderByDesc('created_at')
            ->paginate(100)
            ->onEachSide(0)
            ->withQueryString();

        return inertia('Patients/Index', [
            'filterParams' => request()?->all(),
            'patients' => PatientResource::collection($patients),
        ]);
    }

    /**
     * Возвращает форму добавления пациента
     */
    public function create(): Response
    {
        return inertia('Patients/Edit');
    }

    /**
     * Добавляет нового пациента
     */
    public function store(PatientRequest $request): RedirectResponse
    {
        $patient = $this->patientService->store(
            $request->validated() + ['photos' => $request->file('photos', [])]
        );

        return redirect()->route('patients.show', $patient->id)->with('isCreated', true);
    }

    /**
     * Просмотр карточки пациента
     */
    public function show(int $id): Response
    {
        $patient = Patient::myByPermission()
            ->with('photos', 'doctor', 'medicalClinic', 'location', 'caseNumbers')
            ->findOrFail($id);

        return inertia('Patients/Show', [
            'qrCode' => (string) QrCode::size(100)->generate(route('public.patients.show', $patient->hashid)),
            'patient' => PatientResource::make($patient),
        ]);
    }

    /**
     * Возвращает форму редактирования пациента
     */
    public function edit(int $id): Response
    {
        $patient = Patient::myByPermission()
            ->with(['doctor', 'location'])
            ->findOrFail($id);

        return inertia('Patients/Edit', [
            'patient' => PatientResource::make($patient),
        ]);
    }

    /**
     * Обновляет данные пациента
     */
    public function update(int $id, PatientRequest $request): RedirectResponse
    {
        $patient = $this->patientService->update(
            $id, $request->validated() + ['photos' => $request->file('photos', [])]
        );

        return redirect()->route('patients.show', $patient->id);
    }

    /**
     * Сохраняет ответ итогового результата диагноза
     */
    public function saveReport(int $id, PatientReportRequest $request): RedirectResponse
    {
        $this->patientService->saveReport($id, $request->validated());

        return redirect()->route('patients.show', $id);
    }

    /**
     * Сохраняет комментарий по итоговому результату диагноза
     */
    public function saveComment(int $id, PatientCommentRequest $request): RedirectResponse
    {
        $this->patientService->saveComment($id, $request->comment);

        return redirect()->route('patients.show', $id);
    }

    /**
     * Меняет статус результата на Проверено
     */
    public function markAsChecked(int $id): RedirectResponse
    {
        $this->patientService->markAsChecked($id);

        return redirect()->route('patients.show', $id);
    }

    /**
     * Возвращает данные для печати на принтере карточки пациента
     */
    public function print(Patient $patient): Response
    {
        $patient->load(['doctor', 'location', 'caseNumbers']);

        return inertia('Patients/Print', [
            'currentDate' => now()->format('d.m.Y'),
            'patient' => PatientResource::make($patient),
        ]);
    }

    /**
     * Сохраняет дату печати карточки клиента
     */
    public function editPrintDate(int $id, PrintDateRequest $request): RedirectResponse
    {
        $this->patientService->changePrintDate($id, $request->print_date);

        return back();
    }

    /**
     * Удаляет загруженное фото пациента по ID фотографии
     */
    public function deletePhoto(int $id, int $photo): RedirectResponse
    {
        $this->patientService->deletePhoto($id, $photo);

        return back();
    }

    public function sharePatient(int $patientId, int $userId): void
    {
        $this->patientService->sharePatient($patientId, $userId);
    }

    public function clearShared(int $patientId): void
    {
        $this->patientService->clearShared($patientId);
    }
}
