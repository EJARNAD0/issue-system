<?php

namespace App\Http\Controllers;

class IssueController extends Controller
{
    public function store()
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'priority' => 'required',
        ]);
    }
}
