<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Patient",
 *     title="Patient",
 *     required={"document", "first_name", "last_name", "birth_date", "email", "phone", "genre"},
 *     @OA\Property(property="id", type="integer", format="int64", description="Identificator in system", readOnly=true),
 *     @OA\Property(property="document", type="integer", format="int64", description="Identification document", maxLength=20, uniqueItems=true),
 *     @OA\Property(property="first_name", type="string", description="Patient first name", maxLength=255),
 *     @OA\Property(property="last_name", type="string", description="Patient last name", maxLength=255),
 *     @OA\Property(property="birth_date", type="string", format="date", description="Patient birthday"),
 *     @OA\Property(property="email", type="string", format="email", description="Contact email", maxLength=255, uniqueItems=true),
 *     @OA\Property(property="phone", type="string", description="Contact phone", maxLength=20),
 *     @OA\Property(property="genre", type="string", description="Patient genre (Male/Female)", enum={"Male", "Female"}),
 *     example={
 *         "document": 12345678901234567890,
 *         "first_name": "John",
 *         "last_name": "Doe",
 *         "birth_date": "1990-01-01",
 *         "email": "johndoe@example.com",
 *         "phone": 1234567890,
 *         "genre": "Male"
 *     }
 * )
 * 
 * @OA\Schema(
 *     schema="PatientWithDiagnoses",
 *     title="Patient with Diagnoses including Pivot Data",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Patient"),
 *         @OA\Schema(
 *             required={"diagnoses"},
 *             @OA\Property(
 *                 property="diagnoses",
 *                 type="array",
 *                 @OA\Items(
 *                     allOf={
 *                         @OA\Schema(ref="#/components/schemas/Diagnosis"),
 *                         @OA\Schema(
 *                             @OA\Property(property="pivot", ref="#/components/schemas/DiagnosisPivot")
 *                         )
 *                     }
 *                 )
 *             ),
 *     example={
 *         "document": 12345678901234567890,
 *         "first_name": "John",
 *         "last_name": "Doe",
 *         "birth_date": "1990-01-01",
 *         "email": "johndoe@example.com",
 *         "phone": 1234567890,
 *         "genre": "Male",
 *         "diagnoses": {
 *             {
 *                "name": "Diabetes",
 *                "description": "Type 2 diabetes mellitus",
 *                "pivot":
 *                {
 *                   "creation_date": "1990-01-01",
 *                   "observation": "Type 2 diabetes mellitus"
 *                }
 *             }
 *         }
 *     }
 *         )
 *     }
 * )
 */
class Patient extends Model
{
    use HasFactory;

    protected $fillable = ['document', 'first_name', 'last_name', 'birth_date', 'email', 'phone', 'genre'];
    protected $hidden = ['created_at', 'updated_at'];

    public function diagnoses()
    {
        return $this->belongsToMany(Diagnosis::class, 'patient_diagnosis')
            ->withPivot('observation', 'creation_date');
    }
}
