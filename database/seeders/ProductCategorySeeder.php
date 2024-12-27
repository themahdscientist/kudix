<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Over-the-Counter (OTC) Medications
            ['id' => 1, 'name' => 'pain-relievers', 'description' => 'Over-the-Counter (OTC) Medications'],
            ['id' => 2, 'name' => 'cold-and-flu-remedies', 'description' => 'Over-the-Counter (OTC) Medications'],
            ['id' => 3, 'name' => 'allergy-medications', 'description' => 'Over-the-Counter (OTC) Medications'],
            ['id' => 4, 'name' => 'antacids', 'description' => 'Over-the-Counter (OTC) Medications'],
            ['id' => 5, 'name' => 'vitamins-and-supplements', 'description' => 'Over-the-Counter (OTC) Medications'],
            ['id' => 6, 'name' => 'first-aid-supplies', 'description' => 'Over-the-Counter (OTC) Medications'],
            ['id' => 7, 'name' => 'sleep-aids', 'description' => 'Over-the-Counter (OTC) Medications'],
            ['id' => 8, 'name' => 'anti-diarrheal', 'description' => 'Over-the-Counter (OTC) Medications'],
            ['id' => 9, 'name' => 'laxatives', 'description' => 'Over-the-Counter (OTC) Medications'],

            // Prescription Medications
            ['id' => 10, 'name' => 'antibiotics', 'description' => 'Prescription Medications'],
            ['id' => 11, 'name' => 'antidepressants', 'description' => 'Prescription Medications'],
            ['id' => 12, 'name' => 'antihypertensives', 'description' => 'Prescription Medications'],
            ['id' => 13, 'name' => 'cardiovascular-medications', 'description' => 'Prescription Medications'],
            ['id' => 14, 'name' => 'diabetes-medications', 'description' => 'Prescription Medications'],
            ['id' => 15, 'name' => 'respiratory-medications', 'description' => 'Prescription Medications'],
            ['id' => 16, 'name' => 'oncology-medications', 'description' => 'Prescription Medications'],
            ['id' => 17, 'name' => 'antipsychotics', 'description' => 'Prescription Medications'],
            ['id' => 18, 'name' => 'antivirals', 'description' => 'Prescription Medications'],
            ['id' => 19, 'name' => 'hormone-replacement-therapy', 'description' => 'Prescription Medications'],
            ['id' => 20, 'name' => 'immunosuppressants', 'description' => 'Prescription Medications'],

            // Medical Devices
            ['id' => 21, 'name' => 'blood-pressure-monitors', 'description' => 'Medical Devices'],
            ['id' => 22, 'name' => 'glucose-meters', 'description' => 'Medical Devices'],
            ['id' => 23, 'name' => 'thermometers', 'description' => 'Medical Devices'],
            ['id' => 24, 'name' => 'nebulizers', 'description' => 'Medical Devices'],
            ['id' => 25, 'name' => 'hearing-aids', 'description' => 'Medical Devices'],
            ['id' => 26, 'name' => 'contact-lenses-and-solutions', 'description' => 'Medical Devices'],
            ['id' => 27, 'name' => 'wheelchairs', 'description' => 'Medical Devices'],
            ['id' => 28, 'name' => 'crutches', 'description' => 'Medical Devices'],
            ['id' => 29, 'name' => 'pulse-oximeters', 'description' => 'Medical Devices'],
            ['id' => 30, 'name' => 'oxygen-concentrators', 'description' => 'Medical Devices'],

            // Personal Care Products
            ['id' => 31, 'name' => 'skincare-products', 'description' => 'Personal Care Products'],
            ['id' => 32, 'name' => 'haircare-products', 'description' => 'Personal Care Products'],
            ['id' => 33, 'name' => 'cosmetics', 'description' => 'Personal Care Products'],
            ['id' => 34, 'name' => 'oral-hygiene-products', 'description' => 'Personal Care Products'],
            ['id' => 35, 'name' => 'baby-products', 'description' => 'Personal Care Products'],
            ['id' => 36, 'name' => 'feminine-hygiene-products', 'description' => 'Personal Care Products'],
            ['id' => 37, 'name' => 'adult-diapers', 'description' => 'Personal Care Products'],
            ['id' => 38, 'name' => 'sun-protection-products', 'description' => 'Personal Care Products'],
            ['id' => 39, 'name' => 'anti-dandruff-products', 'description' => 'Personal Care Products'],
            ['id' => 40, 'name' => 'anti-aging-products', 'description' => 'Personal Care Products'],

            // Other Categories
            ['id' => 41, 'name' => 'homeopathic-remedies', 'description' => 'Other Categories'],
            ['id' => 42, 'name' => 'herbal-supplements', 'description' => 'Other Categories'],
            ['id' => 43, 'name' => 'veterinary-products', 'description' => 'Other Categories'],
            ['id' => 44, 'name' => 'medical-equipment', 'description' => 'Other Categories'],
            ['id' => 45, 'name' => 'sexual-health-products', 'description' => 'Other Categories'],
            ['id' => 46, 'name' => 'weight-management-products', 'description' => 'Other Categories'],
            ['id' => 47, 'name' => 'protein-supplements', 'description' => 'Other Categories'],
            ['id' => 48, 'name' => 'aromatherapy-products', 'description' => 'Other Categories'],
            ['id' => 49, 'name' => 'essential-oils', 'description' => 'Other Categories'],
            ['id' => 50, 'name' => 'antiseptics-and-disinfectants', 'description' => 'Other Categories'],
            ['id' => 51, 'name' => 'orthopedic-supports', 'description' => 'Other Categories'],
            ['id' => 52, 'name' => 'anti-smoking-aids', 'description' => 'Other Categories'],
        ];

        foreach ($categories as $category) {
            ProductCategory::factory()->create($category);
        }
    }
}
