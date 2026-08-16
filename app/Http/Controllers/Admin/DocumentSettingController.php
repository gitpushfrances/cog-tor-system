<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentSetting;
use Illuminate\Http\Request;

class DocumentSettingController extends Controller
{
    public function edit()
    {
        $settings = DocumentSetting::current();

        return view('admin.document-settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'registrar_name'         => 'required|string|max:255',
            'registrar_credentials'  => 'nullable|string|max:255',
            'registrar_title'        => 'required|string|max:255',
            'prepared_by_name'       => 'nullable|string|max:255',
            'prepared_by_title'      => 'nullable|string|max:255',
            'campus_admin_name'      => 'nullable|string|max:255',
            'campus_admin_title'     => 'nullable|string|max:255',
        ]);

        $settings = DocumentSetting::first();

        if ($settings) {
            $settings->update($validated);
        } else {
            DocumentSetting::create($validated);
        }

        return redirect()->route('admin.document-settings.edit')
            ->with('success', 'Document signatory settings updated successfully.');
    }
}
