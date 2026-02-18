<?php

namespace App\Http\Controllers;

use App\Models\InstrumentCertificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificateDownloadController extends Controller
{
    public function __invoke(Request $request, InstrumentCertificate $certificate)
    {
        // Zahteva prijavo (auth middleware)
        if (! Storage::disk('local')->exists($certificate->certificate_path)) {
            abort(404, 'Certifikat ni najden.');
        }

        $filename = basename($certificate->certificate_path);

        return Storage::disk('local')->download(
            $certificate->certificate_path,
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
}
