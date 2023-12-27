<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Diagnosis",
 *     title="Diagnosis",
 *     required={"name"},
 *     @OA\Property(property="id", type="integer", format="int64", description="Unique identifier for the diagnosis", readOnly=true),
 *     @OA\Property(property="name", type="string", description="Diagnostic name", maxLength=255),
 *     @OA\Property(property="description", type="string", description="Description of the diagnosis", maxLength=255),
 *     example={
 *         "name": "Diabetes",
 *         "description": "Type 2 diabetes mellitus"
 *     }
 * )
 * 
 * @OA\Schema(
 *     schema="DiagnosisPivot",
 *     title="Diagnosis Pivot Data",
 *     required={"patient_id", "diagnosis_id", "observation", "creation_date"},
 *     @OA\Property(property="patient_id", type="integer", format="int64", description="ID of the patient"),
 *     @OA\Property(property="diagnosis_id", type="integer", format="int64", description="ID of the diagnosis"),
 *     @OA\Property(property="observation", type="string", description="Observation related to the diagnosis"),
 *     @OA\Property(property="creation_date", type="string", format="date-time", description="Date of creation of the pivot record"),
 *     example={
 *         "creation_date": "1990-01-01",
 *         "observation": "Type 2 diabetes mellitus",
 *         "diagnosis_id": 1234,
 *         "patient_id": 456
 *     }
 * )
 */


class Diagnosis extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];
    protected $hidden = ['created_at', 'updated_at'];

    public function patients()
    {
        return $this->belongsToMany(Patient::class, 'patient_diagnosis')
            ->withPivot('observation', 'creation_date');
    }
}
