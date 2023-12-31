<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TagsControllerTest extends TestCase
{
    use LazilyRefreshDatabase; // This trait resets the database after each test

    /**
     * @test
     */
    public function itListsTags(): void
    {

        $response = $this->getJson('/api/tags'); // Use getJson to work with JSON responses

        $response->assertStatus(200);

        // Assuming your API returns a structure like {"data": [{"id": 1, ...}, {"id": 2, ...}]}
        $response->assertJsonStructure(['data' => [['id']]]);

        // Assuming your Tag model has 'id' attribute
        $this->assertNotNull($response->json('data')[0]['id']);

        // Additional assertions if needed, e.g., checking the number of tags returned
        $this->assertCount(3, $response->json('data'));
    }
}
