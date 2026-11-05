<?php

namespace App\Features\Stock\Services;

use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StockExcelService
{
    public function export(int $userId): string
    {
        $book = new Spreadsheet();
        $sheet = $book->getActiveSheet();
        $sheet->setTitle('Stocks');

        $rows = [
            ['name', 'sku', 'description', 'qty', 'unit', 'price', 'min_stock', 'status'],
            ...Stock::where('user_id', $userId)
                ->orderBy('id')
                ->get(['name', 'sku', 'description', 'quantity', 'unit', 'price', 'min_stock', 'status'])
                ->map(fn (Stock $s) => [
                    $s->name,
                    $s->sku,
                    $s->description,
                    $s->quantity,
                    $s->unit,
                    $s->price,
                    $s->min_stock,
                    $s->status instanceof StockStatus ? $s->status->value : (string) $s->status,
                ])
                ->all(),
        ];

        $sheet->fromArray($rows);

        $tmpBase = tempnam(sys_get_temp_dir(), 'stock-');
        if ($tmpBase && is_file($tmpBase)) {
            @unlink($tmpBase);
        }
        $path = ($tmpBase ?: sys_get_temp_dir().'/stock-'.uniqid()).'.xlsx';

        (new Xlsx($book))->save($path);

        return $path;
    }

    public function import(string $path, int $userId): int
    {
        $rows = IOFactory::load($path)->getActiveSheet()->toArray(null, true, true, true);
        $count = 0;

        foreach (array_slice($rows, 1) as $row) {
            $name = trim((string) ($row['A'] ?? ''));
            if ($name === '' || strtolower($name) === 'name' || strtolower($name) === 'nama barang') {
                continue;
            }

            $rawQty = $row['D'] ?? 0;
            $rawMin = $row['G'] ?? null;
            $rawPrice = $row['F'] ?? null;

            $data = [
                'name' => $name,
                'sku' => trim((string) ($row['B'] ?? '')) ?: null,
                'description' => trim((string) ($row['C'] ?? '')) ?: null,
                'quantity' => is_numeric($rawQty) ? max(0, (int) $rawQty) : 0,
                'unit' => trim((string) ($row['E'] ?? '')) ?: 'pcs',
                'price' => is_numeric($rawPrice) ? (float) $rawPrice : null,
                'min_stock' => ($rawMin !== null && $rawMin !== '' && is_numeric($rawMin)) ? max(0, (int) $rawMin) : 5,
            ];

            $v = Validator::make($data, [
                'name' => 'required|string|max:150',
                'quantity' => 'required|integer|min:0',
                'min_stock' => 'required|integer|min:0',
                'unit' => 'required|string|max:30',
                'price' => 'nullable|numeric|min:0',
            ]);

            if ($v->fails()) {
                continue;
            }

            $data['user_id'] = $userId;
            $data['status'] = StockStatus::fromQuantity($data['quantity'], $data['min_stock']);
            Stock::create($data);
            $count++;
        }

        return $count;
    }
}
