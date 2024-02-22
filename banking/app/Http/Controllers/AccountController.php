<?php

namespace App\Http\Controllers;

use App\Http\Controllers\CustomerController;
use App\Models\Account;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{

    public function store ($customer_id,Request $request){
        $validator = Validator::make($request->all(), [
            'balance'=>'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'account_type'=>'required|string'
        ]);

        if($validator->fails() ) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $account = new Account([
            'account_number'=>CustomerController::generateAccountNumber(),
            'balance'=>$request->balance,
            'account_type'=>$request->account_type,
            'actived'=>true,
            'state'=>false
        ]);

        $customer = Customer::find($customer_id);

        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        $account->customer()->associate($customer);

        $account->save();

        return response()->json(['message' => 'Customer created account successfully', "account"=>$account], 201);
    }

    //logical dilate customer
    public function delete($id)
    {
       $account = Account::find($id);
       $account->state = true;
       $account->save();

       return response()->json(['message' => 'Account deleted successfully'], 200);
    }

}
