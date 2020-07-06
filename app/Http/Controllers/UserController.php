<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\User;

class UserController extends Controller
{
    public function register(Request $request){
        // Recibir post
        $json = $request->input('json', null);
        $params = json_decode($json);

        $email = (!is_null($json) && isset($params->email)) ? $params->email : null;
        $name = (!is_null($json) && isset($params->name)) ? $params->name : null;
        $surname = (!is_null($json) && isset($params->surname)) ? $params->surname : null;
        $role = 'ROLE_USER';
        $password = (!is_null($json) && isset($params->password)) ? $params->password : null;

        // Validar y ejecutar
        if(!is_null($email) && !is_null($name) && !is_null($password)){
            // Crear el usuario
            $user = new User();
            $user->email = $email;
            $user->name = $name;
            $user->surname = $surname;
            $user->role = $role;
            $pwd = hash('sha256',$password);
            $user->password = $pwd;

            // Comprobar usuario duplicado
            $isset_user = User::where('email','=',$email)->first();
            $exist = (isset($isset_user) && !empty($isset_user)) ? 'SI':'NO';

            if($exist == 'NO'){
                // Guardar usuario
                $user->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Usuario creado correctamente'
                );
            } else {
                // No guardar porque el usuario existe
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'Usuario duplicado, no puede registrarse'
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Usuario no creado, faltan completar datos'
            );
        }

        return response()->json($data, 200);
    }

    public function login(Request $request){
        $jwtAuth = new JwtAuth();

        // Recibir post
        $json = $request->input('json', null);
        $params = json_decode($json);

        $email = (!is_null($json) && isset($params->email)) ? $params->email : null;
        $password = (!is_null($json) && isset($params->password)) ? $params->password : null;
        $getToken = (!is_null($json) && isset($params->getToken)) ? $params->getToken : null;

        // Cifrar password
        $pwd = hash('sha256',$password);

        if(!is_null($email) && !is_null($password) && ($getToken==null || $getToken=='false')){
            $signup = $jwtAuth->signup($email,$pwd);
        } elseif($getToken != null){
            $signup = $jwtAuth->signup($email,$pwd,$getToken);
        } else{
            $signup = array(
                'status' => 'error',
                'message' => 'Envia tus datos por post'
            );
        }

        return response()->json($signup, 200);
    }
}
