<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailGroup;
use App\Models\EmailList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $groups = EmailGroup::withCount('emails')->orderBy('name')->get();
        return view('admin.email-lists.import', compact('groups'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file'  => 'required|file|mimes:csv,txt|max:51200', // 50 MB
            'list_name' => 'required|string|max:255',
        ]);

        set_time_limit(300); // 5-minute budget for large files

        $group = EmailGroup::firstOrCreate(
            ['name' => trim($request->list_name)],
            ['description' => 'Imported on ' . now()->format('Y-m-d H:i')]
        );

        // One query: pull all known emails into a hash-map for O(1) lookups.
        // This also covers within-file duplicates — we add each accepted email here.
        $seen = DB::table('email_lists')
            ->pluck('email')
            ->flip()
            ->all(); // ['addr@x.com' => 0, ...]

        $handle = fopen($request->file('csv_file')->getRealPath(), 'r');

        // Read the first row to detect whether it is a header.
        $firstRow = fgetcsv($handle);
        if ($firstRow === false) {
            fclose($handle);
            return back()->withErrors(['csv_file' => 'The CSV file is empty.']);
        }
        $firstRow   = array_map('trim', $firstRow);
        $firstLower = array_map('strtolower', $firstRow);

        $emailCol      = null;
        $nameCol       = null;
        $firstRowIsData = false;

        foreach ($firstLower as $i => $cell) {
            if (in_array($cell, ['email', 'email address', 'e-mail', 'emailaddress', 'mail'])) {
                $emailCol = $i;
            }
            if (in_array($cell, ['name', 'full name', 'fullname', 'first name', 'firstname', 'contact'])) {
                $nameCol = $i;
            }
        }

        if ($emailCol === null) {
            // No header keywords found — treat the first row as data.
            $firstRowIsData = true;
            foreach ($firstRow as $i => $cell) {
                if (filter_var($cell, FILTER_VALIDATE_EMAIL)) {
                    $emailCol = $i;
                    $nameCol  = ($i === 0 && count($firstRow) > 1) ? 1 : ($i > 0 ? 0 : null);
                    break;
                }
            }
            if ($emailCol === null) {
                $emailCol = 0;
                $nameCol  = count($firstRow) > 1 ? 1 : null;
            }
        }

        $imported   = 0;
        $duplicates = 0;
        $invalid    = 0;
        $batch      = [];
        $batchSize  = 500;
        $now        = now()->toDateTimeString();

        $processRow = function (array $row) use (
            $emailCol, $nameCol, $group, $now,
            &$seen, &$imported, &$duplicates, &$invalid, &$batch
        ): void {
            $email = strtolower(trim($row[$emailCol] ?? ''));
            $name  = $nameCol !== null ? (trim($row[$nameCol] ?? '') ?: null) : null;

            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $invalid++;
                return;
            }

            if (isset($seen[$email])) {
                $duplicates++;
                return;
            }

            $seen[$email] = true; // Guard both DB duplicates and within-file duplicates

            $batch[] = [
                'email'      => $email,
                'name'       => $name,
                'status'     => 'pending',
                'group_id'   => $group->id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $imported++;
        };

        if ($firstRowIsData) {
            $processRow($firstRow);
        }

        while (($row = fgetcsv($handle)) !== false) {
            $row = array_map('trim', $row);
            if (!array_filter($row)) continue; // skip blank lines

            $processRow($row);

            if (count($batch) >= $batchSize) {
                EmailList::insert($batch);
                $batch = [];
            }
        }

        fclose($handle);

        if (!empty($batch)) {
            EmailList::insert($batch);
        }

        return redirect()->route('admin.email-lists.index')
            ->with('import_result', compact('imported', 'duplicates', 'invalid'));
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        $count = EmailList::whereIn('id', $request->ids)->delete();
        return back()->with('success', "{$count} contact(s) removed.");
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
