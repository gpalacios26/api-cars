<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Helpers\JwtAuth;
use App\Car;

class CarController extends Controller
{
    private $request;
    private $hash;
    private $checkToken;
    
    public function __construct(Request $request) {
        $this->request = $request;
        $this->hash = $this->request->header('Authorization', null);
        // Validar hash y token
        if(isset($this->hash) && !empty($this->hash)){
            $jwtAuth = new JwtAuth();
            $this->checkToken = $jwtAuth->checkToken($this->hash);
        }
    }

    public function index(){
        // Obtener todos los registros
        $cars = Car::all()->load('user');

        $data = array(
            'status' => 'success',
            'code' => 200,
            'cars' => $cars
        );

        return response()->json($data, 200);
    }

    public function show($id){
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

        return response()->json($data, 200);
    }

    public function store(){
        if($this->checkToken){
            // Recibir post
            $json = $this->request->input('json', null);
            $params = json_decode($json);
            $params_array = json_decode($json, true);

            // Obtener los datos del usuario autenticado
            $jwtAuth = new JwtAuth();
            $user = $jwtAuth->checkToken($this->hash, true);

            // Validación de datos
            $validated = \Validator::make($params_array, [
                'title' => 'required',
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
                'title' => 'required',
                'description' => 'required',
                'price' => 'required',
                'status' => 'required'
            ]);

            if($validated->fails()){
                return response()->json($validated->errors(), 400);
            }

            //Comprobar que existe el registro
            $carOld = Car::find($id);
            if($carOld){
                // Actualizar el registro
                unset($params_array['id']);
                unset($params_array['user_id']);
                unset($params_array['created_at']);
                unset($params_array['updated_at']);
                unset($params_array['user']);
                $car = Car::where('id',$id)->update($params_array);

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'car' => $carOld
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