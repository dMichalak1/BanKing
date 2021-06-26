<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Validator;
use DateTime;
use App\Models\Account;
use App\Models\Transfer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
class UserController extends Controller
{
    
    function register(Request $req)
    {
      $user = new User;
       $account= new Account;
    
    $data  = Validator::make($req->all(),
    
        [
            'name' => 'required | min:2 | max:40',
            'surname' => 'required | min:2 | max:40',
            'email' => 'required | email | unique:users,email',
            'personalIdNumber' => 'required | digits:11',
            'phoneNumber' => 'required | digits:9',
            'address' => 'required | min:2 | max:60',
            'dateOfBirth' => 'required',
            'login' => 'required | unique:users,login',
            'password' => 'required| min:8 | max:100 | string'
        ],
        [
            'name.required' => 'Pole Imię jest wymagane',
            'name.min' => 'Pole Imię musi mieć minimum 2 znaki',
            'name.max' => 'Pole Imię może mieć maximum 2 znaki',
            'email.required' => 'Pole email jest wymagane',
            'email.email' => 'Pole email musi być adresem poczty elektronicznej'
        ]

            );
        if($data->fails())
        {
            return response()->json($data->errors(),400);
        }
          
              $req = User::create([
                'name' => $req['name'],
                'surname' => $req['surname'],
                'email' => $req['email'],
                'personalIdNumber' => $req['personalIdNumber'],
                'phoneNumber' => $req['phoneNumber'],
                'address' => $req['address'],
                'dateOfBirth' => $req['dateOfBirth'],
                'login' => $req['login'],
                'password' => Hash::make($req['password']),
              ]);
          
              $token = $req->createToken('fundaProjectToken')->plainTextToken;
        
         $account-> accountNumber = $account->generateNumber(5);        
         $account->balance='0';
         $account->accountType="standard";
         $account->userId=$req->id;
         $account->save();

        $response = [
            'user'=>$req,
            'token'=>$token,
        ];

         return response()->json($response,201);

        }


        function login(Request $req)
        {
            $data  = Validator::make($req->all(),    
        [
            'login' => 'required',
            'password' => 'required|string'
        ]);

        if($data->fails())
        {
            return response()->json($data->errors(),400);
        }

            $user = User::where('login', $req->login)->first();

            if(!$user || !Hash::check($req->password,$user->password))
            {
                return response(['wiadomość'=>'Niepoprawny login lub hasło'],404);
            }
            else
            {
                     $token = $user->createToken('fundaProjectTokenLogin')->plainTextToken;

                        $transfers = new Transfer;
                        $id = $user->id;
                        $account = Account::where('userId','like','%'.$id.'%')->first(); 
                        $transfer = Transfer::where('accountId','like','%'.$account->id.'%')
                        ->Where('isComplete','like','0')
                        ->get();
                        $transfers->checkTransfer($transfer);


                     $response = 
                    [
                    'user'=>$user,
                    'token'=>$token,
                    ];
                    return response($response,200);
                }
            

        }
        public function logout()
        {
            auth()->user()->tokens()->delete();
            return response(['wiadomość'=> 'Wylogowanie powiodło się']);
        }
        public function showUsers(){
            
            if(User::get())
            {
                return response()->json(User::get(),200);
            }
            else
            {
                //nie działa
                return response()->json(['wiadomość'=>'Nie znaleziono użytkowników'],404);
            }
        }
  
  
        
       
       
}
