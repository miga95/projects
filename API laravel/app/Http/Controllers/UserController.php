<?php

namespace App\Http\Controllers;

use Mail;
use App\Models\User;
use App\Models\Token;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\FileController;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use JWTAuth;



class UserController extends Controller
{
    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                "message" => $validator->errors()->first(),
                "status" => "ERROR"
            ], 400);
        }
        
        
        
        if ($user = User::whereEmail(request('email'))
            ->wherePassword(hash('sha256', request('password')))
            ->first()
        ) {
            $token = Str::random(100);
            $user->api_token=$token;
            $user->save();
            
            Auth::login($user);
            return response()->json([
                "data"=> $user,
                "message" => "User authenticated",
                "status" => "SUCCESS"
            ], 201);
        }
        return response()->json([
            "message" => "Aucun utilisateur trouvé pour ces informations",
            "status" => "ERROR"
        ], 401);
    }

    
    
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'pseudo'=>'string|max:255',
            'email' => 'required|string|email|max:255|unique:user',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                    "message" => $validator->errors()->first(),
                    "status" => "ERROR"
                ]
                , 400);
        }

       
        $user = User::create([
            'username' => $request->get('username'),
            'pseudo' => $request->get('pseudo'),
            'email' => $request->get('email'),
            'password' => hash('sha256', $request->get('password')),
            'api_token' => Str::random(100),
        ]);

        
        return response()->json([
                "data" => $user ,
                "message" => "user created",
                "status" => "SUCCESS"
            ]
            , 200);
    }
 

    public function deleteUser($id)
    {
        $files= new FileController;
        $files->getFilesByIdUser($id);
        foreach($files as $file){
            DB::table('files')->where('user_id', $id)->delete();
        }
        if ($user = DB::table('user')->where('id', $id)->delete()) {
            
            return response()->json([
                "message" => 'Utilisateur supprimé avec succès',
                "status" => "SUCCESS"
            ], 200);
        }
        return response()->json([
                "message" => "Erreur lors de la suppression de l'utilisateur",
                "status" => "ERROR"
        ], 500);
    }


    public function updateUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'string|max:40',
            'pseudo' => 'string',
            'email' => 'string|email',
            'password' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => $validator->errors()->first(),
                "status" => "ERROR"
            ], 400);
        }

        if ($request->get('password') !== NULL)
            $request->merge(['password' => hash('sha256', $request->get('password'))]);

        $currentdate = date("Y-m-d H:i:s", time());
        $request->merge(['updated_at' => $currentdate]);

        $user = Auth::user();
    
        $user->update($request->all());
        $token = Str::random(100);
            $user->api_token=$token;
            $user->save();

        return response()->json([
                "data" => $user,
                "message" => "User updated",
                "status" => "SUCCESS"
            ]
        );
    }

    public function getUser()
    {
        if (Auth::check()) {
            $user = Auth::user();
            return response()->json([
                "data" => $user,
                "message" => "Utilisateur récupéré avec succès",
                "status" => "SUCCESS"
            ], 200);
        }
        return response()->json([
            "message" => "Aucun utilisateur trouvé",
            "status" => "ERROR"
        ], 404);
    }

    public function getAllUsers()
    {
        if (count($users = User::all(['id', 'username','pseudo', 'email'])) == 0)
            return response()->json([
                "data" => [],
                "message" => "Aucun utilisateur n'a été trouvé",
                "status" => "SUCCESS"
            ], 200);

        return response()->json([
            "data" => $users,
            "message" => "SUCCESS",
            "status" => "SUCCESS"
        ], 200);
    }

}
