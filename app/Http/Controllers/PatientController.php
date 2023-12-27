<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Log;

class PatientController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/patients",
     *     operationId="getPatientsWithDiagnoses",
     *     tags={"Patients"},
     *     summary="Get a list of patients with their diagnoses",
     *     description="Retrieves a list of patients along with their associated diagnoses.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="patients", type="array", @OA\Items(ref="#/components/schemas/PatientWithDiagnoses"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Error al obtener la lista de pacientes con diagnósticos."),
     *             @OA\Property(property="details", type="string", example="Error details")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function index()
    {
        try {
            $patients = Patient::with('diagnoses')->get();

            return response()->json(['patients' => $patients], 200);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Error al obtener la lista de pacientes con diagnósticos.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/patients/search",
     *     operationId="searchPatients",
     *     tags={"Patients"},
     *     summary="Search for patients by name, last name, or document number",
     *     description="Searches for patients by their first name, last name, or document number.",
     *     @OA\Parameter(
     *         name="search_query",
     *         in="query",
     *         required=true,
     *         description="Search query for patient's name, last name, or document number",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="patients", type="array", @OA\Items(ref="#/components/schemas/Patient"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Error al buscar pacientes."),
     *             @OA\Property(property="details", type="string", example="Error details")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function searchPatients(Request $request)
    {
        try {
            $searchQuery = $request->input('search_query');

            $patients = Patient::where('first_name', 'LIKE', "%$searchQuery%")
                ->orWhere('last_name', 'LIKE', "%$searchQuery%")
                ->orWhere('document', 'LIKE', "%$searchQuery%")
                ->get();

            return response()->json(['patients' => $patients], 200);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Error al buscar pacientes.', 'details' => $e->getMessage()], 500);
        }
    }


    /**
     * @OA\Post(
     *     path="/api/patients",
     *     operationId="createPatient",
     *     tags={"Patients"},
     *     summary="Create a new patient",
     *     description="Registers a new patient in the system.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Patient data",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/Patient")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Patient")
     *     ),
     *     @OA\Response(
     *         response=419,
     *         description="Validation error or Patient already exists",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="El paciente ya está registrado.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Error al intentar registrar el paciente, contacte al administrador."),
     *             @OA\Property(property="details", type="string", example="Error details")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'document' => 'required|integer|digits_between:1,20|unique:patients',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'birth_date' => 'required|date',
                'email' => 'required|string|email|max:255|unique:patients',
                'phone' => 'required|integer|digits_between:1,20',
                'genre' => 'required|in:Male,Female',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 419);
            }

            // Verificar si el paciente ya está registrado
            $existingPatient = Patient::where('document', $request->input('document'))->first();
            if ($existingPatient) {
                return response()->json(['error' => 'El paciente ya está registrado.'], 419);
            }

            // Crear un nuevo paciente si pasa todas las validaciones
            $newPatient = Patient::create([
                'document' => $request->input('document'),
                'first_name' => $request->input('first_name'),
                'last_name' => $request->input('last_name'),
                'birth_date' => $request->input('birth_date'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'genre' => $request->input('genre'),
            ]);

            return response()->json(['success' => 'Paciente registrado exitosamente.', 'patient' => $newPatient], 200);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Error al intentar registrar el paciente, contacte al administrador.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/patients/{id}",
     *     operationId="updatePatient",
     *     tags={"Patients"},
     *     summary="Update an existing patient",
     *     description="Update the information of an existing patient in the system.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the patient to be updated",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Patient data",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/Patient")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Patient")
     *     ),
     *     @OA\Response(
     *         response=419,
     *         description="Validation error or Patient not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="El paciente no fue encontrado.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Error al actualizar la información del paciente."),
     *             @OA\Property(property="details", type="string", example="Error details")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $patient = Patient::find($id);

            if (!$patient) {
                return response()->json(['error' => 'El paciente no fue encontrado.'], 419);
            }

            $validator = Validator::make($request->all(), [
                'document' => 'required|integer|digits_between:1,20|unique:patients,document,'.$id,
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'birth_date' => 'required|date',
                'email' => 'required|string|email|max:255|unique:patients,email,'.$id,
                'phone' => 'required|integer|digits_between:1,20',
                'genre' => 'required|in:Male,Female',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 419);
            }

            // Actualizar la información del paciente
            $patient->update([
                'document' => $request->input('document'),
                'first_name' => $request->input('first_name'),
                'last_name' => $request->input('last_name'),
                'birth_date' => $request->input('birth_date'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'genre' => $request->input('genre'),
            ]);

            return response()->json(['success' => 'Información del paciente actualizada.', 'patient' => $patient], 200);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Error al actualizar la información del paciente.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/patients/{id}",
     *     operationId="deletePatient",
     *     tags={"Patients"},
     *     summary="Delete a patient and associated diagnoses",
     *     description="Deletes a patient along with their associated diagnoses (if any) from the system.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the patient to be deleted",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="string", example="Paciente y sus diagnósticos (si existen) eliminados correctamente.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=419,
     *         description="Patient not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="El paciente no fue encontrado.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Error al eliminar el paciente y sus diagnósticos."),
     *             @OA\Property(property="details", type="string", example="Error details")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function destroy($id)
    {
        try {
            $patient = Patient::find($id);

            if (!$patient) {
                return response()->json(['error' => 'El paciente no fue encontrado.'], 419);
            }

            if ($patient->diagnoses()->count() > 0) {
                $patient->diagnoses()->detach();
            }

            $patient->delete();

            return response()->json(['success' => 'Paciente y sus diagnósticos (si existen) eliminados correctamente.'], 200);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Error al eliminar el paciente y sus diagnósticos.', 'details' => $e->getMessage()], 500);
        }
    }

}
