<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Diagnosis;
use App\Models\Patient;
use Carbon\Carbon;
use Log;

class DiagnosisController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/diagnoses",
     *     operationId="createDiagnosis",
     *     tags={"Diagnoses"},
     *     summary="Create a new diagnosis",
     *     description="Registers a new diagnosis in the system.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Diagnosis data",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(property="name", type="string", description="Diagnosis name", maxLength=255),
     *                 @OA\Property(property="description", type="string", description="Diagnosis description", maxLength=255),
     *                 example={"name": "Example Diagnosis", "description": "This is an example diagnosis"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="string", example="Diagnóstico creado correctamente."),
     *             @OA\Property(property="diagnosis", ref="#/components/schemas/Diagnosis")
     *         )
     *     ),
     *     @OA\Response(
     *         response=419,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object", example={"name": {"The name field is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Error al crear el diagnóstico."),
     *             @OA\Property(property="details", type="string", example="Error details")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 419);
        }

        try {
            $data = [
                'name' => $request->input('name'),
                'description' => $request->input('description'),
            ];
            $newDiagnosis = Diagnosis::create($data);

            return response()->json(['success' => 'Diagnóstico creado correctamente.', 'diagnosis' => $newDiagnosis], 200);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Error al crear el diagnóstico.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/diagnoses/{patient_id}/assign-diagnosis",
     *     operationId="assignDiagnosisToPatient",
     *     tags={"Diagnoses"},
     *     summary="Assign a diagnosis to a patient",
     *     description="Assigns a diagnosis to a specific patient in the system.",
     *     @OA\Parameter(
     *         name="patient_id",
     *         in="path",
     *         required=true,
     *         description="ID of the patient",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Diagnosis data",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"diagnosis_id", "diagnosis_date"},
     *                 @OA\Property(property="diagnosis_id", type="integer", description="ID of the diagnosis"),
     *                 @OA\Property(property="observation", type="string", description="Observation about the diagnosis", maxLength=255),
     *                 @OA\Property(property="diagnosis_date", type="string", format="date", description="Diagnosis date"),
     *                 example={"diagnosis_id": 1, "observation": "Observation text", "diagnosis_date": "2023-12-31"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="string", example="Diagnóstico asignado correctamente al paciente."),
     *             @OA\Property(property="patient", ref="#/components/schemas/Patient")
     *         )
     *     ),
     *     @OA\Response(
     *         response=419,
     *         description="Validation error or Patient not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object", example={"diagnosis_date": {"The diagnosis date field is required."}}),
     *             @OA\Property(property="error", type="string", example="El paciente no fue encontrado.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Error al asignar el diagnóstico al paciente."),
     *             @OA\Property(property="details", type="string", example="Error details")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function assignDiagnosis(Request $request, $patient_id)
    {
        try {
            $patient = Patient::find($patient_id);

            if (!$patient) {
                return response()->json(['error' => 'El paciente no fue encontrado.'], 419);
            }

            $validator = Validator::make($request->all(), [
                'diagnosis_id' => 'required|exists:diagnoses,id',
                'observation' => 'nullable|string|max:255',
                'diagnosis_date' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 419);
            }

            $diagnosis = Diagnosis::find($request->input('diagnosis_id'));

            $patient->diagnoses()->attach($diagnosis->id, [
                'observation' => $request->input('observation'),
                'creation_date' => $request->input('diagnosis_date'),
            ]);

            return response()->json(['success' => 'Diagnóstico asignado correctamente al paciente.', 'patient' => $patient], 200);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Error al asignar el diagnóstico al paciente.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/diagnoses/top-diagnoses-last-six-months",
     *     operationId="topDiagnosesLastSixMonths",
     *     tags={"Diagnoses"},
     *     summary="Get top diagnoses in the last six months",
     *     description="Retrieves the top five diagnoses that have been assigned the most in the last six months.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="top_diagnoses", type="array", @OA\Items(ref="#/components/schemas/Diagnosis"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Error al obtener los diagnósticos más asignados."),
     *             @OA\Property(property="details", type="string", example="Error details")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function topDiagnosesLastSixMonths()
    {
        try {
            $sixMonthsAgo = Carbon::now()->subMonths(6);

            $topDiagnoses = Diagnosis::select('diagnoses.id', 'diagnoses.name', 'diagnoses.description', DB::raw('COUNT(patient_diagnosis.diagnosis_id) as count'))
                ->join('patient_diagnosis', 'diagnoses.id', '=', 'patient_diagnosis.diagnosis_id')
                ->where('patient_diagnosis.created_at', '>=', $sixMonthsAgo)
                ->groupBy('diagnoses.id', 'diagnoses.name', 'diagnoses.description')
                ->orderByDesc('count')
                ->limit(5)
                ->get();

            $filteredTopDiagnoses = $topDiagnoses->map(function ($diagnosis) {
                return [
                    'name' => $diagnosis->name,
                    'description' => $diagnosis->description,
                ];
            });

            return response()->json(['top_diagnoses' => $filteredTopDiagnoses], 200);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Error al obtener los diagnósticos más asignados.', 'details' => $e->getMessage()], 500);
        }
    }

}
