<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    // este metodo sirve para listar todas las empresas que tiene el usuario logueado
    public function index()
    {
        // me traigo solo las empresas que le pertenecen al user que hizo la peticion
        $companies = Auth::user()->companies;

        return response()->json([
            'companies' => $companies
        ], 200);
    }

    // aqui guardamos una empresa nueva en la base de datos
    public function store(Request $request)
    {
        // primero valido los datos para que no venga nada raro
        $request->validate([
            'name'     => 'required|string|max:255',
            'industry' => 'required|string',
            'country'  => 'required|string',
            'region'   => 'required|string',
            'keywords' => 'nullable|array',
        ]);

        // creo la empresa y de una vez la ligo al usuario que esta autenticado
        $company = Auth::user()->companies()->create([
            'name'     => $request->name,
            'industry' => $request->industry,
            'country'  => $request->country,
            'region'   => $request->region,
            'keywords' => $request->keywords,
        ]);

        return response()->json([
            'message' => 'Empresa registrada con exito!',
            'company' => $company
        ], 201);
    }

    // sirve para ver los detalles de una empresa especifica
    public function show($id)
    {
        // la busco pero solo dentro de las que son del usuario, asi nadie ve lo que no debe
        $company = Auth::user()->companies()->find($id);

        if (!$company) {
            return response()->json(['message' => 'No encontramos esa empresa o no tienes permiso'], 404);
        }

        return response()->json(['company' => $company], 200);
    }

    // para cuando el usuario quiera cambiar algun dato de su empresa
    public function update(Request $request, $id)
    {
        $company = Auth::user()->companies()->find($id);

        if (!$company) {
            return response()->json(['message' => 'No encontramos la empresa para actualizar'], 404);
        }

        // valido lo que llega, pero aqui los campos son opcionales porque puede que solo cambie uno
        $request->validate([
            'name'     => 'string|max:255',
            'industry' => 'string',
            'country'  => 'string',
            'region'   => 'string',
            'keywords' => 'array',
        ]);

        // actualizo los campos que mandaron
        $company->update($request->all());

        return response()->json([
            'message' => 'Datos actualizados correctamente',
            'company' => $company
        ], 200);
    }

    // este sirve para borrar la empresa si el usuario ya no la quiere
    public function destroy($id)
    {
        $company = Auth::user()->companies()->find($id);

        if (!$company) {
            return response()->json(['message' => 'No encontramos la empresa para borrar'], 404);
        }

        $company->delete();

        return response()->json(['message' => 'Empresa eliminada correctamente'], 200);
    }
}
