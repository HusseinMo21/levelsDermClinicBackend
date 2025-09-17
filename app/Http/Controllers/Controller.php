<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Levels Derm Clinic API",
 *     version="1.0.0",
 *     description="API for cosmetic clinic management system with Arabic support",
 *     @OA\Contact(
 *         email="admin@levelsderm.com"
 *     )
 * )
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter token in format: Bearer {token}"
 * )
 * 
 * @OA\Tag(
 *     name="Patients",
 *     description="Patient management endpoints"
 * )
 * 
 * @OA\Tag(
 *     name="Appointments", 
 *     description="Appointment management endpoints"
 * )
 * 
 * @OA\Tag(
 *     name="Services",
 *     description="Service management endpoints"
 * )
 * 
 * @OA\Tag(
 *     name="Payments",
 *     description="Payment management endpoints"
 * )
 * 
 * @OA\Tag(
 *     name="Dashboard",
 *     description="Dashboard statistics and KPIs"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}