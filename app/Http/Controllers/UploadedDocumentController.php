<?php

namespace App\Http\Controllers;

use App\Models\UploadedDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class UploadedDocumentController extends Controller
{
    private const MAX_UPLOAD_KB = 102400;

    public function store(Request $request): RedirectResponse
    {
        if (! session('hr_logged_in')) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:150'],
            'storage_mode' => ['required', 'in:database,local'],
            'document' => ['required', 'file', 'max:'.self::MAX_UPLOAD_KB],
        ], [
            'document.required' => 'Pilih file yang ingin diupload.',
            'document.max' => 'Ukuran file maksimal 100 MB.',
            'storage_mode.in' => 'Mode penyimpanan tidak valid.',
        ]);

        $file = $request->file('document');
        $originalName = $file->getClientOriginalName();
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $mode = $validated['storage_mode'];

        $document = [
            'title' => $validated['title'] ?? null,
            'original_name' => $originalName,
            'stored_name' => pathinfo($originalName, PATHINFO_FILENAME),
            'mime_type' => $file->getMimeType(),
            'extension' => $extension,
            'size' => $file->getSize() ?: 0,
            'storage_mode' => $mode,
            'uploaded_by' => session('hr_username', 'admin'),
        ];

        try {
            if ($mode === UploadedDocument::MODE_LOCAL) {
                $storedName = Str::uuid()->toString().($extension ? '.'.$extension : '');
                $document['disk'] = 'local';
                $document['stored_name'] = $storedName;
                $document['storage_path'] = $file->storeAs('uploads/documents', $storedName, 'local');
            } else {
                $document['disk'] = 'database';
                $document['file_data'] = base64_encode((string) file_get_contents($file->getRealPath()));
            }

            UploadedDocument::create($document);
        } catch (\Throwable) {
            if (($document['disk'] ?? null) === 'local' && isset($document['storage_path'])) {
                Storage::disk('local')->delete($document['storage_path']);
            }

            return back()
                ->withErrors(['document' => 'Upload gagal. Pastikan database sudah dimigrate dan koneksi database aktif.'])
                ->withInput();
        }

        return back()->with('document_status', 'File berhasil diupload dan siap didownload.');
    }

    public function download(UploadedDocument $uploadedDocument): Response
    {
        if (! session('hr_logged_in')) {
            return redirect()->route('login');
        }

        $downloadName = $uploadedDocument->original_name;
        $headers = [
            'Content-Type' => $uploadedDocument->mime_type ?: 'application/octet-stream',
        ];

        if ($uploadedDocument->isStoredLocally()) {
            if (! $uploadedDocument->storage_path || ! Storage::disk('local')->exists($uploadedDocument->storage_path)) {
                abort(404, 'File lokal tidak ditemukan.');
            }

            return Storage::disk('local')->download($uploadedDocument->storage_path, $downloadName, $headers);
        }

        $contents = base64_decode((string) $uploadedDocument->file_data, true);

        if ($contents === false) {
            abort(404, 'Data file di database tidak valid.');
        }

        return response($contents, 200, array_merge($headers, [
            'Content-Disposition' => 'attachment; filename="'.$this->fallbackAsciiName($downloadName).'"',
            'Content-Length' => (string) strlen($contents),
        ]));
    }

    public function destroy(UploadedDocument $uploadedDocument): RedirectResponse
    {
        if (! session('hr_logged_in')) {
            return redirect()->route('login');
        }

        if ($uploadedDocument->isStoredLocally() && $uploadedDocument->storage_path) {
            Storage::disk('local')->delete($uploadedDocument->storage_path);
        }

        $uploadedDocument->delete();

        return back()->with('document_status', 'File berhasil dihapus.');
    }

    private function fallbackAsciiName(string $fileName): string
    {
        $name = Str::ascii($fileName);
        $name = str_replace(['"', '\\', '/'], '', $name);

        return $name !== '' ? $name : 'download';
    }
}
