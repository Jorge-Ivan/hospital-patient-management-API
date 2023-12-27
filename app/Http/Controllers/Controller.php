<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Hospital patient management API",
 *     version="1.0.0",
 *     description="REST API with PHP for hospital patient management, which will allow hospital doctors to search for a patient, create new patients and add diagnoses to patients.",
 *     @OA\Contact(
 *         url="https://www.linkedin.com/in/jorgecarrillog/",
 *         name="Jorge Ivan Carrillo"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\SecurityScheme(
 *     type="http",
 *     description="Bearer Token Authentication",
 *     name="Authorization",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="bearerAuth"
 * )
 */

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
