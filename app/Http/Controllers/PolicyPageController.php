<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PolicyPageController extends Controller
{
    /**
     * Display a published policy page to the public.
     */
    public function show(string $slug)
    {
        $page = Page::published()->where('slug', $slug)->firstOrFail();

        return Inertia::render('Policy/Show', [
            'page' => $page,
        ]);
    }

    /**
     * Admin: List all institutional policy pages.
     */
    public function adminIndex()
    {
        $pages = Page::with('lastUpdatedBy:id,name')->orderBy('title')->get();

        return Inertia::render('Admin/Pages/Index', [
            'pages' => $pages,
        ]);
    }

    /**
     * Admin: Show edit form for a policy page.
     */
    public function adminEdit(Page $page)
    {
        return Inertia::render('Admin/Pages/Edit', [
            'page' => $page,
        ]);
    }

    /**
     * Admin: Update policy page content and metadata.
     */
    public function adminUpdate(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'is_published' => 'boolean',
        ]);

        $page->update([
            ...$validated,
            'last_updated_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.pages.index')->with('success', "«{$page->title}» পলিসি পৃষ্ঠা সফলভাবে আপডেট হয়েছে।");
    }
}
