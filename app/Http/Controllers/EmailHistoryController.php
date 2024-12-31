<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmailHistory;

class EmailHistoryController extends Controller
{
    public function showHistory()
    {
        $histories = EmailHistory::orderBy('sent_at', 'desc')->get();

        return response()->json($histories);
    }
}

