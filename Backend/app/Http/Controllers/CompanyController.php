<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    // lista todas las empresas del usuario autenticado
    public function index()
    {
        $companies = Auth::user()->companies;

        return response()->json([
            'companies' => $companies
        ], 200);
    }

    // crea una nueva empresa ligada al usuario autenticado
    public function store(StoreCompanyRequest $request)
    {
        // datos ya validados y sanitizados por StoreCompanyRequest
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

    // muestra los detalles de una empresa especifica del usuario
    public function show($id)
    {
        $company = Auth::user()->companies()->find($id);

        if (!$company) {
            return response()->json(['message' => 'No encontramos esa empresa o no tienes permiso'], 404);
        }

        return response()->json(['company' => $company], 200);
    }

    // actualiza los datos de una empresa existente
    public function update(UpdateCompanyRequest $request, $id)
    {
        $company = Auth::user()->companies()->find($id);

        if (!$company) {
            return response()->json(['message' => 'No encontramos la empresa para actualizar'], 404);
        }

        // solo actualizamos los campos que llegaron (validated filtra los extras)
        $company->update($request->validated());

        return response()->json([
            'message' => 'Datos actualizados correctamente',
            'company' => $company
        ], 200);
    }

    // elimina una empresa del usuario
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
