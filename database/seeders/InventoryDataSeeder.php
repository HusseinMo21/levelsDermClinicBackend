<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventoryItem;
use App\Models\ToolWithdrawal;
use App\Models\ToolRequest;
use App\Models\InventoryNotification;
use App\Models\Doctor;
use App\Models\User;
use Carbon\Carbon;

class InventoryDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample inventory items
        $inventoryItems = [
            [
                'item_code' => 'ITM001',
                'name' => 'شاش',
                'description' => 'شاش طبي معقم',
                'category' => 'مواد طبية',
                'subcategory' => 'ضمادات',
                'unit_of_measure' => 'قطعة',
                'unit_cost' => 2.50,
                'current_stock' => 500,
                'unit_price' => 3.00,
                'minimum_stock_level' => 50,
                'maximum_stock_level' => 1000,
                'has_expiry_date' => false,
                'is_active' => true,
                'status' => 'active',
                'storage_location' => 'المخزن الرئيسي',
                'created_by' => 1,
            ],
            [
                'item_code' => 'ITM002',
                'name' => 'قطن',
                'description' => 'قطن طبي معقم',
                'category' => 'مواد طبية',
                'subcategory' => 'ضمادات',
                'unit_of_measure' => 'قطعة',
                'unit_cost' => 1.50,
                'current_stock' => 300,
                'unit_price' => 2.00,
                'minimum_stock_level' => 30,
                'maximum_stock_level' => 500,
                'has_expiry_date' => false,
                'is_active' => true,
                'status' => 'active',
                'storage_location' => 'المخزن الرئيسي',
                'created_by' => 1,
            ],
            [
                'item_code' => 'ITM003',
                'name' => 'حقن',
                'description' => 'حقن طبية معقمة',
                'category' => 'أدوات طبية',
                'subcategory' => 'حقن',
                'unit_of_measure' => 'قطعة',
                'unit_cost' => 0.50,
                'current_stock' => 200,
                'unit_price' => 0.75,
                'minimum_stock_level' => 20,
                'maximum_stock_level' => 300,
                'has_expiry_date' => true,
                'shelf_life_days' => 1095, // 3 years
                'is_active' => true,
                'status' => 'active',
                'storage_location' => 'المخزن الرئيسي',
                'created_by' => 1,
            ],
            [
                'item_code' => 'ITM004',
                'name' => 'سرينجات',
                'description' => 'سرينجات طبية',
                'category' => 'أدوات طبية',
                'subcategory' => 'حقن',
                'unit_of_measure' => 'قطعة',
                'unit_cost' => 1.00,
                'current_stock' => 150,
                'unit_price' => 1.50,
                'minimum_stock_level' => 15,
                'maximum_stock_level' => 200,
                'has_expiry_date' => true,
                'shelf_life_days' => 1095,
                'is_active' => true,
                'status' => 'active',
                'storage_location' => 'المخزن الرئيسي',
                'created_by' => 1,
            ],
            [
                'item_code' => 'ITM005',
                'name' => 'أدوات تعقيم',
                'description' => 'أدوات تعقيم طبية',
                'category' => 'أدوات طبية',
                'subcategory' => 'تعقيم',
                'unit_of_measure' => 'قطعة',
                'unit_cost' => 5.00,
                'current_stock' => 100,
                'unit_price' => 7.50,
                'minimum_stock_level' => 10,
                'maximum_stock_level' => 150,
                'has_expiry_date' => true,
                'shelf_life_days' => 730, // 2 years
                'is_active' => true,
                'status' => 'active',
                'storage_location' => 'المخزن الرئيسي',
                'created_by' => 1,
            ],
            [
                'item_code' => 'ITM006',
                'name' => 'قفازات',
                'description' => 'قفازات طبية معقمة',
                'category' => 'مواد طبية',
                'subcategory' => 'حماية',
                'unit_of_measure' => 'زوج',
                'unit_cost' => 0.25,
                'current_stock' => 50,
                'unit_price' => 0.40,
                'minimum_stock_level' => 5,
                'maximum_stock_level' => 100,
                'has_expiry_date' => true,
                'shelf_life_days' => 1095,
                'is_active' => true,
                'status' => 'active',
                'storage_location' => 'المخزن الرئيسي',
                'created_by' => 1,
            ],
        ];

        foreach ($inventoryItems as $item) {
            InventoryItem::create($item);
        }

        // Get doctors for withdrawals
        $doctors = Doctor::with('user')->get();

        // Create sample tool withdrawals
        $withdrawals = [
            [
                'withdrawal_number' => 'WD001',
                'inventory_item_id' => 1, // شاش
                'doctor_id' => 1,
                'quantity' => 250,
                'operation_name' => '8 يونيو - 5:00م',
                'notes' => 'لعملية تجميلية',
                'status' => 'completed',
                'withdrawn_by' => 1,
                'withdrawal_date' => Carbon::parse('2025-12-11'),
            ],
            [
                'withdrawal_number' => 'WD002',
                'inventory_item_id' => 2, // قطن
                'doctor_id' => 2,
                'quantity' => 458,
                'operation_name' => '6 مارس - 2:00م',
                'notes' => 'لعملية جراحية',
                'status' => 'completed',
                'withdrawn_by' => 1,
                'withdrawal_date' => Carbon::parse('2025-08-01'),
            ],
            [
                'withdrawal_number' => 'WD003',
                'inventory_item_id' => 3, // حقن
                'doctor_id' => 1,
                'quantity' => 654,
                'operation_name' => '5 يناير - 5:00م',
                'notes' => 'لحقن البوتوكس',
                'status' => 'completed',
                'withdrawn_by' => 1,
                'withdrawal_date' => Carbon::parse('2025-08-08'),
            ],
            [
                'withdrawal_number' => 'WD004',
                'inventory_item_id' => 4, // سرينجات
                'doctor_id' => 2,
                'quantity' => 820,
                'operation_name' => '4 يونيو - 2:00م',
                'notes' => 'لعملية تجميلية',
                'status' => 'completed',
                'withdrawn_by' => 1,
                'withdrawal_date' => Carbon::parse('2025-08-09'),
            ],
            [
                'withdrawal_number' => 'WD005',
                'inventory_item_id' => 5, // أدوات تعقيم
                'doctor_id' => 1,
                'quantity' => 500,
                'operation_name' => '2 يونيو - 2:00م',
                'notes' => 'لتعقيم الأدوات',
                'status' => 'completed',
                'withdrawn_by' => 1,
                'withdrawal_date' => Carbon::parse('2025-08-15'),
            ],
        ];

        foreach ($withdrawals as $withdrawal) {
            ToolWithdrawal::create($withdrawal);
        }

        // Create sample tool requests
        $requests = [
            [
                'request_number' => 'TR001',
                'doctor_id' => 1,
                'inventory_item_id' => 6, // قفازات
                'requested_quantity' => 100,
                'status' => 'pending',
                'reason' => 'نفاد المخزون',
                'requested_by' => 1,
                'requested_at' => Carbon::now()->subHours(2),
            ],
            [
                'request_number' => 'TR002',
                'doctor_id' => 2,
                'inventory_item_id' => 1, // شاش
                'requested_quantity' => 50,
                'status' => 'pending',
                'reason' => 'عملية جراحية',
                'requested_by' => 1,
                'requested_at' => Carbon::now()->subHours(5),
            ],
        ];

        foreach ($requests as $request) {
            ToolRequest::create($request);
        }

        // Create sample notifications
        $notifications = [
            [
                'title' => 'سحب أداة',
                'message' => 'تم سحب 20 وحدة من القفازات',
                'type' => 'withdrawal',
                'priority' => 'medium',
                'is_read' => false,
                'created_at' => Carbon::now()->subMinutes(22),
            ],
            [
                'title' => 'طلب توريد جديد',
                'message' => 'طلب توريد جديد قيد الانتظار',
                'type' => 'supply_request',
                'priority' => 'high',
                'is_read' => false,
                'created_at' => Carbon::parse('2025-02-11'),
            ],
            [
                'title' => 'مخزون منخفض',
                'message' => 'أداة سرنجة 5 مل على وشك النفاد',
                'type' => 'low_stock',
                'priority' => 'urgent',
                'is_read' => false,
                'created_at' => Carbon::parse('2025-02-10'),
            ],
        ];

        foreach ($notifications as $notification) {
            InventoryNotification::create($notification);
        }
    }
}