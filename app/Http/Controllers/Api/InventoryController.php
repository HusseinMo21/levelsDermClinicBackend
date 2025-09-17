<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\ToolRequest;
use App\Models\ToolWithdrawal;
use App\Models\InventoryNotification;
use App\Models\PurchaseOrder;
use App\Http\Requests\StoreInventoryItemRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 */
class InventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function getDashboard(): JsonResponse
    {
        try {
            // Calculate KPIs
            $totalTools = InventoryItem::where('is_active', true)->count();
            $categories = InventoryItem::where('is_active', true)->distinct('category')->count();
            $expiredCategories = InventoryItem::where('is_active', true)
                ->whereHas('batches', function($query) {
                    $query->where('expiry_date', '<', now());
                })->count();
            $newToolRequests = ToolRequest::where('status', 'pending')
                ->where('requested_at', '>=', now()->subDays(7))
                ->count();
            $pendingSupplyOrders = PurchaseOrder::where('status', 'pending')->count();

            $kpis = [
                'total_tools' => $totalTools,
                'categories' => $categories,
                'expired_categories' => $expiredCategories,
                'new_tool_requests' => $newToolRequests,
                'pending_supply_orders' => $pendingSupplyOrders,
            ];

            // Get recent notifications
            $notifications = InventoryNotification::with(['relatedUser', 'relatedItem'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'type' => $notification->type,
                        'priority' => $notification->priority,
                        'is_read' => $notification->is_read,
                        'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                        'time_ago' => $notification->created_at->diffForHumans(),
                    ];
                });

            // Get recent withdrawals
            $recentWithdrawals = ToolWithdrawal::with(['inventoryItem', 'doctor.user'])
                ->orderBy('withdrawal_date', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($withdrawal) {
                    return [
                        'id' => $withdrawal->id,
                        'tool_name' => $withdrawal->inventoryItem->name ?? 'غير محدد',
                        'operation' => $withdrawal->operation_name ?? 'غير محدد',
                        'date' => $withdrawal->withdrawal_date->format('d/m/Y'),
                        'used_by' => $withdrawal->doctor->user->name ?? 'غير محدد',
                        'quantity' => $withdrawal->quantity,
                        'notes' => $withdrawal->notes ?? '',
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'تم جلب بيانات المخزن بنجاح',
                'data' => [
                    'kpis' => $kpis,
                    'notifications' => $notifications,
                    'recent_withdrawals' => $recentWithdrawals,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب بيانات المخزن',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getWithdrawals(Request $request): JsonResponse
    {
        try {
            $query = ToolWithdrawal::with(['inventoryItem', 'doctor.user']);

            // Search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('inventoryItem', function ($itemQuery) use ($search) {
                        $itemQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('operation_name', 'like', "%{$search}%");
                });
            }

            // Doctor filter
            if ($request->filled('doctor_id')) {
                $query->where('doctor_id', $request->doctor_id);
            }

            // Date filter
            if ($request->filled('date')) {
                $query->whereDate('withdrawal_date', $request->date);
            }

            $perPage = $request->get('per_page', 15);
            $withdrawals = $query->orderBy('withdrawal_date', 'desc')->paginate($perPage);

            $formattedWithdrawals = $withdrawals->map(function ($withdrawal) {
                return [
                    'id' => $withdrawal->id,
                    'tool_name' => $withdrawal->inventoryItem->name ?? 'غير محدد',
                    'operation' => $withdrawal->operation_name ?? 'غير محدد',
                    'date' => $withdrawal->withdrawal_date->format('d/m/Y'),
                    'used_by' => $withdrawal->doctor->user->name ?? 'غير محدد',
                    'quantity' => $withdrawal->quantity,
                    'notes' => $withdrawal->notes ?? '',
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'تم جلب جدول الأدوات المسحوبة بنجاح',
                'data' => $formattedWithdrawals,
                'pagination' => [
                    'current_page' => $withdrawals->currentPage(),
                    'last_page' => $withdrawals->lastPage(),
                    'per_page' => $withdrawals->perPage(),
                    'total' => $withdrawals->total(),
                    'from' => $withdrawals->firstItem(),
                    'to' => $withdrawals->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب جدول الأدوات المسحوبة',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getNotifications(Request $request): JsonResponse
    {
        try {
            $query = InventoryNotification::with(['relatedUser', 'relatedItem']);

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            if ($request->has('is_read')) {
                $query->where('is_read', $request->boolean('is_read'));
            }

            $notifications = $query->orderBy('created_at', 'desc')->paginate(20);

            $formattedNotifications = $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'priority' => $notification->priority,
                    'is_read' => $notification->is_read,
                    'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                    'time_ago' => $notification->created_at->diffForHumans(),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'تم جلب الإشعارات بنجاح',
                'data' => $formattedNotifications,
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الإشعارات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     *     path="/api/inventory/items",
     *     summary="Add new tool to inventory",
     *     description="Add a new tool/item to the inventory system with all required information",
     *     tags={"Inventory For Admin View"},
     *     security={{"bearerAuth":{}}},
     *         required=true,
     *             required={"name","category","quantity","date","supplier"},
     *         )
     *     ),
     *         response=201,
     *         description="Tool added successfully",
     *                 property="data",
     *                 type="object",
     *             )
     *         )
     *     ),
     *         response=422,
     *         description="Validation error",
     *         )
     *     )
     * )
     */
    public function store(StoreInventoryItemRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Generate unique item code
            $lastItem = InventoryItem::orderBy('id', 'desc')->first();
            $nextNumber = $lastItem ? (intval(substr($lastItem->item_code, 3)) + 1) : 1;
            $itemCode = 'ITM' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            // Create the inventory item with default values
            $inventoryItem = InventoryItem::create([
                'item_code' => $itemCode,
                'name' => $request->name,
                'description' => $request->notes,
                'category' => $request->category,
                'subcategory' => null, // Default value
                'unit_of_measure' => 'قطعة', // Default value
                'unit_cost' => 0, // Default value
                'current_stock' => $request->quantity,
                'unit_price' => 0, // Default value
                'minimum_stock_level' => 10, // Default value
                'maximum_stock_level' => 100, // Default value
                'has_expiry_date' => false, // Default value
                'shelf_life_days' => null, // Default value
                'requires_prescription' => false,
                'storage_conditions' => null, // Default value
                'usage_instructions' => null, // Default value
                'is_active' => true,
                'status' => 'active',
                'storage_location' => 'المخزن الرئيسي', // Default value
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            // Create a notification for the new item
            InventoryNotification::create([
                'title' => 'أداة جديدة',
                'message' => "تم إضافة أداة جديدة: {$inventoryItem->name} - الكمية: {$request->quantity}",
                'type' => 'new_item',
                'priority' => 'medium',
                'related_item_id' => $inventoryItem->id,
                'metadata' => [
                    'item_name' => $inventoryItem->name,
                    'quantity' => $request->quantity,
                    'supplier' => $request->supplier,
                    'date_added' => $request->date,
                ],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة الأداة بنجاح',
                'data' => [
                    'id' => $inventoryItem->id,
                    'item_code' => $inventoryItem->item_code,
                    'name' => $inventoryItem->name,
                    'category' => $inventoryItem->category,
                    'quantity' => $inventoryItem->current_stock,
                    'supplier' => $request->supplier,
                    'date_added' => $request->date,
                    'notes' => $inventoryItem->notes,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة الأداة',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/inventory/requests",
     *     summary="Get withdrawal requests table for admin",
     *     description="Get paginated list of tool withdrawal requests from doctors with search and filters - Admin only",
     *     tags={"Inventory For Admin View"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for request number, doctor name, or tool name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by request status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "approved", "rejected", "fulfilled"})
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Filter by request date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="تم جلب جدول الطلبات بنجاح"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="request_number", type="string", example="#124"),
     *                     @OA\Property(property="doctor_name", type="string", example="د محمد سامي"),
     *                     @OA\Property(property="requested_tool", type="string", example="كحول طبي"),
     *                     @OA\Property(property="quantity", type="integer", example=250),
     *                     @OA\Property(property="date", type="string", example="11/12/2025"),
     *                     @OA\Property(property="condition", type="string", example="جديد"),
     *                     @OA\Property(property="status", type="string", example="pending"),
     *                     @OA\Property(property="reason", type="string", example="لعملية تجميلية"),
     *                     @OA\Property(property="created_at", type="string", example="2025-09-17T10:30:00Z")
     *                 )
     *             ),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     )
     * )
     */
    public function getRequests(Request $request): JsonResponse
    {
        try {
            $query = ToolRequest::with(['doctor.user', 'item']);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                      ->orWhereHas('doctor.user', function ($subQ) use ($search) {
                          $subQ->where('display_name', 'like', "%{$search}%")
                               ->orWhere('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('item', function ($subQ) use ($search) {
                          $subQ->where('name', 'like', "%{$search}%");
                      });
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('date')) {
                $query->whereDate('created_at', $request->date);
            }

            $perPage = $request->get('per_page', 15);
            $requests = $query->orderBy('created_at', 'desc')->paginate($perPage);

            // Map status to Arabic
            $statusMap = [
                'pending' => 'جديد',
                'approved' => 'قيد التنفيذ',
                'rejected' => 'مرفوض',
                'fulfilled' => 'منتهي',
            ];

            $formattedRequests = $requests->map(function ($request) use ($statusMap) {
                $doctorName = $request->doctor->user->display_name ?? $request->doctor->user->name ?? 'غير محدد';
                if ($request->doctor->user->hasRole('doctor')) {
                    $doctorName = 'د. ' . $request->doctor->full_name;
                }

                return [
                    'id' => $request->id,
                    'request_number' => '#' . str_pad($request->id, 3, '0', STR_PAD_LEFT),
                    'doctor_name' => $doctorName,
                    'requested_tool' => $request->item->name ?? 'غير محدد',
                    'quantity' => $request->quantity,
                    'date' => $request->created_at->format('d/m/Y'),
                    'condition' => $statusMap[$request->status] ?? $request->status,
                    'status' => $request->status,
                    'reason' => $request->reason ?? '',
                    'created_at' => $request->created_at->toDateTimeString(),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'تم جلب جدول الطلبات بنجاح',
                'data' => $formattedRequests,
                'pagination' => [
                    'current_page' => $requests->currentPage(),
                    'last_page' => $requests->lastPage(),
                    'per_page' => $requests->perPage(),
                    'total' => $requests->total(),
                    'from' => $requests->firstItem(),
                    'to' => $requests->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب جدول الطلبات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/inventory/expired",
     *     summary="Get expired items for admin",
     *     description="Get expired and nearly expired inventory items with real data alert - Admin only",
     *     tags={"Inventory For Admin View"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for tool name or category",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter by category",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Filter by expiry date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="تم جلب الاصناف المنتهية بنجاح"),
     *             @OA\Property(
     *                 property="alert",
     *                 type="object",
     *                 @OA\Property(property="completely_expired", type="integer", example=3),
     *                 @OA\Property(property="nearly_expired", type="integer", example=2),
     *                 @OA\Property(property="message", type="string", example="يوجد 3 أدوات منتهية تمامًا و2 أدوات على وشك النفاد. يرجى مراجعة المخزون وإرسال طلبات التوريد اللازمة في أسرع وقت")
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="tool_name", type="string", example="جوانتيات طبية"),
     *                     @OA\Property(property="category", type="string", example="أدوات حماية"),
     *                     @OA\Property(property="remaining_quantity", type="integer", example=250),
     *                     @OA\Property(property="last_used_date", type="string", example="11/12/2025"),
     *                     @OA\Property(property="expiry_status", type="string", example="expired"),
     *                     @OA\Property(property="expiry_date", type="string", example="2025-09-15"),
     *                     @OA\Property(property="days_until_expiry", type="integer", example=-5)
     *                 )
     *             ),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     )
     * )
     */
    public function getExpiredItems(Request $request): JsonResponse
    {
        try {
            $query = InventoryItem::with(['batches']);

            // Filter for items with expiry dates
            $query->where('has_expiry_date', true);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('category', 'like', "%{$search}%");
                });
            }

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            if ($request->filled('date')) {
                $query->whereHas('batches', function ($q) use ($request) {
                    $q->whereDate('expiry_date', $request->date);
                });
            }

            $perPage = $request->get('per_page', 15);
            $items = $query->orderBy('name')->paginate($perPage);

            // Calculate expiry statistics
            $completelyExpired = 0;
            $nearlyExpired = 0;
            $expiredItems = [];

            foreach ($items as $item) {
                $expiredBatches = $item->batches->filter(function ($batch) {
                    return $batch->expiry_date && $batch->expiry_date->isPast();
                });

                $nearlyExpiredBatches = $item->batches->filter(function ($batch) {
                    return $batch->expiry_date && 
                           $batch->expiry_date->isFuture() && 
                           $batch->expiry_date->diffInDays(now()) <= 30;
                });

                if ($expiredBatches->count() > 0) {
                    $completelyExpired++;
                } elseif ($nearlyExpiredBatches->count() > 0) {
                    $nearlyExpired++;
                }

                // Get the most recent batch for last used date
                $latestBatch = $item->batches->sortByDesc('created_at')->first();
                $lastUsedDate = $latestBatch ? $latestBatch->created_at->format('d/m/Y') : 'لم يتم الاستخدام';

                // Determine expiry status
                $expiryStatus = 'active';
                $daysUntilExpiry = null;
                $expiryDate = null;

                if ($expiredBatches->count() > 0) {
                    $expiryStatus = 'expired';
                    $expiryDate = $expiredBatches->first()->expiry_date->format('Y-m-d');
                    $daysUntilExpiry = $expiredBatches->first()->expiry_date->diffInDays(now());
                } elseif ($nearlyExpiredBatches->count() > 0) {
                    $expiryStatus = 'nearly_expired';
                    $expiryDate = $nearlyExpiredBatches->first()->expiry_date->format('Y-m-d');
                    $daysUntilExpiry = $nearlyExpiredBatches->first()->expiry_date->diffInDays(now());
                }

                $expiredItems[] = [
                    'id' => $item->id,
                    'tool_name' => $item->name,
                    'category' => $item->category,
                    'remaining_quantity' => $item->current_stock,
                    'last_used_date' => $lastUsedDate,
                    'expiry_status' => $expiryStatus,
                    'expiry_date' => $expiryDate,
                    'days_until_expiry' => $daysUntilExpiry,
                ];
            }

            // Create alert message
            $alertMessage = "يوجد {$completelyExpired} أدوات منتهية تمامًا و{$nearlyExpired} أدوات على وشك النفاد. يرجى مراجعة المخزون وإرسال طلبات التوريد اللازمة في أسرع وقت";

            return response()->json([
                'success' => true,
                'message' => 'تم جلب الاصناف المنتهية بنجاح',
                'alert' => [
                    'completely_expired' => $completelyExpired,
                    'nearly_expired' => $nearlyExpired,
                    'message' => $alertMessage,
                ],
                'data' => $expiredItems,
                'pagination' => [
                    'current_page' => $items->currentPage(),
                    'last_page' => $items->lastPage(),
                    'per_page' => $items->perPage(),
                    'total' => $items->total(),
                    'from' => $items->firstItem(),
                    'to' => $items->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الاصناف المنتهية',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
