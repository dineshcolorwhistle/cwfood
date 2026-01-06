<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FsanzFoodsSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/fsanz/fsanz_foods_repaired_full.csv');

        if (!file_exists($path)) {
            dd("CSV not found at: " . $path);
        }

        $handle = fopen($path, 'r');

        // Read header row properly
        $header = fgetcsv($handle);

        $batch = [];
        $batchSize = 500;

        while (($row = fgetcsv($handle)) !== false) {

            // Skip blank or corrupt rows
            if (!$row || count($row) < 2) {
                continue;
            }

            // Validate column count
            if (count($header) !== count($row)) {
                Log::warning("FSANZ row skipped due to column mismatch", [
                    'expected' => count($header),
                    'actual'   => count($row),
                    'row'      => $row
                ]);
                continue;
            }
   
            $record = array_combine($header, $row);
            if (!$record) {
                continue;
            }

            /**
             * Convert true/false strings → 1/0
             */
            foreach ($record as $key => $value) {
                if (is_string($value)) {
                    $v = strtolower(trim($value));
                    if ($v === 'true')  $record[$key] = 1;
                    if ($v === 'false') $record[$key] = 0;
                }
            }

            /**
             * JSON fields that must be valid JSON strings
             */
            $jsonFields = [
                'estimated_ingredients',
                'estimated_allergens',
                'estimated_hazards',
                'estimated_processing_info',
                'estimated_dietary_status',
                'estimated_regulatory_info',
                'estimated_typical_uses',
                'estimated_origin',
                'alternative_origin_sources',
                'functional_category',
            ];

            foreach ($jsonFields as $field) {

                $raw = $record[$field] ?? null;

                // Handle missing or empty values
                if ($raw === null || trim($raw) === '') {
                    // functional_category is NOT NULL → must always be JSON
                    $record[$field] = ($field === 'functional_category') ? '[]' : null;
                    continue;
                }

                // Attempt JSON decode
                $decoded = json_decode($raw, true);

                if (json_last_error() === JSON_ERROR_NONE) {

                    // Must store JSON as STRING (not PHP array)
                    $jsonString = json_encode($decoded, JSON_UNESCAPED_UNICODE);

                    // Ensure JSON_valid() passes
                    $record[$field] = $jsonString;

                } else {
                    // JSON is invalid → avoid MySQL CHECK failure
                    Log::warning("Invalid JSON in FSANZ CSV", [
                        'field' => $field,
                        'value' => $raw,
                    ]);

                    // For functional_category, fallback to empty array
                    $record[$field] = ($field === 'functional_category') ? '[]' : null;
                }
            }

            /**
             * Remove `id` from CSV so MySQL auto increments
             */
            unset($record['id']);

            /**
             * Add timestamps manually
             */
            $record['created_at'] = now();
            $record['last_updated'] = now();
            $record['last_ai_analysis'] = now();

            /**
             * Add to batch
             */
            $batch[] = $record;

            if (count($batch) >= $batchSize) {
                DB::table('fsanz_foods')->insert($batch);
                $batch = [];
            }
        }

        /**
         * Insert any remaining items
         */
        if (!empty($batch)) {
            DB::table('fsanz_foods')->insert($batch);
        }

        fclose($handle);

        echo "FSANZ foods import completed.\n";
    }
}
