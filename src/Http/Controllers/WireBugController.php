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
            'type'     => ['required', 'string', Rule::in(array_keys(config('wirebug.types', [])))],
            'message'  => ['required', 'string', 'max:5000'],
            'email'    => ['nullable', 'email', 'max:255'],
            'url'      => ['nullable', 'string', 'max:2048'],
            'viewport' => ['nullable', 'string', 'max:32'],
        ]);

        $capture = config('wirebug.capture_context', true);

        $report = BugReport::create([
            'user_id'    => $request->user()?->getAuthIdentifier(),
            'type'       => $validated['type'],
            'message'    => $validated['message'],
            'email'      => $validated['email'] ?? null,
            'url'        => $capture ? ($validated['url'] ?? null) : null,
            'user_agent' => $capture ? substr((string) $request->userAgent(), 0, 512) : null,
            'locale'     => $capture ? app()->getLocale() : null,
            'meta'       => $capture ? array_filter([
                'viewport' => $validated['viewport'] ?? null,
                'referer'  => $request->headers->get('referer'),
            ]) : null,
        ]);

        return response()->json(['ok' => true, 'id' => $report->id], 201);
    }
}
