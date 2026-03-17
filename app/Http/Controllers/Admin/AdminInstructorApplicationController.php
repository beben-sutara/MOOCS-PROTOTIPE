<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InstructorApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminInstructorApplicationController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', ''));

        $applicationsQuery = InstructorApplication::query()
            ->with(['user', 'reviewer'])
            ->latest();

        if ($search !== '') {
            $applicationsQuery->where(function ($query) use ($search) {
                $query->where('expertise', 'like', '%' . $search . '%')
                    ->orWhere('motivation', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
            });
        }

        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $applicationsQuery->where('status', $status);
        }

        return view('admin.instructor-applications.index', [
            'applications' => $applicationsQuery->limit(50)->get(),
            'search' => $search,
            'status' => $status,
            'pendingCount' => InstructorApplication::where('status', 'pending')->count(),
            'approvedCount' => InstructorApplication::where('status', 'approved')->count(),
            'rejectedCount' => InstructorApplication::where('status', 'rejected')->count(),
        ]);
    }

    public function update(Request $request, InstructorApplication $instructorApplication)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($instructorApplication->status !== 'pending') {
            return redirect()->route('admin.instructor-applications.index')
                ->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        if ($validated['status'] === 'rejected' && blank($validated['admin_notes'] ?? null)) {
            return back()
                ->withErrors(['admin_notes' => 'Catatan admin wajib diisi saat menolak pengajuan.'])
                ->withInput();
        }

        DB::transaction(function () use ($request, $instructorApplication, $validated) {
            $instructorApplication->update([
                'status' => $validated['status'],
                'admin_notes' => $validated['admin_notes'] ?? null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            if ($validated['status'] === 'approved' && $instructorApplication->user->role === 'user') {
                $instructorApplication->user->update([
                    'role' => 'instructor',
                ]);
            }
        });

        return redirect()->route('admin.instructor-applications.index')
            ->with('success', $validated['status'] === 'approved'
                ? 'Pengajuan instructor berhasil disetujui.'
                : 'Pengajuan instructor berhasil ditolak.');
    }
}
