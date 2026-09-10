<?php

namespace App\Http\Controllers\Financeiro;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContasPagarController extends Controller
{
    public function index()
    {
        return \Inertia\Inertia::render('Financeiro/ContasPagar/Index');
    }
} 
