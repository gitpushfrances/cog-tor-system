<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class BackupController extends Controller
{
    public function index()
    {
        $backupPath = storage_path('app/cog-tor-backup');
        $backups = [];
        if (is_dir($backupPath)) {
            $files = glob($backupPath . '/*.zip');
            rsort($files);
            foreach ($files as $file) {
                $backups[] = [
                    'name'    => basename($file),
                    'size'    => round(filesize($file) / 1048576, 2) . ' MB',
                    'created' => date('F d, Y h:i A', filemtime($file)),
                ];
            }
        }
        return view('admin.backup.index', compact('backups'));
    }

    public function run()
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(120)->get('http://localhost/cog-tor-backup-trigger/run-backup.php');
            $result = $response->json();

            \Log::info('Backup via Apache trigger result', [
                'http_status' => $response->status(),
                'raw_body'    => $response->body(),
                'parsed'      => $result,
            ]);

            if (! is_array($result) || empty($result['success'])) {
                $errorDetail = is_array($result) ? ($result['error'] ?? 'Unknown error') : 'Invalid response: ' . $response->body();
                return back()->with('error', 'Backup failed: ' . $errorDetail);
            }

            return back()->with('success', 'Backup completed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function download($filename)
    {
        $path = storage_path('app/cog-tor-backup/' . $filename);
        if (file_exists($path) === false) {
            abort(404);
        }
        return response()->download($path);
    }

    public function delete($filename)
    {
        $path = storage_path('app/cog-tor-backup/' . $filename);
        if (file_exists($path) === true) {
            unlink($path);
        }
        return back()->with('success', 'Backup deleted.');
    }

    public function restore(Request $request)
    {
        $request->validate([
            'sql_file' => 'required|file|mimes:sql,txt,zip|max:102400',
        ]);

        $file = $request->file('sql_file');

        try {
            $sql = strtolower($file->getClientOriginalExtension()) === 'zip'
                ? $this->extractSqlFromZip($file->getRealPath())
                : file_get_contents($file->getRealPath());

            if (! is_string($sql) || stripos($sql, 'CREATE TABLE') === false) {
                return back()->with('error', 'Restore failed: no valid database dump was found in the uploaded file.');
            }

            DB::unprepared($sql);
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);

            Log::info('Database restored', [
                'user_id' => auth()->id(),
                'file'    => $file->getClientOriginalName(),
            ]);

            return back()->with('success', 'Database restored successfully.');
        } catch (\Throwable $e) {
            Log::error('Database restore failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    private function extractSqlFromZip(string $path): ?string
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return null;
        }

        $sql = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = str_replace('\\', '/', $zip->getNameIndex($i));
            if (str_contains($name, 'db-dumps/') && str_ends_with(strtolower($name), '.sql')) {
                $content = $zip->getFromIndex($i);
                $sql = $content === false ? null : $content;
                break;
            }
        }

        $zip->close();
        return $sql;
    }
}
