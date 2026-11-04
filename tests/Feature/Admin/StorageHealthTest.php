<?php

namespace Tests\Feature\Admin;

use App\Models\AdminUser;
use App\Support\StorageHealth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorageHealthTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): AdminUser
    {
        return AdminUser::create(['username' => 'a', 'email' => 'a@example.com', 'password' => Hash::make('secret123'), 'login_attempts' => 0]);
    }

    public function test_a_rejected_upload_is_reported_and_nothing_is_saved(): void
    {
        Storage::fake('s3');
        config(['filesystems.default' => 's3']);
        // Simulate the bucket refusing the write: the fake disk cannot write to a read-only root.
        Storage::shouldReceive('disk')->with('s3')->andReturn($fake = \Mockery::mock());
        $fake->shouldReceive('putFileAs')->andReturn(false);

        $this->actingAs($this->admin(), 'admin')->post(route('admin.categories.store'), [
            'name' => 'Caps', 'image_file' => UploadedFile::fake()->image('cap.png'),
        ])->assertSessionHasErrors('upload');
        $this->assertDatabaseMissing('categories', ['name' => 'Caps']);
    }

    public function test_storage_banner_shows_when_the_bucket_rejects_writes(): void
    {
        config(['filesystems.default' => 's3']);
        Storage::shouldReceive('disk')->with('s3')->andReturn($fake = \Mockery::mock());
        $fake->shouldReceive('put')->andReturn(false);
        StorageHealth::forget();

        $this->actingAs($this->admin(), 'admin')->get(route('admin.dashboard'))->assertOk()
            ->assertSee('Cloud storage is rejecting uploads');
    }
}
