<?php

namespace App\Http\Controllers;

use App\Models\Saving;
use App\Models\Withdraw;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        // Fetching Saving records where the associated wish belongs to the authenticated user
        $wish = Saving::with('wish')
            ->whereHas('wish', function ($query) {
                $query->where('user_id', auth()->id()); // Filter wishes by authenticated user
            })
            ->get();
    
        // Fetching Withdraw records where the associated wish belongs to the authenticated user
        $withdraw = Withdraw::with('wish')
            ->whereHas('wish', function ($query) {
                $query->where('user_id', auth()->id()); // Filter wishes by authenticated user
            })
            ->get();
        
        // Merging the collections of Saving and Withdraw records
        $wish_withdraw = $wish->merge($withdraw);
    
        // Sorting the merged collection by 'date' in descending order
        $sorted = $wish_withdraw->sortByDesc(function ($item) {
            return $item->date; // Assuming 'date' is the correct field name
        })->values();
    
        // Returning the sorted data in a JSON response
        return response()->json([
            'data' => $sorted
        ], 200);
    }
    
}
