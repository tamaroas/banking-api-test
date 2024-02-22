<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Account;
use App\Models\transaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class transactionController extends Controller
{
    // credite account
    public function creditAccount(Request $request){
        
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'source_account' => 'required|string'
        ]);

        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try{
            //start transaction
            DB::beginTransaction();
    
            // Search for the account with the given account number
            $account = Account::where('account_number', $request->source_account)->first();
    
            if (!$account) {
                return response()->json(['error' => 'Account not found'], 404);
            }

            $new_amount = $account->balance + $request->amount;
            $account->balance = $new_amount;
            $account->save();
    
            transaction::create(array_merge(
                $validator->validated(),
                ['type'=>"deposit"]
            ));

            DB::commit();
    
            return response()->json(['message' => 'account credited successfully', "account"=>$account], 200);
            
        }catch(Exception $e){
            DB::rollBack();

            return response()->json([
                'message' => 'a problem occurred the transaction could not be completed',
                'error'=>$e->getMessage()
        ], 400);
        }
       
    }

    //debite account
    public function debitAccount (Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'source_account' => 'required|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try{
            //start transaction
            DB::beginTransaction();
    
            // Search for the account with the given account number
            $account = Account::where('account_number', $request->source_account)->first();
    
            if (!$account) {
                return response()->json(['error' => 'Account not found'], 404);
            }

            if($account->balance < $request->amount){return response()->json(['error' => 'insufficient balance'], 200);}

            $new_amount = $account->balance - $request->amount;
            $account->balance = $new_amount;
            $account->save();
    
            transaction::create(array_merge(
                $validator->validated(),
                ['type'=>"withdrawal"]
            ));

            DB::commit();
    
            return response()->json(['message' => 'account debited successfully', "account"=>$account], 200);
            
        }catch(Exception $e){
            DB::rollBack();

            return response()->json([
                'message' => 'a problem occurred the transaction could not be completed',
                'error'=>$e->getMessage()
        ], 400);
        }
    }

    //account to account transfer
    public function payment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'source_account' => 'required|string',
            'target_account' => 'required|string'
        ]);

        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try{
            //start transaction
            DB::beginTransaction();
    
            // Search for the account with the given account number
            $accountsource = Account::where('account_number', $request->source_account)->first();
            $accounttarget = Account::where('account_number', $request->target_account)->first();
    
            if (!$accountsource) {
                return response()->json(['error' => 'Not found source account'], 404);
            }

            if (!$accounttarget) {
                return response()->json(['error' => 'Not found target account'], 404);
            }

            if($accountsource->balance < $request->amount){return response()->json(['error' => 'insufficient balance'], 200);}

            $new_amount_source = $accountsource->balance - $request->amount;
            $accountsource->balance = $new_amount_source;
            $accountsource->save();

            $new_amount_target = $accounttarget->balance + $request->amount;
            $accounttarget->balance = $new_amount_target;
            $accounttarget->save();
    
            transaction::create(array_merge(
                $validator->validated(),
                ['type'=>"payment"]
            ));

            DB::commit();
    
            return response()->json(['message' => 'payment successfully completed', "account"=>$accountsource], 200);
            
        }catch(Exception $e){
            DB::rollBack();

            return response()->json([
                'message' => 'a problem occurred the transaction could not be completed',
                'error'=>$e->getMessage()
        ], 400);
        }

    }

    //transaction historical
    public function transactionHistorical(Request $request)
    {
        $transactions = transaction::where('source_account', $request->account_number)
                                        ->orWhere('target_account', $request->account_number)->get();
        
        return response()->json(["transaction"=>$transactions], 200);
    }

    // cancele trasaction
    public function cancelTransaction($id)
    {
        try{
            DB::beginTransaction();

            $transaction = transaction::find($id);
            if($transaction->type == "deposit"){
                $account = Account::where('account_number', $transaction->source_account)->first();
                $new_amount = $account->balance - $transaction->amount;
                $account->balance = $new_amount;
                $account->save();  
            }

            if($transaction->type == "withdrawal"){
                $account = Account::where('account_number', $transaction->source_account)->first();
                $new_amount = $account->balance + $transaction->amount;
                $account->balance = $new_amount;
                $account->save();  
            }

            if($transaction->type == "payment"){
                $accountsource = Account::where('account_number', $transaction->source_account)->first();
                $accounttarget = Account::where('account_number', $transaction->target_account)->first();

                $new_amountsource = $accountsource->balance + $transaction->amount;
                $accountsource->balance = $new_amountsource;
                $accountsource->save();

                $new_amounttarget = $accounttarget->balance - $transaction->amount;
                $accounttarget->balance = $new_amounttarget;
                $accounttarget->save();
            }

            $transaction->canceled = true;
            $transaction->save();

            DB::commit();

            return response()->json(['message' => 'transaction canceled successfully completed'], 200);
        }catch(Exception $e){
            DB::rollBack();

            return response()->json([
                'message' => 'a problem occurred the transaction could not be completed',
                'error'=>$e->getMessage()
        ], 400);
        }
        


        
    }
}
