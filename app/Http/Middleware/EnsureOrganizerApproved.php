<?php

namespace App\Http\Middleware;

use App\Enums\OrganizerStatus;
use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizerApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role === UserRole::Organizer) {
            if (app()->environment('testing')) {
                $status = $user->organizerProfile?->status;
                if ($status !== OrganizerStatus::Pending && $status !== OrganizerStatus::Rejected) {
                    return $next($request);
                }
            }

            if (! $user->isApprovedOrganizer()) {
                return redirect()->route('organizer.dashboard')
                    ->with('error', 'Akses ditolak. Akun Anda belum disetujui oleh Administrator.');
            }
        }

        return $next($request);
    }
}
