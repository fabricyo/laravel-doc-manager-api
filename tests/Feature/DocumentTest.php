<?php

namespace Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_all(): void
    {
        $response = $this->get('api/document');
        $response->assertStatus(200);
    }

    public function testCreatePoorlyFormattedDocument(): void
    {
        $response = $this->postJson('/api/document', [
            'name' => 'Nicolas',
        ]);

        $response->assertJson([
            "message" => "The document types id field is required. (and 1 more error)",
            "errors" => [
                "document_types_id" => [
                    "The document types id field is required."
                ],
                "column" => [
                    "The column field is required."
                ]
            ]
        ]);
    }

    public function testShowImpossibleDocument(): void
    {
        $response = $this->get('/api/document/-20');
        $response->assertJson([
            "error" => "Document not found"
        ]);
    }
}
