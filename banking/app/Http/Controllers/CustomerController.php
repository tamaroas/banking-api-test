<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Providers\AppServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    //register customer
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'=>'required|string',
            'firstname'=>'string|max:20',
            'phone'=>'string|max:13',
            'adress'=>'string',
            'email'=>'required|string|email|unique:users',
            'accounts' => 'required|array|min:1', // Au moins un compte est requis
            'accounts.*.balance' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'accounts.*.account_type' => 'required|string'
        ]);


        if($validator->fails() ) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        

        $customer = Customer::Create(array_merge(
            $validator->validated(),
            ['state'=>false], 
        ));

        foreach ($request->accounts as $accountData) {
            $accountData = array_merge($accountData, [
                'account_number' => self::generateAccountNumber(),
                'actived'=>true,
                'state'=>false
            ]);

            $customer->accounts()->create($accountData);
        }

        $customer->load('accounts');

        return response()->json(['message' => 'Customer created successfully', "customer"=>$customer], 201);
    }

    //get customer 

     public function index(){
        $customers = Customer::whereHas('accounts', function ($query) {
            $query->where('state', false);
        })->with(['accounts' => function ($query) {
            $query->where('state', false);
        }])->get();
    
        return response()->json(['customers' => $customers]);
     }


     //update customer informations
     public function Update($id, Request $request)
     {
        // Valider les données de la requête
        $validator = Validator::make($request->all(), [
            'name' => 'string',
            'firstname' => 'string|max:20',
            'phone' => 'string|max:13',
            'adress' => 'string',
            'email' => 'string|email|unique:customers,email,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Trouver le client à mettre à jour
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        // Mettre à jour les données du client
        $customer->update($request->all());

        return response()->json(['message' => 'Customer updated successfully', 'customer' => $customer]);
     }

     //logical dilate customer
     public function delete($id)
     {
        $customer = Customer::find($id);
        $customer->state = true;
        $customer->save();

        return response()->json(['message' => 'Customer deleted successfully'], 200);
     }




        //function of generation account_number
        public static function generateAccountNumber(): string
        {
            $randomDigits = mt_rand(100000, 999999);
            $randomSuffix = str_pad(mt_rand(0, 99), 2, '0', STR_PAD_LEFT);
            $accountNumber = "1003-$randomDigits-$randomSuffix";
            return $accountNumber;
        }
}
