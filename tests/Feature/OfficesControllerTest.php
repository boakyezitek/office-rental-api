<?php

namespace Tests\Feature;

use App\Models\Image;
use App\Models\Office;
use App\Models\Reservation;
use App\Models\Tag;
use App\Models\User;
use App\Notifications\OfficePendingApproval;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

use function PHPUnit\Framework\assertJson;

class OfficesControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @test
     */
    public function itListAllOfficeInPaginatedWay(): void
    {
        Office::factory(3)->create();

        $response = $this->get('/api/offices');
        $response->assertOk();
        $response->assertJsonStructure(['data' => [['id']]]);
        $response->assertJsonCount(3,'data');

        $this->assertNotNull($response->json('data')[0]['id']);
        $this->assertNotNull($response->json('meta'));
        $this->assertNotNull($response->json('links'));

    }

    /**
     * @test
     */
    public function itOnlyListsOfficeThatAreNotHiddenAndAprroved()
    {
        Office::factory(3)->create();

        Office::factory()->create(['hidden' => true]);

        Office::factory()->create(['approval_status' => Office::APPROVAL_APPROVED]);

        $response = $this->get('/api/offices');
        $response->assertOk();
        $response->assertJsonCount(4,'data');
    }

    /**
     * @test
     */
    public function itListsOfficesIncludingHiddenAndUnApprovedIfFilteringForTheCurrentLoggedInUser()
    {
        $user = User::factory()->create();

        Office::factory(3)->for($user)->create();

        Office::factory()->hidden()->for($user)->create();
        Office::factory()->pending()->for($user)->create();

        $this->actingAs($user);

        $response = $this->get('/api/offices?user_id='.$user->id);

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    }

    /**
     * @test
     */
    public function itFiltersByUserId():void
    {
        Office::factory(3)->create();

        $user = User::factory()->create();

        $office = Office::factory()->for($user)->create();

        $response = $this->get('/api/offices?user_id='.$user->id);

        $response->assertOk();
        $response->assertJsonCount(1,'data');
        $this->assertEquals($office->id, $response->json('data')[0]['id']);
    }

    /**
     * @test
     */
    public function itFiltersByVisitorId():void
    {
        Office::factory(3)->create();

        $visitor = User::factory()->create();

        $office = Office::factory()->create();

        $reservation = Reservation::factory()->for($office)->for($visitor)->create();

        $response = $this->get('/api/offices?visitor_id='.$visitor->id);

        $response->assertOk();
        $response->assertJsonCount(1,'data');
        $this->assertEquals($office->id, $response->json('data')[0]['id']);
    }


    /**
     * @test
     */

     public function itIncludesImagesTagsAndUser():void
     {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();
        $office = Office::factory()->for($user)->create();

        $office->tags()->attach($tag);
        $office->images()->create(['path' => 'image.jpg']);

        $response = $this->get('/api/offices');

        $response->assertOk();

        $this->assertCount(1, $response->json('data')[0]['tags']);
        $this->assertIsArray($response->json('data')[0]['tags']);
        $this->assertCount(1, $response->json('data')[0]['images']);
        $this->assertIsArray($response->json('data')[0]['images']);
        $this->assertEquals($user->id ,$response->json('data')[0]['user']['id']);

     }

    /**
     * @test
     */

     public function itReturnTheNumberOfReservation():void
     {
        $office = Office::factory()->create();

        Reservation::factory()->for($office)->create(['status' => Reservation::STATUS_ACTIVE]);
        Reservation::factory()->for($office)->create(['status' => Reservation::STATUS_CANCELLED]);

        $response = $this->get('/api/offices');

        $response->assertOk();
        $this->assertEquals(1, $response->json('data')[0]['reservations_count']);
     }

    /**
     * @test
    */

    public function itOrdersByDistanceWhenCoordinatesAreProvided()
    {


        $office = Office::factory()->create([
            'lat' => '5.804104333704523',
            'lng' => '-0.14553221081870343',
            'title' => 'Leiria',
        ]);

        $office2 = Office::factory()->create([
            'lat' => '5.655141285936562',
            'lng' => '-0.18241469719923803',
            'title' => 'University of Ghana',
        ]);

        $response = $this->get('/api/offices?lat=5.654730915610937&lng=-0.10684656193102442');

        $response->assertOk();
        $this->assertEquals('University of Ghana', $response->json('data')[0]['title']);
        $this->assertEquals('Leiria', $response->json('data')[1]['title']);


        $response = $this->get('/api/offices');
        $response->assertOk();
        $this->assertEquals('Leiria', $response->json('data')[0]['title']);
        $this->assertEquals('University of Ghana', $response->json('data')[1]['title']);
    }

    /**
     * @test
    */

    public function itShowsTheOffice()
    {
        $user = User::factory()->createQuietly();
        $tag = Tag::factory()->createQuietly();
        $office = Office::factory()->for($user)->create();

        $office->tags()->attach($tag);
        $office->images()->create(['path' => 'image.jpg']);

        Reservation::factory()->for($office)->create(['status' => Reservation::STATUS_ACTIVE]);
        Reservation::factory()->for($office)->create(['status' => Reservation::STATUS_CANCELLED]);

        $response = $this->get('/api/offices/'.$office->id);


        $response->assertOk();

        $this->assertCount(1, $response->json('data')['tags']);
        $this->assertIsArray($response->json('data')['tags']);
        $this->assertCount(1, $response->json('data')['images']);
        $this->assertIsArray($response->json('data')['images']);
        $this->assertEquals($user->id ,$response->json('data')['user']['id']);
        $this->assertEquals(1, $response->json('data')['reservations_count']);
    }

    /**
     * @test
    */

    public function itCreateAnOffice()
    {
        $user = User::factory()->create();

        $tag1 = Tag::factory()->create();
        $tag2= Tag::factory()->create();
        $this->actingAs($user);
        $response = $this->postJson('/api/offices', [
            'title' => 'Office University Of Ghana',
            'description' => 'Descriptoin',
            'lat' => '5.655141285936562',
            'lng' => '-0.18241469719923803',
            'address_line1' => 'address',
            'price_per_day' => 10_000,
            'monthly_discount' => 5,
            'tags' => [$tag1->id, $tag2->id],
        ]);

        $response->assertCreated()
        ->assertJsonPath('data.title', 'Office University Of Ghana')
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.approval_status', Office::APPROVAL_PENDING)
        ->assertJsonCount(2, 'data.tags');

        $this->assertDatabaseHas('offices', [
            'title' => 'Office University Of Ghana'
        ]);
    }

    /**
     * @test
     */
    public function itDoesntAllowCreatingIfScopeIsNotProvided()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['office.create']);

        $response = $this->postJson('/api/offices');

        $this->assertNotEquals(Response::HTTP_FORBIDDEN, $response->status());
    }


    /**
     * @test
     */
    public function itUpdatesAnOffice()
    {
        $user = User::factory()->create();
        $tags = Tag::factory(3)->create();
        $office = Office::factory()->for($user)->create();

        $office->tags()->attach($tags);

        $this->actingAs($user);

        $anotherTag = Tag::factory()->create();

        $response = $this->putJson('/api/offices/'.$office->id, [
            'title' => 'Amazing Office',
            'tags' => [$tags[0]->id, $anotherTag->id]
        ]);

        $response->assertOk()
            ->assertJsonCount(2, 'data.tags')
            ->assertJsonPath('data.tags.0.id', $tags[0]->id)
            ->assertJsonPath('data.tags.1.id', $anotherTag->id)
            ->assertJsonPath('data.title', 'Amazing Office');
    }

    /**
     * @test
     */
    public function itDoesntUpdateOfficeThatDoesntBelongToUser()
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $office = Office::factory()->for($anotherUser)->create();

        $this->actingAs($user);

        $response = $this->putJson('/api/offices/'.$office->id, [
            'title' => 'Amazing Office'
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

     /**
     * @test
     */
    public function itMarksTheOfficeAsPendingIfDirty()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Notification::fake();

        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        $this->actingAs($user);

        $response = $this->putJson('/api/offices/'.$office->id, [
            'lat' => 40.74051727562952
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('offices', [
            'id' => $office->id,
            'approval_status' => Office::APPROVAL_PENDING,
        ]);

        Notification::assertSentTo($admin, OfficePendingApproval::class);
    }


    /**
     * @test
     */
    public function itUpdatedTheFeaturedImageOfAnOffice()
    {
        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        $image = $office->images()->create([
            'path' => 'image.jpg'
        ]);

        $this->actingAs($user);

        $response = $this->putJson('/api/offices/'.$office->id, [
            'featured_image_id' => $image->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.featured_image_id', $image->id);
    }


    /**
     * @test
     */
    public function itDoesntUpdateFeaturedImageThatBelongsToAnotherOffice()
    {
        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();
        $office2 = Office::factory()->for($user)->create();

        $image = $office2->images()->create([
            'path' => 'image.jpg'
        ]);

        $this->actingAs($user);

        $response = $this->putJson('/api/offices/'.$office->id, [
            'featured_image_id' => $image->id,
        ]);

        $response->assertUnprocessable()->assertInvalid('featured_image_id');
    }

    /**
     * @test
     */
    public function itCanDeleteOffices()
    {
        Storage::put('/office_image.jpg', 'empty');

        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        $image = $office->images()->create([
            'path' => 'office_image.jpg'
        ]);

        $this->actingAs($user);

        $response = $this->deleteJson('/api/offices/'.$office->id);

        $response->assertOk();

        $this->assertSoftDeleted($office);

        $this->assertModelMissing($image);

        Storage::assertMissing('office_image.jpg');
    }

    /**
     * @test
     */
    public function itCannotDeleteAnOfficeThatHasReservations()
    {
        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        Reservation::factory(3)->for($office)->create();

        $this->actingAs($user);

        $response = $this->deleteJson('/api/offices/'.$office->id);

        $response->assertUnprocessable();

        $this->assertNotSoftDeleted($office);
    }
}
