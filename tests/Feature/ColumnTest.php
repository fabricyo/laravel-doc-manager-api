<?php

namespace Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ColumnTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_all(): void
    {
        $response = $this->get('api/column');
        $response->assertStatus(200);
    }

    public function testCreatePoorlyFormattedColumn(): void
    {
        $response = $this->postJson('/api/column', [
            'first_name' => 'Amanda',
        ]);

        $response->assertJson([
            "message" => "The name field is required. (and 1 more error)",
            "errors" => [
                "name" => ["The name field is required."],
                "document_types_id" => ["The document types id field is required."]]
        ]);
    }

    public function testShowImpossibleColumn(): void
    {
        $response = $this->get('/api/column/-20');
        $response->assertJson([
            "error" => "Column not found"
        ]);
    }


}
