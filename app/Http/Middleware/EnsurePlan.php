<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlan
{
    private const PLAN_HIERARCHY = ['free' => 0, 'pro' => 1, 'enterprise' => 2];

    public function handle(Request $request, Closure $next, string ...$plans): Response
    {
        $user     = $request->user();
        $userPlan = $user?->plan ?? 'free';
        $userRank = self::PLAN_HIERARCHY[$userPlan] ?? 0;

        foreach ($plans as $required) {
            $requiredRank = self::PLAN_HIERARCHY[$required] ?? 0;
            if ($userRank >= $requiredRank) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'This feature requires a ' . implode(' or ', $plans) . ' plan.',
            ], 403);
        }

        return redirect()->route('dashboard')
            ->with('error', 'This feature requires a ' . implode(' or ', $plans) . ' plan.');
    }
}
