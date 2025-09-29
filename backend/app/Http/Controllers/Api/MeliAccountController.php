<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeliAccountController extends Controller
{
    // Retorna a lista de contas do Mercado Livre associadas ao usuário autenticado
    public function index()
    {
        // Usamos o relacionamento que já definimos no model User
        $accounts = Auth::user()->meliAccounts()->get(['id', 'nickname']);

        return response()->json($accounts);
    }
}
