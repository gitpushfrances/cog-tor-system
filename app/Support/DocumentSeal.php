<?php

namespace App\Support;

use App\Models\TorRecord;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * TOR seal (client B4): a QR on every TOR page holding the document details and a verify code.
 * The code is an HMAC of the record's fixed facts (document number, student id, generation time)
 * keyed with APP_KEY, so it cannot be produced without the server. Works fully offline:
 * any phone camera shows the text; the registrar checks it on Verify Document.
 *
 * Keep the same APP_KEY (.env) on the defense PC: a different key makes old codes fail.
 */
class DocumentSeal
{
    /** "A1B2C-3D4E5": 10 hex characters of HMAC-SHA256, upper case. */
    public static function code(TorRecord $tor): string
    {
        $facts = implode('|', ['TOR', $tor->document_number, $tor->student_id, $tor->generated_at?->format('Y-m-d H:i:s')]);
        $hex = strtoupper(substr(hash_hmac('sha256', $facts, (string) config('app.key')), 0, 10));

        return substr($hex, 0, 5) . '-' . substr($hex, 5);
    }

    /** Plain text inside the QR (readable by any phone camera, no internet needed). */
    public static function payload(TorRecord $tor): string
    {
        $student = $tor->student;

        // Kept short so the printed QR has large dots and scans easily.
        return implode("\n", [
            'ESSU Guiuan TOR',
            'Doc No: ' . $tor->document_number,
            'Student: ' . ($student ? $student->student_number . ' ' . strtoupper($student->last_name) . ', ' . $student->first_name : ''),
            'Issued: ' . $tor->generated_at?->format('Y-m-d'),
            'Verify code: ' . self::code($tor),
        ]);
    }

    /** QR as an SVG data URI (no GD/Imagick needed; DomPDF renders it). */
    public static function qrDataUri(TorRecord $tor): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle(240, 0), new SvgImageBackEnd()));

        return 'data:image/svg+xml;base64,' . base64_encode($writer->writeString(self::payload($tor)));
    }

    /** Pulls "Doc No" and "Verify code" out of scanned QR text, or takes them as typed. */
    public static function parse(?string $input, ?string $code = null): array
    {
        $input = trim((string) $input);
        $number = preg_match('/Doc\s*No:\s*(\S+)/i', $input, $m) ? $m[1] : $input;
        if (! $code && preg_match('/Verify\s*code:\s*([A-F0-9]{5}-?[A-F0-9]{5})/i', $input, $m)) {
            $code = $m[1];
        }

        return [strtoupper(trim($number)), self::normalizeCode($code)];
    }

    /**
     * ['status' => not_found | mismatch | current | superseded, 'tor' => ?TorRecord, 'newer' => ?TorRecord]
     */
    public static function verify(string $number, ?string $code): array
    {
        $tor = TorRecord::with('student')->where('document_number', $number)->first();
        if (! $tor) {
            return ['status' => 'not_found', 'tor' => null, 'newer' => null];
        }
        if (! $code || ! hash_equals(self::code($tor), $code)) {
            return ['status' => 'mismatch', 'tor' => $tor, 'newer' => null];
        }
        if ($tor->is_current) {
            return ['status' => 'current', 'tor' => $tor, 'newer' => null];
        }

        $newer = TorRecord::where('student_id', $tor->student_id)->where('is_current', true)->first();

        return ['status' => 'superseded', 'tor' => $tor, 'newer' => $newer];
    }

    private static function normalizeCode(?string $code): ?string
    {
        $hex = strtoupper(preg_replace('/[^A-Fa-f0-9]/', '', (string) $code));

        return strlen($hex) === 10 ? substr($hex, 0, 5) . '-' . substr($hex, 5) : null;
    }
}
