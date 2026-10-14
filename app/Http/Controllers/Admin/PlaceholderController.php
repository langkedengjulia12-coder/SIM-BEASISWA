<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlaceholderController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    public function create()
    {
        return view('admin.dashboard');
    }

    public function store(Request $request)
    {
        return redirect()->route('admin.dashboard');
    }

    public function show($id)
    {
        return view('admin.dashboard', compact('id'));
    }

    public function edit($id)
    {
        return view('admin.dashboard', compact('id'));
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('admin.dashboard');
    }

    public function destroy($id)
    {
        return redirect()->route('admin.dashboard');
    }

    public function notificationsIndex()
    {
        return view('admin.dashboard');
    }

    public function notificationsShow($id)
    {
        return view('admin.dashboard', compact('id'));
    }

    public function notificationsMarkAllRead()
    {
        return redirect()->route('admin.dashboard');
    }
}
