<?php

namespace App\Http\Controllers;

use App\Http\Requests\BidRequest;
use App\Models\Bid;
use App\Models\Project;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BidController extends Controller
{
    /** CREATE: developer mengajukan penawaran */
    public function store(BidRequest $request, Project $project): RedirectResponse
    {
        if (! $project->isOpen()) {
            return back()->with('err', 'Lelang sudah ditutup; penawaran tidak dapat diajukan.');
        }

        $bid = new Bid($request->validated());
        $bid->project()->associate($project);
        $bid->developer()->associate($request->user());

        try {
            $bid->save();
        } catch (UniqueConstraintViolationException) {
            return back()->with('err', 'Anda sudah mengajukan penawaran pada proyek ini.');
        }

        return redirect()->route('projects.show', $project)->with('ok', 'Penawaran berhasil dikirim.');
    }

    public function edit(Bid $bid): View
    {
        Gate::authorize('update', $bid);

        return view('bids.edit', ['bid' => $bid->load('project')]);
    }

    /** UPDATE: developer merevisi penawarannya */
    public function update(BidRequest $request, Bid $bid): RedirectResponse
    {
        Gate::authorize('update', $bid);

        $bid->update($request->validated());

        return redirect()->route('projects.show', $bid->project_id)->with('ok', 'Penawaran diperbarui.');
    }

    /** DELETE: developer menarik penawarannya */
    public function destroy(Bid $bid): RedirectResponse
    {
        Gate::authorize('delete', $bid);

        $projectId = $bid->project_id;
        $bid->delete();

        return redirect()->route('projects.show', $projectId)->with('ok', 'Penawaran ditarik.');
    }

    /** Klien memilih pemenang lalu lelang ditutup */
    public function pick(Project $project, Bid $bid): RedirectResponse
    {
        Gate::authorize('pickWinner', $project);
        abort_unless($bid->project_id === $project->id, 404);

        $error = DB::transaction(function () use ($project, $bid) {
            // Kunci baris proyek agar dua request bersamaan tidak menghasilkan dua pemenang
            $locked = Project::whereKey($project->id)->lockForUpdate()->first();
            if ($locked->status !== Project::STATUS_OPEN) {
                return 'Pemenang sudah dipilih atau lelang sudah ditutup.';
            }

            // is_winner & status tidak fillable, jadi diisi dengan forceFill
            $bid->forceFill(['is_winner' => true])->save();
            $locked->forceFill(['status' => Project::STATUS_CLOSED])->save();

            return null;
        });

        return redirect()->route('projects.show', $project)
            ->with($error ? 'err' : 'ok', $error ?? 'Pemenang berhasil dipilih.');
    }
}
