<?php

namespace ME\SchoolManagement\Http\Controllers;

use Illuminate\Http\Request;
use ME\Http\Controllers\Controller;

class SchoolManagementController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'Welcome to the School Management API', 'version' => '1.0']);
    }
}
