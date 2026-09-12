<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PixConfigController extends Controller
{
    public function show()
    {
        $accountId = session('current_account_id');
        $account = $accountId ? \App\Models\Account::find($accountId) : \App\Models\Account::first();
        
        $data = [
            'chave' => '',
            'nome' => '',
            'cidade' => '',
            'descricao' => ''
        ];

        if ($account && $account->pixConfig) {
            $data = [
                'chave' => $account->pixConfig->pix_chave,
                'nome' => substr(preg_replace('/[^A-Za-z0-9\s]/', '', $account->name), 0, 25) ?: 'CLINICA',
                'cidade' => 'SAO PAULO', // Ou buscar do endereco, mas SAO PAULO é comum fallback PIX
                'descricao' => 'Pagamento',
            ];
        }

        return response()->json($data);
    }
}
