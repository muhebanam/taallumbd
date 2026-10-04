<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $orders = Order::where('user_id', $user->id)
            ->with(['course:id,title'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Student/Orders', [
            'orders' => $orders,
        ]);
    }
}
