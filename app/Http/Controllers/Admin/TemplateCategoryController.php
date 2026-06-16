<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TemplateCategory;
use Illuminate\Http\Request;

class TemplateCategoryController extends Controller
{
    public function index()
    {
        $categories = TemplateCategory::withCount('templates')->orderBy('name')->get();
        return view('admin.template-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:template_categories,name',
        ]);

        TemplateCategory::create(['name' => $request->name]);

        return back()->with('success', 'Category "' . $request->name . '" added.');
    }

    public function update(Request $request, TemplateCategory $templateCategory)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:template_categories,name,' . $templateCategory->id,
        ]);

        $old = $templateCategory->name;
        $templateCategory->update(['name' => $request->name]);

        // Keep existing templates in sync
        \App\Models\EmailTemplate::where('category', $old)->update(['category' => $request->name]);

        return back()->with('success', 'Category renamed to "' . $request->name . '".');
    }

    public function destroy(TemplateCategory $templateCategory)
    {
        // Clear category from templates that used it
        \App\Models\EmailTemplate::where('category', $templateCategory->name)->update(['category' => null]);

        $templateCategory->delete();

        return back()->with('success', 'Category deleted.');
    }
}
