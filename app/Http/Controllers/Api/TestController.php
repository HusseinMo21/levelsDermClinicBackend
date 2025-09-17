<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 */
class TestController extends Controller
{
    /**
     *     path="/api/test",
     *     summary="Test endpoint",
     *     description="Simple test endpoint",
     *     tags={"Test"},
     *         response=200,
     *         description="Success"
     *     )
     * )
     */
    public function test(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Test successful',
            'data' => ['test' => 'value']
        ]);
    }
}
