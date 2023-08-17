<?php

namespace Tests\Feature;

use App\Models\DocumentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use PhpParser\Comment\Doc;
use Tests\TestCase;

class DocTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_all(): void
    {
        $response = $this->get('api/doctype');
        $response->assertStatus(200);
    }

    public function testCreatePoorlyFormattedDocType(): void
    {
        $response = $this->postJson('/api/doctype', [
            'first_name' => 'Gelson',
        ]);

        $response->assertJson([
            "message" => "The name field is required.",
            "errors" => ["name" => ["The name field is required."]]
        ]);

    }

    public function testShowImpossibleDoctype(): void
    {
        $response = $this->get('/api/doctype/-20');
        $response->assertJson([
            "error" => "document type not found"
        ]);
    }


}
