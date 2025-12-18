<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PropertyApplicationController extends Controller
{
    public function create(Property $property)
    {
        return Inertia::render('Properties/Apply', [
            'property' => $property,
        ]);
    }

    public function store(Request $request, Property $property)
    {
        $validated = $request->validate([
            'employer_name' => 'required|string',
            'job_title' => 'required|string',
            'monthly_income' => 'required|numeric',
            'employment_duration' => 'required|string',
            'reference_1_name' => 'required|string',
            'reference_1_phone' => 'required|string',
            'reference_1_relation' => 'required|string',
            'reference_2_name' => 'required|string',
            'reference_2_phone' => 'required|string',
            'reference_2_relation' => 'required|string',
            'preferred_move_in_date' => 'required|date',
            'lease_duration_months' => 'required|integer',
            'occupants_count' => 'required|integer',
            'has_pets' => 'boolean',
            'pet_details' => 'nullable|string',
            'additional_comments' => 'nullable|string',
        ]);

        // Insert directly into DB if model doesn't exist yet
        DB::table('property_applications')->insert([
            'user_id' => $request->user()->id,
            'property_id' => $property->id,
            'status' => 'pending',
            'employer_name' => $validated['employer_name'],
            'job_title' => $validated['job_title'],
            'monthly_income' => $validated['monthly_income'],
            'employment_duration' => $validated['employment_duration'],
            'reference_1_name' => $validated['reference_1_name'],
            'reference_1_phone' => $validated['reference_1_phone'],
            'reference_1_relation' => $validated['reference_1_relation'],
            'reference_2_name' => $validated['reference_2_name'],
            'reference_2_phone' => $validated['reference_2_phone'],
            'reference_2_relation' => $validated['reference_2_relation'],
            'preferred_move_in_date' => $validated['preferred_move_in_date'],
            'lease_duration_months' => $validated['lease_duration_months'],
            'occupants_count' => $validated['occupants_count'],
            'has_pets' => $validated['has_pets'] ?? false,
            'pet_details' => $validated['pet_details'] ?? null,
            'additional_comments' => $validated['additional_comments'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
       
       return redirect()->route('dashboard')->with('success', 'Application submitted successfully! The landlord will review your application.');
    }
}
