<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContactGroupController extends Controller
{
    /**
     * Get list of available contact groups (POST method)
     */
    public function index(Request $request)
    {
        // Predefined groups from your UI
        $groups = [
            'Clients',
            'Partners',
            'Team',
            'Family',
            'Prospects',
            'Vendors',
            'Friends',
            'Colleagues',
        ];

        return response()->json([
            'data' => $groups,
            'message' => 'Contact groups retrieved successfully.',
        ]);
    }
}

