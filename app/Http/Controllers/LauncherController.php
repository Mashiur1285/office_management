<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class LauncherController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Launcher');
    }
}
