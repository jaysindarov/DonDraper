<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $teams = $user->teams()->withCount('members')->get();

        return Inertia::render('Teams/Index', [
            'teams'       => $teams,
            'currentTeam' => $user->currentTeam,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $user = $request->user();

        if ($user->plan !== 'enterprise') {
            return back()->withErrors(['plan' => 'Team workspaces require an Enterprise plan.']);
        }

        $team = Team::create([
            'name'     => $validated['name'],
            'owner_id' => $user->id,
            'credits'  => 0,
            'plan'     => 'enterprise',
        ]);

        $team->members()->attach($user->id, ['role' => 'owner']);
        $user->update(['current_team_id' => $team->id]);

        return redirect()->route('teams.show', $team)->with('success', 'Team created!');
    }

    public function show(Team $team)
    {
        $this->authorizeTeamAccess($team);

        return Inertia::render('Teams/Show', [
            'team'        => $team->load('members', 'owner'),
            'invitations' => $team->invitations()->where('expires_at', '>', now())->get(),
        ]);
    }

    public function switch(Request $request, Team $team)
    {
        $this->authorizeTeamAccess($team);

        $request->user()->update(['current_team_id' => $team->id]);

        return back()->with('success', "Switched to {$team->name}.");
    }

    public function destroy(Team $team)
    {
        abort_unless(auth()->id() === $team->owner_id, 403);

        $team->delete();

        if (auth()->user()->current_team_id === $team->id) {
            auth()->user()->update(['current_team_id' => null]);
        }

        return redirect()->route('teams.index')->with('success', 'Team deleted.');
    }

    public function invite(Request $request, Team $team)
    {
        abort_unless(auth()->id() === $team->owner_id, 403);

        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        TeamInvitation::create([
            'team_id'    => $team->id,
            'email'      => $validated['email'],
            'token'      => Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);

        return back()->with('success', "Invitation sent to {$validated['email']}.");
    }

    public function acceptInvite(string $token)
    {
        $invitation = TeamInvitation::where('token', $token)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $user = auth()->user();

        if (!$invitation->team->members()->where('user_id', $user->id)->exists()) {
            $invitation->team->members()->attach($user->id, ['role' => 'member']);
        }

        $invitation->delete();

        return redirect()->route('teams.show', $invitation->team)
            ->with('success', "You joined {$invitation->team->name}!");
    }

    private function authorizeTeamAccess(Team $team): void
    {
        abort_unless(
            $team->members()->where('user_id', auth()->id())->exists(),
            403
        );
    }
}
