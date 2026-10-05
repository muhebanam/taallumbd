<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EventTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventIngestionController extends Controller
{
    public function __construct(
        protected EventTracker $tracker
    ) {}

    /**
     * Handle incoming learning event or batch of learning events.
     */
    public function store(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $sessionId = $request->input('session_id') ?: ($request->hasSession() ? $request->session()->getId() : null);

        // Check if payload is a batch of events
        if ($request->has('events') && is_array($request->input('events'))) {
            $rawEvents = $request->input('events');
            $validatedEvents = [];

            foreach ($rawEvents as $raw) {
                if (! is_array($raw) || empty($raw['event_type'])) {
                    continue;
                }

                $validatedEvents[] = [
                    'user_id' => $userId ?: ($raw['user_id'] ?? null),
                    'event_type' => (string) $raw['event_type'],
                    'subject_type' => $raw['subject_type'] ?? null,
                    'subject_id' => isset($raw['subject_id']) ? (int) $raw['subject_id'] : null,
                    'course_id' => isset($raw['course_id']) ? (int) $raw['course_id'] : null,
                    'properties' => is_array($raw['properties'] ?? null) ? $raw['properties'] : [],
                    'session_id' => $raw['session_id'] ?? $sessionId,
                    'occurred_at' => $raw['occurred_at'] ?? now()->format('Y-m-d H:i:s'),
                ];
            }

            if (! empty($validatedEvents)) {
                $this->tracker->trackBatch($validatedEvents);
            }

            return response()->json([
                'success' => true,
                'count' => count($validatedEvents),
            ], 200);
        }

        // Single event payload
        $validated = $request->validate([
            'event_type' => 'required|string|max:64',
            'subject_type' => 'nullable|string|max:100',
            'subject_id' => 'nullable|integer',
            'course_id' => 'nullable|integer',
            'properties' => 'nullable|array',
            'session_id' => 'nullable|string|max:64',
            'occurred_at' => 'nullable|date',
        ]);

        $this->tracker->track(
            eventType: $validated['event_type'],
            userId: $userId,
            properties: $validated['properties'] ?? [],
            subjectType: $validated['subject_type'] ?? null,
            subjectId: $validated['subject_id'] ?? null,
            courseId: $validated['course_id'] ?? null,
            sessionId: $validated['session_id'] ?? $sessionId,
            occurredAt: isset($validated['occurred_at']) ? new \DateTime($validated['occurred_at']) : null
        );

        return response()->json([
            'success' => true,
            'count' => 1,
        ], 200);
    }
}
