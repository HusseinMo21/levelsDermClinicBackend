<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(name="Test")
 */
class TestController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/test",
     *     summary="Test endpoint",
     *     description="Simple test endpoint",
     *     tags={"Test"},
     *     @OA\Response(
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
