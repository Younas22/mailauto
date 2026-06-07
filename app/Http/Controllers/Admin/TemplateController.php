<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = EmailTemplate::latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && in_array($request->status, ['active', 'inactive'])) {
            $query->where('status', $request->status);
        }

        $templates = $query->paginate(10)->withQueryString();
        return view('admin.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.templates.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'   => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body'    => 'required|string',
            'status'  => 'in:active,inactive',
        ]);

        EmailTemplate::create([
            'title'   => $request->title,
            'subject' => $request->subject,
            'body'    => $request->body,
            'status'  => $request->status ?? 'active',
        ]);

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template created successfully.');
    }

    public function edit(EmailTemplate $template)
    {
        return view('admin.templates.edit', compact('template'));
    }

    public function update(Request $request, EmailTemplate $template)
    {
        $request->validate([
            'title'   => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body'    => 'required|string',
            'status'  => 'in:active,inactive',
        ]);

        $template->update([
            'title'   => $request->title,
            'subject' => $request->subject,
            'body'    => $request->body,
            'status'  => $request->status ?? 'active',
        ]);

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template updated successfully.');
    }

    public function destroy(EmailTemplate $template)
    {
        $template->delete();

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template deleted successfully.');
    }
}
