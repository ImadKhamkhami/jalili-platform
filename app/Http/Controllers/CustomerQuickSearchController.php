<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerQuickSearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $search = trim($request->search);

        if (!$search) {
            abort(404);
        }

        $customer = Customer::where('name', 'like', "%{$search}%")
            ->orWhere('national_id', 'like', "%{$search}%")
            ->first();

        if (!$customer) {
            abort(404, 'الزبون غير موجود');
        }

       return redirect()->route('customers.index', [
    'search' => $search,
]);

    }
}

