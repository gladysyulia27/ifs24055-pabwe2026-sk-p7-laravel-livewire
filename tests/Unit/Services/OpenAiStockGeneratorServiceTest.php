<?php

namespace Tests\Unit\Services;

use App\Features\Ai\Services\OpenAiStockGeneratorService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenAiStockGeneratorServiceTest extends TestCase
{
    public function test_generate_via_openai_http_and_fallback(): void
    {
        config([
            'openai.api_key' => 'sk-test-key',
            'openai.base_url' => 'https://api.openai.com/v1',
            'openai.model' => 'gpt-4o-mini',
        ]);

        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::sequence()
                ->push([
                    'choices' => [[
                        'message' => [
                            'content' => json_encode([
                                'items' => [
                                    [
                                        'name' => 'Oscilloscope Digital',
                                        'sku' => 'OSC-01',
                                        'description' => 'Alat ukur sinyal',
                                        'quantity' => 4,
                                        'unit' => 'unit',
                                        'price' => 3500000,
                                        'min_stock' => 2,
                                    ],
                                ],
                            ]),
                        ],
                    ]],
                ], 200)
                ->push(['error' => 'Server Error'], 500),
        ]);

        $service = new OpenAiStockGeneratorService();

        // 1. Successful API response
        $items = $service->generate('Laboratorium Elektronika', 1);
        $this->assertCount(1, $items);
        $this->assertSame('Oscilloscope Digital', $items[0]['name']);

        // 2. Failed API response falls back to contextual generator
        $fallbackItems = $service->generate('Laboratorium Elektronika', 6);
        $this->assertCount(6, $fallbackItems);
    }
}