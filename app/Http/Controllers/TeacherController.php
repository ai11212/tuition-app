<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Staff;

class TeacherController extends Controller
{
    /** Add Teacher form + list of all teachers */
    public function create()
    {
        $teachers = Staff::where('role', 'teacher')->orderByDesc('id')->get();
        return view('teachers.create', compact('teachers'));
    }

    /** Register a teacher (staff row with role=teacher) */
    public function store(Request $r)
    {
        $data = $r->validate([
            'name'           => 'required|string|max:255',
            'phone'          => 'required|string|max:50',
            'nationality'    => 'nullable|string|max:100',
            'address'        => 'nullable|string|max:190',
            'dbs'            => 'required|in:0,1',
            'dbs_file'       => 'nullable|file|mimes:pdf|max:5120',
            'hourly_rate'    => 'nullable|numeric|min:0',
            'joining_date'   => 'nullable|date',
            'leaving_date'   => 'nullable|date|after_or_equal:joining_date',
            'status'         => 'required|in:active,inactive',
            'reference_doc'  => 'required|in:0,1',
            'reference_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Uploaded documents (public disk writes straight into public/storage)
        $data['dbs_file'] = ($data['dbs'] == '1' && $r->hasFile('dbs_file'))
            ? $r->file('dbs_file')->store('teachers/dbs', 'public')
            : null;
        $data['reference_file'] = ($data['reference_doc'] == '1' && $r->hasFile('reference_file'))
            ? $r->file('reference_file')->store('teachers/references', 'public')
            : null;

        // Auto-generate reference T001, T002, ... (portable: no DB-specific SQL)
        $max = 0;
        foreach (Staff::where('reference', 'LIKE', 'T%')->pluck('reference') as $ref) {
            if (preg_match('/^T(\d+)$/', $ref, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }
        $data['reference'] = sprintf('T%03d', $max + 1);

        $data['role'] = 'teacher';
        $data['status'] = $data['status'] ?? 'active'; // form defaults to Active
        Staff::create($data);

        return redirect()->route('teachers.create')
            ->with('ok', "Teacher {$data['name']} added with reference {$data['reference']}");
    }

    /** Edit Teacher form */
    public function edit(Staff $staff)
    {
        return view('teachers.edit', ['teacher' => $staff]);
    }

    /** Update a teacher (reference/role stay unchanged) */
    public function update(Request $r, Staff $staff)
    {
        $data = $r->validate([
            'name'           => 'required|string|max:255',
            'phone'          => 'required|string|max:50',
            'nationality'    => 'nullable|string|max:100',
            'address'        => 'nullable|string|max:190',
            'dbs'            => 'required|in:0,1',
            'dbs_file'       => 'nullable|file|mimes:pdf|max:5120',
            'hourly_rate'    => 'nullable|numeric|min:0',
            'joining_date'   => 'nullable|date',
            'leaving_date'   => 'nullable|date|after_or_equal:joining_date',
            'status'         => 'required|in:active,inactive',
            'reference_doc'  => 'required|in:0,1',
            'reference_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // DBS certificate: cleared when DBS=No, replaced on new upload, otherwise kept
        if ($data['dbs'] == '0') {
            if ($staff->dbs_file) {
                Storage::disk('public')->delete($staff->dbs_file);
            }
            $data['dbs_file'] = null;
        } elseif ($r->hasFile('dbs_file')) {
            if ($staff->dbs_file) {
                Storage::disk('public')->delete($staff->dbs_file);
            }
            $data['dbs_file'] = $r->file('dbs_file')->store('teachers/dbs', 'public');
        } else {
            unset($data['dbs_file']);
        }

        // Reference document: cleared when Reference=No, replaced on new upload, otherwise kept
        if ($data['reference_doc'] == '0') {
            if ($staff->reference_file) {
                Storage::disk('public')->delete($staff->reference_file);
            }
            $data['reference_file'] = null;
        } elseif ($r->hasFile('reference_file')) {
            if ($staff->reference_file) {
                Storage::disk('public')->delete($staff->reference_file);
            }
            $data['reference_file'] = $r->file('reference_file')->store('teachers/references', 'public');
        } else {
            unset($data['reference_file']);
        }

        $staff->update($data);

        return redirect()->route('teachers.create')->with('ok', "Teacher {$data['name']} updated");
    }

    /** Remove a teacher (and their stored documents) */
    public function destroy(Staff $staff)
    {
        foreach ([$staff->dbs_file, $staff->reference_file] as $file) {
            if ($file) {
                Storage::disk('public')->delete($file);
            }
        }

        $name = $staff->name;
        $staff->delete();

        return redirect()->route('teachers.create')->with('ok', "Teacher {$name} removed");
    }
}
