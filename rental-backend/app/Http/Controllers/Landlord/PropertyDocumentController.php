<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PropertyDocumentController extends Controller
{
    public function store(Request $request, $propertyId)
    {
        $property = Property::forLandlord(auth()->id())->findOrFail($propertyId);

        $request->validate([
            'documents'   => 'required|array|max:10',
            'documents.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png',
            'names'       => 'nullable|array',
            'names.*'     => 'nullable|string|max:100',
        ]);

        foreach ($request->file('documents') as $i => $file) {
            $path = $file->store("properties/{$property->id}/documents", 'public');
            $property->documents()->create([
                'uploaded_by' => auth()->id(),
                'name'        => $request->names[$i] ?? $file->getClientOriginalName(),
                'file_path'   => $path,
                'mime_type'   => $file->getMimeType(),
                'file_size'   => $file->getSize(),
            ]);
        }

        return back()->with('success', 'Documents uploaded successfully.');
    }

    public function destroy($propertyId, $documentId)
    {
        $property = Property::forLandlord(auth()->id())->findOrFail($propertyId);
        $document = $property->documents()->findOrFail($documentId);

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Document removed.');
    }
}
