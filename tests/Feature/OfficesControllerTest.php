<?php

namespace Tests\Feature;

use App\Models\Office;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OfficesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_example(): void
    {
        Office::factory()->count(3)->create();

        $response = $this->get('/api/offices');

        $response->assertOk()->dump();

        $response->assertJsonStructure(['data' => [['id']]]);

        $this->assertNotNull($response->json('data')[0]['id']);

        $this->assertCount(3, $response->json('data'));
    }
}
