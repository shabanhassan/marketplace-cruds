<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {}

    /**
     * Useful order statistics for the merchant API.
     * 
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        // TODO: Complete this method
        $orders     = $request->user()->merchant->orders;
        $orders     = $orders->whereBetween('created_at', [$request['from'], $request['to']])->where('payout_status',"unpaid");
        
        // dd($orders->pluck('affiliate_id'));
        return response()->json([
            'count'             => $orders->count(),
            'commissions_owed'  => $orders->whereNotNull('affiliate_id')->sum('commission_owed'),
            'revenue'           => $orders->sum('subtotal')
        ],200);
    }
}
