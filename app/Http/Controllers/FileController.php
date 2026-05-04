<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Attachment;

class FileController extends Controller
{
    public function serveSecureFile($filepath)
    {
        $user = Auth::user();

        // 1. Directory Traversal Protection
        if (str_contains($filepath, '..')) {
            abort(403, 'Invalid file path.');
        }

        // 2. Authorization Rules (The Laravel Way)
        if ($user->role !== 'admin') {
            // Check kung kanya ang Profile ID o Selfie
            $ownsProfileFile = User::where('id', $user->id)
                ->where(function($query) use ($filepath) {
                    $query->where('id_photo_path', $filepath)
                          ->orWhere('selfie_photo_path', $filepath);
                })->exists();

            // Check kung kanya ang uploaded requirement sa Service Request
            $ownsAttachment = Attachment::where('file_path', $filepath)
                ->whereHas('serviceRequest', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->exists();

            if (!$ownsProfileFile && !$ownsAttachment) {
                abort(403, 'Unauthorized Access to PII. Bawal tingnan ang file ng iba.');
            }
        }

        // 3. File Verification & Delivery
        if (!Storage::disk('local')->exists($filepath)) {
            abort(404, 'File not found sa Deep Storage.');
        }

        // 4. Performance: I-cache sa browser ng user ng 1 linggo (604800 seconds)
        return response()->file(Storage::disk('local')->path($filepath), [
            'Cache-Control' => 'private, max-age=604800',
        ]);
    }
}
