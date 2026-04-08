<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;

class SwaggerTestController extends Controller
{
    #[OA\Get(
        path: "/api/swagger-test",
        tags: ["Test"],
        summary: "Test swagger",
        responses: [
            new OA\Response(
                response: 200,
                description: "OK"
            )
        ]
    )]
    public function index()
    {
        return response()->json([
            "message" => "swagger jalan"
        ]);
    }
}
