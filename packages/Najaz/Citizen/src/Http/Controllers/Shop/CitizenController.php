<?php

namespace Najaz\Citizen\Http\Controllers\Shop;

use Illuminate\View\View;
use Webkul\Shop\Http\Controllers\Controller;

class CitizenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('citizens::shop.index');
    }
}
