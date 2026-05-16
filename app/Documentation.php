<?php

namespace App;

use OpenApi\Attributes as OA;

#[OA\OpenApi(
    openapi: "3.1.0",
    info: new OA\Info(
        version: "1.0.0",
        title: "Logger API",
        description: "API for PC activity recording and synchronization."
    ),
    servers: [new OA\Server(url: "/api")],
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
class Documentation {}
