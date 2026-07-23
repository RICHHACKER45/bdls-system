<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),

            // FIX SA BUG 3: Ibigay ang CSRF Token para makapag-pasa sa mga Iframe Forms
            'csrf_token' => csrf_token(),

            // FIX SA BUG 2 at 4: Ibigay ang mga Flash Messages at Walk-in Data sa React
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'success_message' => fn () => $request->session()->get('success_message'),
                'walkin_searched' => fn () => $request->session()->get('walkin_searched'),
                'walkin_search_number' => fn () => $request->session()->get('walkin_search_number'),
                'walkin_user' => fn () => $request->session()->get('walkin_user'),
                'active_tab' => fn () => $request->session()->get('active_tab'),
            ],
        ];
    }
}
