<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_upload_media(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('test-avatar.jpg');

        $response = $this->postJson('/api/v1/media/upload', [
            'file' => $file,
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_upload_and_retrieve_media(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        // Write temp file inside workspace testing storage so FileinfoMimeTypeGuesser
        // can read it reliably even when host OS temp directories are restricted.
        $tempDir = storage_path('framework/testing');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }
        $tempPath = $tempDir . DIRECTORY_SEPARATOR . 'sample-document-' . uniqid() . '.pdf';
        file_put_contents($tempPath, "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF\n");

        $file = new UploadedFile($tempPath, 'sample-document.pdf', 'application/pdf', null, true);

        try {
            $uploadResponse = $this->actingAs($user, 'sanctum')->postJson('/api/v1/media/upload', [
                'file' => $file,
                'collection' => 'documents',
            ]);

            $uploadResponse->assertStatus(201);
            $uploadResponse->assertJsonStructure([
                'message',
                'media' => [
                    'id',
                    'name',
                    'file_name',
                    'mime_type',
                    'size',
                    'url',
                    'collection',
                ],
            ]);

            $this->assertEquals(1, $user->fresh()->getMedia('documents')->count());

            // Test listing media
            $listResponse = $this->actingAs($user, 'sanctum')->getJson('/api/v1/media?collection=documents');
            $listResponse->assertStatus(200);
            $listResponse->assertJsonCount(1, 'media');

            // Test deleting media
            $mediaId = $uploadResponse->json('media.id');
            $deleteResponse = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/media/{$mediaId}");
            $deleteResponse->assertStatus(200);
            $this->assertEquals(0, $user->fresh()->getMedia('documents')->count());
        } finally {
            @unlink($tempPath);
        }
    }
}
