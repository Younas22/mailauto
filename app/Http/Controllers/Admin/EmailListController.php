<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailGroup;
use App\Models\EmailList;
use Illuminate\Http\Request;

class EmailListController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $groupId = $request->query('group');

        $query = EmailList::with('group')->latest();

        if (in_array($status, ['pending', 'sent', 'failed'])) {
            $query->where('status', $status);
        }

        if ($groupId) {
            $query->where('group_id', $groupId);
        }

        $emails = $query->paginate(15)->withQueryString();
        $counts = [
            'total'   => EmailList::count(),
            'pending' => EmailList::where('status', 'pending')->count(),
            'sent'    => EmailList::where('status', 'sent')->count(),
            'failed'  => EmailList::where('status', 'failed')->count(),
        ];

        $groups = EmailGroup::withCount('emails')->orderBy('name')->get();

        return view('admin.email-lists.index', compact('emails', 'counts', 'status', 'groups', 'groupId'));
    }

    public function showImport()
    {
        $groups = EmailGroup::orderBy('name')->get();
        return view('admin.email-lists.import', compact('groups'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file'   => 'required|file|mimes:csv,txt|max:5120',
            'list_name'  => 'required|string|max:255',
        ]);

        // Find or create the email group
        $group = EmailGroup::firstOrCreate(
            ['name' => trim($request->list_name)],
            ['description' => 'Imported on ' . now()->format('Y-m-d H:i')]
        );

        $handle = fopen($request->file('csv_file')->getRealPath(), 'r');

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $cleaned = array_map('trim', $row);
            if (!empty(array_filter($cleaned))) {
                $rows[] = $cleaned;
            }
        }
        fclose($handle);

        if (empty($rows)) {
            return back()->withErrors(['csv_file' => 'The CSV file is empty.']);
        }

        // Auto-detect header and column positions
        $emailCol   = null;
        $nameCol    = null;
        $startRow   = 0;
        $firstLower = array_map('strtolower', $rows[0]);

        foreach ($firstLower as $i => $cell) {
            if (in_array($cell, ['email', 'email address', 'e-mail', 'emailaddress', 'mail'])) {
                $emailCol = $i;
            }
            if (in_array($cell, ['name', 'full name', 'fullname', 'first name', 'firstname', 'contact'])) {
                $nameCol = $i;
            }
        }

        if ($emailCol !== null) {
            $startRow = 1;
        } else {
            foreach ($rows[0] as $i => $cell) {
                if (filter_var($cell, FILTER_VALIDATE_EMAIL)) {
                    $emailCol = $i;
                    $nameCol  = ($i === 0 && count($rows[0]) > 1) ? 1 : ($i > 0 ? 0 : null);
                    break;
                }
            }
            if ($emailCol === null) {
                $emailCol = 0;
                $nameCol  = count($rows[0]) > 1 ? 1 : null;
            }
        }

        $imported   = 0;
        $duplicates = 0;
        $invalid    = 0;

        for ($i = $startRow; $i < count($rows); $i++) {
            $row   = $rows[$i];
            $email = strtolower($row[$emailCol] ?? '');
            $name  = $nameCol !== null ? ($row[$nameCol] ?? null) : null;
            $name  = $name ? trim($name) : null;

            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $invalid++;
                continue;
            }

            if (EmailList::where('email', $email)->exists()) {
                $duplicates++;
                continue;
            }

            EmailList::create([
                'email'    => $email,
                'name'     => $name ?: null,
                'status'   => 'pending',
                'group_id' => $group->id,
            ]);
            $imported++;
        }

        return redirect()->route('admin.email-lists.index')
            ->with('import_result', compact('imported', 'duplicates', 'invalid'));
    }

    public function destroy(EmailList $emailList)
    {
        $emailList->delete();
        return back()->with('success', 'Contact removed.');
    }

    public function sampleCsv()
    {
        $csv = "name,email\nJohn Smith,john@example.com\nJane Doe,jane@example.com\nAlex Johnson,alex@example.com\n";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sample-import.csv"',
        ]);
    }
}
