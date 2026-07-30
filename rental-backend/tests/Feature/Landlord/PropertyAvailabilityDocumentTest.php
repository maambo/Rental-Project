<?php

namespace Tests\Feature\Landlord;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class PropertyAvailabilityDocumentTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    // ── PropertyAvailabilityController ────────────────────────────────

    public function test_landlord_can_update_property_availability(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord, ['availability_status' => 'available']);

        $this->actingAs($landlord)
             ->patch(route('landlord.properties.availability.update', $property), [
                 'availability_status' => 'rented',
             ])
             ->assertRedirect();

        $this->assertEquals('rented', $property->fresh()->availability_status);
    }

    public function test_landlord_cannot_update_another_landlords_property_availability(): void
    {
        $landlordA = $this->makeLandlord();
        $landlordB = $this->makeLandlord();
        $property  = $this->makeProperty($landlordB);

        $this->actingAs($landlordA)
             ->patch(route('landlord.properties.availability.update', $property), [
                 'availability_status' => 'rented',
             ])
             ->assertNotFound();
    }

    public function test_availability_status_must_be_valid(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord);

        $this->actingAs($landlord)
             ->patch(route('landlord.properties.availability.update', $property), [
                 'availability_status' => 'demolished',
             ])
             ->assertSessionHasErrors('availability_status');
    }

    // ── PropertyDocumentController ────────────────────────────────────

    public function test_landlord_can_upload_property_document(): void
    {
        Storage::fake('public');

        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord);

        $this->actingAs($landlord)
             ->post(route('landlord.properties.documents.store', $property), [
                 'documents' => [UploadedFile::fake()->create('contract.pdf', 500, 'application/pdf')],
                 'names'     => ['Lease Contract'],
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('property_documents', [
            'property_id' => $property->id,
            'name'        => 'Lease Contract',
        ]);
    }

    public function test_landlord_can_delete_property_document(): void
    {
        Storage::fake('public');

        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord);

        $file = UploadedFile::fake()->create('deed.pdf', 200, 'application/pdf');
        $path = $file->store("properties/{$property->id}/documents", 'public');

        $document = $property->documents()->create([
            'uploaded_by' => $landlord->id,
            'name'        => 'Deed',
            'file_path'   => $path,
            'mime_type'   => 'application/pdf',
            'file_size'   => 200,
        ]);

        $this->actingAs($landlord)
             ->delete(route('landlord.properties.documents.destroy', [$property, $document]))
             ->assertRedirect();

        $this->assertDatabaseMissing('property_documents', ['id' => $document->id]);
    }

    public function test_landlord_cannot_upload_to_another_landlords_property(): void
    {
        Storage::fake('public');

        $landlordA = $this->makeLandlord();
        $landlordB = $this->makeLandlord();
        $property  = $this->makeProperty($landlordB);

        $this->actingAs($landlordA)
             ->post(route('landlord.properties.documents.store', $property), [
                 'documents' => [UploadedFile::fake()->create('file.pdf', 100, 'application/pdf')],
             ])
             ->assertNotFound();
    }
}
