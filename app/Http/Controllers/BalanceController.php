<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BalanceController extends Controller
{
    public function index()
    {
        // if theres no balance handle error
        if (!Auth::user()->balance) {
            return response()->json([
                'message' => 'No balance found'
            ], 404);
        }
         $balance = Auth::user()->balance;
         return response()->json($balance);
    }

    public function store(){
        if (!Auth::user()->balance) {
            Balance::create([
                'user_id' => Auth::user()->id,
                'nominal' => 0
            ]);
            return response()->json(Auth::user()->balance);
        }
    }
}
