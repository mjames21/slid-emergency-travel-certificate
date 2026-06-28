<?php

namespace App\Http\Controllers;

use App\Models\Permit;
use App\Services\Documents\GeneratePermitPdfService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function permit(Permit $permit, GeneratePermitPdfService $service): Response
    {
        $this->authorize('print', $permit);

        $path = $service->handle($permit);

        return response(Storage::disk('local')->get($path), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$permit->permit_no.'.pdf"',
        ]);
    }
}
