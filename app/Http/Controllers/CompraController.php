<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use App\Http\Responses\ApiResponse;
use App\Models\Compra;
use App\Models\Producto;
use Illuminate\Database\QueryException;
use Illuminate\support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class CompraController extends Controller
{
    public function index(){
        try
        {
            $compra = Compra::all();
            return ApiResponse::success('Lista de Compras', 200, $compra);
            
        }catch(Exception $e)
        {
            return ApiResponse::error('Ocurrió un error ' -$e->getMessage(), 500);

        }
    }

    public function store(Request $request)
    {
        try
        {
            $productos = $request -> input('productos');
            
            //validar los productos
            if(empty($productos))
            {
                return ApiResponse::error('No se proporcionaron productos', 400);
            }
            //validar la lista de productos
            $validator = Validator::make($request->all(),[    //me trae toda la informacion para realizar la compra
                'productos' => 'required|array',//la respuesta sera un array
                'productos.*.producto_id' => 'required|integer|exists:productos,id', //que exista en la tabla producto
                'productos.*.cantidad' => 'required|integer|min:1' // como minimo tiene que haber una cantidad pq una compra no puede tener 0 producto
            ]);
            if($validator->fails()) //falla en alguna condición,
            {
                return ApiResponse::error('Datos invalidos en la lista productos', 400,$validator->errors());

            }
            //validar productos duplicados
            $productoIds = array_column($productos, 'producto_id');
            if(count($productoIds) !== count(array_unique($productoIds)))
            // cuenta cuantos ids tiene el array             //cuenta cuantos id unicos existe en el array
            // entonces si son distintos quiere decir que hay duplicado y saltará el error
            {
                return ApiResponse::error('No se permiten productos duplicados para la compra', 400);
            }
            $totalPagar = 0;
            $compraItems = [];
            $subtotal = 0;

            //iteracion dde los productos para calcular el total a pagar de la compra

            foreach($productos as $producto)
            {
                $productoB = Producto::find($producto['producto_id']); //devuelve el id de cada producto y lo almacena en productoB
                if(!$productoB)
                {
                return ApiResponse::error('Producto no encontrado', 404);
                }
                //validar la cantidad disponible de los productos

                if($productoB ->cantidad_disponible < $producto['cantidad'])
                //primero accede a la cantidad disponible de la tabla                    // y el segundo la cantidad que el cliente quiere realizar
                {
                return ApiResponse::error('No hay cantidad disponible', 404);
                }
                //Actualizacion de la cantidad disponible de cada producto.

                $productoB ->cantidad_disponible -= $producto['cantidad'];
                $productoB ->save();

                //cálculo de la compra

                $subtotal = $productoB ->precio * $producto['cantidad'];
                $totalPagar += $subtotal;

                //items de la compra
                // en este array se guardan los datos de cada producto[id_producto,precio,cantidad,subtotal] asi con todos los productos de la compra
                $compraItems[] = [
                    'producto_id' => $productoB->id,
                    'precio' => $productoB->precio,
                    'cantidad' => $producto['cantidad'],
                    'subtotal' => $subtotal
                ];
            }
            //registro en la tabla compra

            $compra = Compra::create([   //realiza un registro
                'subtotal' => $totalPagar,
                'total' => $totalPagar
            ]);

            //Asociar productos a la compra con sus cantidades y sus subtotales
            
            $compra->productos()->attach($compraItems); //attach maneja tablas intermedias en Laravel

            return ApiResponse::success('Compra realizada Correctamente',201, $compra);

        }catch(QueryException $e)
        {
            //error de consulta en la base de datos
            return ApiResponse::error('Error en la consulta de base de datos'.$e->getMessage(),500);
        }catch(Exception $e)
        {
            return ApiResponse::error('Error inesperado',500);

        }
    }

    public function show($id)
    {
        try
        {
            $compra = Compra::findOrFail($id);
            return ApiResponse::success('Compra obtenida correctamente',200, $compra);
        }catch(ModelNotFoundException $e)
        {
            return ApiResponse::error('Compra no encontrada',404);

        }
    }
}
