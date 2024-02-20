<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\Producto;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
class ProductoController extends Controller
{
    public function index()
    {
        try
        {
            $productos = Producto::all();
            return ApiResponse::success('Lista de Productos', 200, $productos);
        }catch(Exception $e)
        {
            return ApiResponse::error('Ocurrió un error ' -$e->getMessage(), 500);

        }
    }

    public function store(Request $request)
    {
         try
        {
            $request ->validate([
                'nombre' => 'required|unique:productos',
                'precio' => 'required|numeric|between:0,999999.99',
                'cantidad_disponible' => 'required|integer',
                'categoria_id' => 'required|exists:categorias,id',// el id de la tabla categoria exista en la tabla
                'marca_id' => 'required|exists:marcas,id',
            ]);
            $producto = Producto::create($request -> all());
            return ApiResponse::success('Producto creado correctamente', 201, $producto);

        }catch(ValidationException $e)
        {
            $errors = $e -> validator ->errors()-> toArray();
            if(isset($errors['categoria_id']))
            {
                $errors['categoria'] = $errors['categoria_id'];
                unset($errors['categoria_id']);
            }

            if(isset($errors['marca_id']))
            {
                $errors['marca'] = $errors['marca_id'];
                unset($errors['marca_id']);
            }
            return ApiResponse::error('Errores de validación: ', 422, $errors);
        }
    }

    public function show($id)
    {
        try
        {
            $producto = Producto::with('marca','categoria') -> findOrFail($id);
            return ApiResponse::success('Producto obtenido correctamente', 201, $producto);

        }catch(ModelNotFoundException $e)
        {
            return ApiResponse::error('Producto no encontrado',404);
            
        }
    }

    public function update(Request $request, $id)
    {
        try
        {
            $producto = Producto::findOrFail($id);
            $request ->validate([
                'nombre' => ['required', Rule:: unique('productos') -> ignore($producto)],
                'precio' => 'required|numeric|between:0,999999.99',
                'cantidad_disponible' => 'required|integer',
                'categoria_id' => 'required|exists:categorias,id',// el id de la tabla categoria exista en la tabla
                'marca_id' => 'required|exists:marcas,id',
            ]);
            $producto -> update($request -> all());
            return ApiResponse::success('Producto modificado correctamente', 201, $producto);

        }catch(ValidationException $e)
        {
            $errors = $e -> validator ->errors()-> toArray();
            if(isset($errors['categoria_id']))
            {
                $errors['categoria'] = $errors['categoria_id'];
                unset($errors['categoria_id']);
            }

            if(isset($errors['marca_id']))
            {
                $errors['marca'] = $errors['marca_id'];
                unset($errors['marca_id']);
            }
            return ApiResponse::error('Errores de validación: ', 422, $errors);
            
        }catch(ModelNotFoundException $e)
        {
            return ApiResponse::error('Producto no encontrado: ' ,404);
        }
    }

    public function destroy($id)
    {
        try
        {
            $producto = Producto::findOrFail($id);
            $producto -> delete();
            return ApiResponse::success('Producto eliminado correctamente', 200);
        }catch(ModelNotFoundException $e)
        {
            return ApiResponse::error('Producto no encontrado',404);
            
        }
    }
}
