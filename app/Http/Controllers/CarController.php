<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Helpers\JwtAuth;
use App\Car;

class CarController extends Controller
{
    private $request;
    private $checkToken;
    
    public function __construct(Request $request) {
        $this->request = $request;
        $hash = $this->request->header('Authorization', null);
        // Validar hash y token
        if(isset($hash)){
            $jwtAuth = new JwtAuth();
            $this->checkToken = $jwtAuth->checkToken($hash);
        } else {
            // Devolver error
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Enviar el token de autenticación'
            );

            return response()->json($data, 200);
        }
    }

    public function index(){
        if($this->checkToken){
            // Obtener todos los registros
            $cars = Car::all()->load('user');

            $data = array(
                'status' => 'success',
                'code' => 200,
                'cars' => $cars
            );
        } else {
            // Devolver error
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Login incorrecto'
            );
        }

        return response()->json($data, 200);
    }

    public function show($id){
        if($this->checkToken){
            // Comprobar que existe el registro
            $car = Car::find($id);
            if($car){
                $car->load('user');

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'car' => $car
                );
            } else {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'No existe el id ingresado'
                );
            }
        } else {
            // Devolver error
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Login incorrecto'
            );
        }

        return response()->json($data, 200);
    }

    public function store(){
        if($this->checkToken){
            // Recibir post
            $json = $this->request->input('json', null);
            $params = json_decode($json);
            $params_array = json_decode($json, true);

            // Obtener los datos del usuario autenticado
            $user = $jwtAuth->checkToken($hash, true);

            // Validación de datos
            $validated = \Validator::make($params_array, [
                'title' => 'required|min:5',
                'description' => 'required',
                'price' => 'required',
                'status' => 'required'
            ]);

            if($validated->fails()){
                return response()->json($validated->errors(), 400);
            }

            // Guardar el registro
            $car = new Car();
            $car->user_id = $user->sub;
            $car->title = $params->title;
            $car->description = $params->description;
            $car->price = $params->price;
            $car->status = $params->status;
            $car->save();

            $data = array(
                'status' => 'success',
                'code' => 200,
                'car' => $car
            );
        } else {
            // Devolver error
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Login incorrecto'
            );
        }

        return response()->json($data, 200);
    }

    public function update($id){
        if($this->checkToken){
            // Recibir post
            $json = $this->request->input('json', null);
            $params_array = json_decode($json, true);

            // Validación de datos
            $validated = \Validator::make($params_array, [
                'title' => 'required|min:5',
                'description' => 'required',
                'price' => 'required',
                'status' => 'required'
            ]);

            if($validated->fails()){
                return response()->json($validated->errors(), 400);
            }

            //Comprobar que existe el registro
            $car = Car::find($id);
            if($car){
                // Actualizar el registro
                $car->update($params_array);

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'car' => $car
                );
            } else {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'No existe el id ingresado'
                );
            }
        } else {
            // Devolver error
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Login incorrecto'
            );
        }

        return response()->json($data, 200);
    }

    public function destroy($id){
        if($this->checkToken){
            //Comprobar que existe el registro
            $car = Car::find($id);
            if($car){
                // Eliminar el registro
                $car->delete();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'car' => $car
                );
            } else {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'No existe el id ingresado'
                );
            }
        } else {
            // Devolver error
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Login incorrecto'
            );
        }

        return response()->json($data, 200);
    }
}