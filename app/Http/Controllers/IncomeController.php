<?php

namespace App\Http\Controllers;

use App\Models\Income;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function index()
    {
        $income = Income::with('balance')
            ->whereHas('balance', function ($query) {
                $query->where('user_id', auth()->id()); 
            })
            ->get();

        return response()->json([
            'data' => $income
        ], 200);
    }

    public function store(Request $request){
        $data = $request->all();
        $user = auth()->user();
        $balance = $user->balance;
        $balance->nominal += $request->nominal;
        $balance->save();
        $income = Income::create([
            'nominal' => $request->nominal,
            'description' => $request->description,
            'balance_id' => $balance->id,
            'date' => date('Y-m-d')
        ]);
        return response()->json([
            'data' => $income,
            'message' => 'Income created successfully'
        ], 200);
    }
}
