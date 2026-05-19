<?php

namespace App\Http\Controllers;

use App\Models\FormSubmission;
use Illuminate\Http\Request;

class CustomerTrackingController extends Controller
{
    public function show(Request $request, string $token)
    {
        $submission = FormSubmission::query()
            ->where('tracking_token', $token)
            ->with(['store', 'events' => fn ($q) => $q->where('is_visible_to_customer', true)->orderBy('occurred_at')])
            ->firstOrFail();

        return view('tracking.show', [
            'submission' => $submission,
            'store'      => $submission->store,
            'events'     => $submission->events,
        ]);
    }
}
