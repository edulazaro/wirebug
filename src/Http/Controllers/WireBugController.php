<?php

namespace EduLazaro\WireBug\Http\Controllers;

use EduLazaro\WireBug\Models\BugReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WireBugController
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type'       => ['required', 'string', Rule::in(array_keys(config('wirebug.types', [])))],
            'message'    => ['required', 'string', 'max:5000'],
            'steps'      => ['nullable', 'string', 'max:5000'],
            'email'      => ['nullable', 'email', 'max:255'],
            'url'        => ['nullable', 'string', 'max:2048'],
            'viewport'   => ['nullable', 'string', 'max:32'],
            // Sin svg (vector de XSS si algún día se sirve por URL pública).
            'screenshot' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,gif,webp',
                'max:' . (int) config('wirebug.uploads.max_kb', 5120),
            ],
            'recording' => [
                'nullable',
                'file',
                'mimes:webm,mp4',
                'max:' . (int) config('wirebug.recording.max_kb', 25600),
            ],
        ]);

        $capture = config('wirebug.capture_context', true);

        $screenshotPath = null;
        $recordingPath = null;

        if ($request->hasFile('screenshot')) {
            $screenshotPath = $request->file('screenshot')->store(
                config('wirebug.uploads.path', 'wirebug'),
                config('wirebug.uploads.disk', 'local')
            );
        }

        if ($request->hasFile('recording')) {
            $recordingPath = $request->file('recording')->store(
                config('wirebug.uploads.path', 'wirebug'),
                config('wirebug.uploads.disk', 'local')
            );
        }

        $report = BugReport::create([
            'user_id'         => $request->user()?->getAuthIdentifier(),
            'type'            => $validated['type'],
            'message'         => $validated['message'],
            'steps'           => $validated['steps'] ?? null,
            'email'           => $validated['email'] ?? null,
            'screenshot_path' => $screenshotPath,
            'recording_path'  => $recordingPath,
            'url'             => $capture ? ($validated['url'] ?? null) : null,
            'user_agent'      => $capture ? substr((string) $request->userAgent(), 0, 512) : null,
            'locale'          => $capture ? app()->getLocale() : null,
            'meta'            => $capture ? array_filter([
                'viewport' => $validated['viewport'] ?? null,
                'referer'  => $request->headers->get('referer'),
            ]) : null,
        ]);

        return response()->json(['ok' => true, 'id' => $report->id], 201);
    }
}
