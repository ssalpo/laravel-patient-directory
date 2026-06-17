<?php

namespace App\Http\Controllers;

use App\Exceptions\DoctorPatientsNotMovedException;
use App\Http\Requests\DoctorRequest;
use App\Http\Resources\DoctorResource;
use App\Http\Resources\PatientResource;
use App\Models\Doctor;
use App\Services\DoctorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class DoctorController extends Controller
{
    public function __construct(
        protected readonly DoctorService $doctorService
    ) {
        $this->middleware('can:read_doctors')->only('index', 'patients');
        $this->middleware('can:edit_doctors')->only(['edit', 'update']);
        $this->middleware('can:create_doctors')->only(['create', 'store']);
        $this->middleware('can:delete_doctors')->only(['destroy']);
    }

    /**
     * Возвращает список докторов
     */
    public function index(): Response
    {
        $doctors = Doctor::withCount('patients')
            ->orderByDesc('created_at')
            ->paginate(100);

        return inertia('Doctors/Index', [
            'doctors' => DoctorResource::collection($doctors),
        ]);
    }

    /**
     * Возвращает форму добавления доктора
     */
    public function create(): Response
    {
        return inertia('Doctors/Edit');
    }

    /**
     * Добавляет нового доктора
     */
    public function store(DoctorRequest $request): JsonResponse|RedirectResponse
    {
        $doctor = $this->doctorService->store($request->validated());

        if ($request->has('modal')) {
            return response()->json(DoctorResource::make($doctor));
        }

        return redirect()->route('doctors.index');
    }

    /**
     * Возвращает форму редактирования доктора
     */
    public function edit(Doctor $doctor): Response
    {
        return inertia('Doctors/Edit', [
            'doctor' => DoctorResource::make($doctor),
        ]);
    }

    /**
     * Обновляет данные доктора
     */
    public function update(int $id, DoctorRequest $request): RedirectResponse
    {
        $this->doctorService->update($id, $request->validated());

        return redirect()->route('doctors.index');
    }

    /**
     * Удаляет доктора
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->doctorService->destroy($id);
        } catch (DoctorPatientsNotMovedException $e) {
            return redirect()->back()->with('message', $e->getMessage());
        }

        return redirect()->route('doctors.index');
    }

    /**
     * Возвращает список пациентов доктора
     */
    public function patients(Doctor $doctor): Response
    {
        $filterParams = request()?->all();

        $patients = $doctor->patients()
            ->with('caseNumbers')
            ->filter($filterParams)
            ->orderByDesc('created_at')
            ->paginate(100)
            ->onEachSide(0)
            ->withQueryString();

        return inertia('Doctors/Patients', [
            'filterParams' => $filterParams,
            'doctor' => DoctorResource::make($doctor),
            'patients' => PatientResource::collection($patients),
        ]);
    }
}
