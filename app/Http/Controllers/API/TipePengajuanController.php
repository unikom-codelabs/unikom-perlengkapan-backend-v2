<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use OpenApi\Attributes as OA;

class PengajuanController extends Controller
{

    #[OA\Get(
        path: "/api/pengajuan",
        tags: ["Pengajuan"],
        summary: "List master tipe pengajuan",
        security: [["bearerAuth" => []]],
        responses: [

            new OA\Response(
                response: 200,
                description: "Berhasil ambil data pengajuan"
            )
        ]
    )]
    public function index()
    {

        return response()->json(

            Pengajuan::all()
        );
    }
}