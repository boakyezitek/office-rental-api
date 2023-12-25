<?php

namespace Tests\Feature;

use App\Models\Image;
use App\Models\Office;
use App\Models\Reservation;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OfficesControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function itListAllOfficeInPaginatedWay(): void
    {
        Office::factory(3)->create();

        $response = $this->get('/api/offices');
        $response->dump();
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
    public function itFiltersByHostId():void
    {
        Office::factory(3)->create();

        $host = User::factory()->create();

        $office = Office::factory()->for($host)->create();

        $response = $this->get('/api/offices?host_id='.$host->id);

        $response->assertOk();
        $response->assertJsonCount(1,'data');
        $this->assertEquals($office->id, $response->json('data')[0]['id']);
    }

    /**
     * @test
     */
    public function itFiltersByUserId():void
    {
        Office::factory(3)->create();

        $user = User::factory()->create();

        $office = Office::factory()->create();

        $reservation = Reservation::factory()->for($office)->for($user)->create();

        $response = $this->get('/api/offices?user_id='.$user->id);

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
        $response->dump();

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

        $response->dump();
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

        $response->dump();
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
        $user = User::factory()->create();
        $tag = Tag::factory()->create();
        $office = Office::factory()->for($user)->create();

        $office->tags()->attach($tag);
        $office->images()->create(['path' => 'image.jpg']);

        Reservation::factory()->for($office)->create(['status' => Reservation::STATUS_ACTIVE]);
        Reservation::factory()->for($office)->create(['status' => Reservation::STATUS_CANCELLED]);

        $response = $this->get('/api/offices/'.$office->id);

        $response->dump();

        $response->assertOk();

        $this->assertCount(1, $response->json('data')['tags']);
        $this->assertIsArray($response->json('data')['tags']);
        $this->assertCount(1, $response->json('data')['images']);
        $this->assertIsArray($response->json('data')['images']);
        $this->assertEquals($user->id ,$response->json('data')['user']['id']);
        $this->assertEquals(1, $response->json('data')['reservations_count']);
    }

}
