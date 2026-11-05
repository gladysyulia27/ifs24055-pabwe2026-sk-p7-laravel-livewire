<?php

namespace App\Features\Ai\Services;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedStockItem;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OpenAiStockGeneratorService implements AiClientInterface
{
    public function generate(string $prompt, int $count = 5): array
    {
        $count = max(1, min(20, $count));
        $key = trim((string) config('openai.api_key', ''));

        if ($key !== '') {
            try {
                $response = Http::withToken($key)
                    ->timeout(45)
                    ->post(rtrim((string) config('openai.base_url', 'https://api.openai.com/v1'), '/').'/chat/completions', [
                        'model' => config('openai.model', 'gpt-4o-mini'),
                        'response_format' => ['type' => 'json_object'],
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => 'Buat data contoh inventaris yang masuk akal. Balas JSON object dengan key items berisi array objek: name, sku, description, quantity, unit, price, min_stock. Jangan sertakan markdown.',
                            ],
                            [
                                'role' => 'user',
                                'content' => "Buat {$count} item stok untuk konteks: {$prompt}",
                            ],
                        ],
                    ])
                    ->throw()
                    ->json();

                $content = $response['choices'][0]['message']['content'] ?? '{}';
                $decoded = json_decode($content, true);
                $items = is_array($decoded) ? ($decoded['items'] ?? []) : [];

                $mapped = [];
                foreach ($items as $item) {
                    if (is_array($item) && ! empty($item['name'])) {
                        $dto = new GeneratedStockItem(
                            name: (string) $item['name'],
                            sku: isset($item['sku']) ? (string) $item['sku'] : null,
                            description: (string) ($item['description'] ?? ''),
                            quantity: max(0, (int) ($item['quantity'] ?? 10)),
                            unit: (string) ($item['unit'] ?? 'pcs'),
                            price: isset($item['price']) && is_numeric($item['price']) ? (float) $item['price'] : null,
                            minStock: max(0, (int) ($item['min_stock'] ?? 5)),
                        );
                        $mapped[] = $dto->toArray();
                    }
                }

                if ($mapped !== []) {
                    return $mapped;
                }
            } catch (\Throwable) {
                // Fall back to contextual generator when offline or API quota/key is unavailable
            }
        }

        return $this->generateContextualFallback($prompt, $count);
    }

    private function generateContextualFallback(string $prompt, int $count): array
    {
        $cleanTopic = trim($prompt) ?: 'Inventaris Umum';
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', Str::ascii($cleanTopic)) ?: 'INV', 0, 3));
        $prefixPadded = str_pad($prefix, 3, 'X');

        $templates = [
            ['suffix' => 'Unit Utama Pro', 'unit' => 'unit', 'qty' => 12, 'min' => 4, 'price' => 2500000.0],
            ['suffix' => 'Starter Kit Standar', 'unit' => 'set', 'qty' => 8, 'min' => 3, 'price' => 750000.0],
            ['suffix' => 'Komponen Cadangan', 'unit' => 'pcs', 'qty' => 25, 'min' => 10, 'price' => 120000.0],
            ['suffix' => 'Kabel & Konektor Pendukung', 'unit' => 'pcs', 'qty' => 4, 'min' => 5, 'price' => 85000.0],
            ['suffix' => 'Modul Aksesoris', 'unit' => 'box', 'qty' => 15, 'min' => 5, 'price' => 350000.0],
        ];

        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $tpl = $templates[$i % count($templates)];
            $num = $i + 1;
            $itemName = ucwords($cleanTopic).' - '.$tpl['suffix'].($i >= count($templates) ? " #{$num}" : '');
            $sku = sprintf('%s-%03d', $prefixPadded, $num);
            $desc = "**{$itemName}**\n\n- Kategori: {$cleanTopic}\n- Spesifikasi standar operasional\n- Disarankan pemeriksaan berkala setiap bulan.";

            $dto = new GeneratedStockItem(
                name: Str::limit($itemName, 140, ''),
                sku: $sku,
                description: $desc,
                quantity: $tpl['qty'],
                unit: $tpl['unit'],
                price: $tpl['price'],
                minStock: $tpl['min'],
            );
            $result[] = $dto->toArray();
        }

        return $result;
    }
}
