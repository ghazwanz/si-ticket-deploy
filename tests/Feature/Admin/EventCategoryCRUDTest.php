<?php

namespace Tests\Feature\Admin;

use App\Models\EventCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventCategoryCRUDTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user to perform the actions
        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    /**
     * Test admin can create a category with image and color.
     */
    public function test_admin_can_create_category_with_image_and_color(): void
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('concert.jpg');

        $response = $this->actingAs($this->admin)->post(route('admin.event-categories.store'), [
            'name' => 'Concert Category',
            'image' => $image,
            'color' => 'sky',
        ]);

        $response->assertRedirect(route('admin.event-categories.index'));
        $response->assertSessionHas('status', 'Kategori event berhasil dibuat.');

        $this->assertDatabaseHas('event_categories', [
            'name' => 'Concert Category',
            'color' => 'sky',
        ]);

        $category = EventCategory::where('name', 'Concert Category')->firstOrFail();
        $this->assertNotNull($category->image);
        Storage::disk('public')->assertExists($category->image);

        // Verify the accessor returns the correct URL
        $this->assertEquals(Storage::disk('public')->url($category->image), $category->image_url);
    }

    /**
     * Test admin can update a category's details, image, and color.
     */
    public function test_admin_can_update_category(): void
    {
        Storage::fake('public');

        $category = EventCategory::create([
            'name' => 'Old Name',
            'slug' => 'old-name',
            'color' => 'rose',
            'image' => UploadedFile::fake()->image('old.png')->store('categories', 'public'),
        ]);

        $oldImagePath = $category->image;
        Storage::disk('public')->assertExists($oldImagePath);

        $newImage = UploadedFile::fake()->image('new.jpg');

        $response = $this->actingAs($this->admin)->put(route('admin.event-categories.update', $category), [
            'name' => 'Updated Category Name',
            'image' => $newImage,
            'color' => 'emerald',
        ]);

        $response->assertRedirect(route('admin.event-categories.index'));
        $response->assertSessionHas('status', 'Kategori event berhasil diperbarui.');

        $category->refresh();

        $this->assertEquals('Updated Category Name', $category->name);
        $this->assertEquals('emerald', $category->color);
        $this->assertNotEquals($oldImagePath, $category->image);

        // The old file should be deleted from storage
        Storage::disk('public')->assertMissing($oldImagePath);
        Storage::disk('public')->assertExists($category->image);
    }

    /**
     * Test admin can remove the category's image.
     */
    public function test_admin_can_remove_category_image(): void
    {
        Storage::fake('public');

        $category = EventCategory::create([
            'name' => 'Some Category',
            'slug' => 'some-category',
            'color' => 'amber',
            'image' => UploadedFile::fake()->image('photo.png')->store('categories', 'public'),
        ]);

        $imagePath = $category->image;
        Storage::disk('public')->assertExists($imagePath);

        $response = $this->actingAs($this->admin)->put(route('admin.event-categories.update', $category), [
            'name' => 'Some Category',
            'color' => 'amber',
            'remove_image' => 1,
        ]);

        $response->assertRedirect(route('admin.event-categories.index'));
        $category->refresh();

        $this->assertNull($category->image);
        Storage::disk('public')->assertMissing($imagePath);

        // The accessor should fall back to the placeholder image
        $this->assertEquals(asset('img/eobanner.png'), $category->image_url);
    }
}
