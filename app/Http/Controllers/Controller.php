<?php

namespace App\Http\Controllers;

abstract class Controller
{
    //
}

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Logger Backend API",
 *      description="API documentation for Logger-Backend client-server system to track PC activity",
 * )
 *
 * @OA\Server(
 *      url="/",
 *      description="Logger API Server"
 * )
 *
 * @OA\SecurityScheme(
 *      securityScheme="bearerAuth",
 *      type="http",
 *      scheme="bearer",
 *      bearerFormat="JWT",
 *      description="Enter JWT Bearer token to access protected API endpoints"
 * )
 */
