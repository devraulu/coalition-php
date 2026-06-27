<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InventoryController extends Controller
{
    private function dataPath(): string
    {
        return storage_path('app/inventory.json');
    }

    private function readItems(): array
    {
        $path = $this->dataPath();

        if (!file_exists($path)) {
            return [];
        }

        $items = json_decode(file_get_contents($path), true);

        return is_array($items) ? $items : [];
    }

    private function writeItems(array $items): void
    {
        file_put_contents($this->dataPath(), json_encode($items, JSON_PRETTY_PRINT));
    }

    private function sortByDate(array $items): array
    {
        usort($items, fn ($a, $b) => strcmp($a['datetime'], $b['datetime']));

        return $items;
    }

    public function index()
    {
        $items = $this->sortByDate($this->readItems());

        return view('inventory', [
            'items' => $items,
            'total' => array_sum(array_column($items, 'total')),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
        ]);

        $items = $this->readItems();

        $items[] = [
            'product_name' => $validated['product_name'],
            'quantity' => (int) $validated['quantity'],
            'price' => (float) $validated['price'],
            'datetime' => now()->format('Y-m-d H:i:s'),
            'total' => round($validated['quantity'] * $validated['price'], 2),
        ];

        $this->writeItems($items);

        $items = $this->sortByDate($items);

        return response()->json([
            'items' => $items,
            'total' => array_sum(array_column($items, 'total')),
        ]);
    }
}
