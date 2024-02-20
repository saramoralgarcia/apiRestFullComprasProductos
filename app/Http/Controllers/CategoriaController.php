<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\Categoria;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use PhpParser\Node\Stmt\TryCatch;

class CategoriaController extends Controller
{
    public function index()
    {
        try
        {
            $categoria = Categoria::all();
            return ApiResponse::success('Lista de Categorias', 200, $categoria);
            // throw new Exception("Error al obtener categorias");
        }catch(Exception $e)
        {
            return ApiResponse::error('Ocurrió un error '.$e->getMessage(),500);
        }
        
    }

    public function store(Request $request)
    {
        try
        {
            $request -> validate([
                'nombre' => 'required|unique:categorias'
            ]);
            $categoria = Categoria::create($request ->all());
            return ApiResponse::success('Categoria creada correctamente', 201, $categoria);
        }catch(ValidationException $e)
        {
            return ApiResponse::error('Error de validación: '.$e->getMessage(),422);
        }
    }

    public function show($id)
    {
        try
        {
            $categoria = Categoria::findOrFail($id);
            return ApiResponse::success('Categoria obtenida correctamente',200, $categoria);
        }catch(ModelNotFoundException $e)
        {
            return ApiResponse::error('Categoria no encontrada',404);    
        }
    }

    public function update(Request $request, $id)
    {
        try
        {
            $categoria = Categoria::findOrFail($id);
            $request -> validate([
                'nombre' => ['required', Rule::unique('categorias')-> ignore($categoria)]
                //si a la hora de actualizar 'rule' permite que si el nombre es el mismo deje introducirlo en la base de datos
            ]);
            $categoria ->update($request ->all());
            return ApiResponse::success('Categoria Actualizada correctamente',200, $categoria);

        }catch(ModelNotFoundException $e)
        {
            return ApiResponse::error('Categoria no encontrada',404);
        }catch(Exception $e)
        {
            return ApiResponse::error('Error: '.$e -> getMessage(),422);
        }
    }
    public function destroy($id)
    {
        try{
        $categoria = Categoria::findOrFail($id);
        $categoria ->delete();
        return ApiResponse::success('Categoria Eliminada correctamente',200);
        }catch(ModelNotFoundException $e)
        {
            return ApiResponse::error('Categoria no encontrada',404);
        }
    }

    public function productosPorCategoria($id)
    {
        try{
            $categoria = Categoria::with('productos') ->findOrFail($id);
            return ApiResponse::success('Categoria y lista de productos',200, $categoria);

            }catch(ModelNotFoundException $e)
            {
                return ApiResponse::error('Categoria no encontrada',404);
            }
    }
}
