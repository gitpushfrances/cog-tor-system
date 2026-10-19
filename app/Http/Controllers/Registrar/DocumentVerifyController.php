<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Support\DocumentSeal;
use Illuminate\Http\Request;

/** Verify Document (client B4): checks the QR / verify code printed on a TOR. */
class DocumentVerifyController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'q'    => 'nullable|string|max:1000',
            'code' => 'nullable|string|max:20',
        ]);

        $result = null;
        if ($request->filled('q')) {
            [$number, $code] = DocumentSeal::parse($request->input('q'), $request->input('code'));
            $result = DocumentSeal::verify($number, $code) + ['number' => $number];

            activity('document')
                ->causedBy($request->user())
                ->event('verified')
                ->withProperties(['document_number' => $number, 'result' => $result['status']])
                ->log("Verified {$number}: {$result['status']}");
        }

        return view('registrar.verify-document', compact('result'));
    }
}
