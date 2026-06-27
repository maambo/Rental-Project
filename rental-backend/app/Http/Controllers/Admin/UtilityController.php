<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UtilityOption;
use App\Models\UtilityType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UtilityController extends Controller
{
    public function index()
    {
        $utilities = UtilityType::with('options')->orderBy('sort_order')->get();

        return Inertia::render('Admin/Utilities/Index', [
            'utilityTypes' => $utilities,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100|unique:utility_types,name',
            'icon'       => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        UtilityType::create([
            'name'       => $validated['name'],
            'icon'       => $validated['icon'] ?? null,
            'is_active'  => true,
            'sort_order' => $validated['sort_order'] ?? UtilityType::max('sort_order') + 1,
        ]);

        return back()->with('success', "Utility \"{$validated['name']}\" created.");
    }

    public function update(Request $request, UtilityType $utility)
    {
        $validated = $request->validate([
            'name'       => "required|string|max:100|unique:utility_types,name,{$utility->id}",
            'icon'       => 'nullable|string|max:50',
            'is_active'  => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $utility->update($validated);

        return back()->with('success', 'Utility updated.');
    }

    public function destroy(UtilityType $utility)
    {
        $utility->update(['is_active' => false]);

        return back()->with('success', "Utility \"{$utility->name}\" hidden from landlord forms.");
    }

    public function storeOption(Request $request, UtilityType $utility)
    {
        $validated = $request->validate([
            'label'      => 'required|string|max:100',
            'value'      => 'required|string|max:100|alpha_dash',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Ensure value is unique within this utility type
        $request->validate([
            'value' => "unique:utility_options,value,NULL,id,utility_type_id,{$utility->id}",
        ]);

        $utility->options()->create([
            'label'      => $validated['label'],
            'value'      => $validated['value'],
            'sort_order' => $validated['sort_order'] ?? $utility->options()->max('sort_order') + 1,
        ]);

        return back()->with('success', "Option \"{$validated['label']}\" added.");
    }

    public function destroyOption(UtilityOption $option)
    {
        $label = $option->label;
        $option->delete();

        return back()->with('success', "Option \"{$label}\" removed.");
    }
}
