<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PixConfigController extends Controller
{
    public function show()
    {
        $path = 'pix_config.json';
        $data = [];
        if (Storage::disk('local')->exists($path)) {
            try {
                $json = Storage::disk('local')->get($path);
                $data = json_decode($json, true) ?: [];
            } catch (\Throwable $e) {
                $data = [];
            }
        }
        return response()->json($data);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'chave' => ['required','string','max:200'],
            'recebedor_nome' => ['nullable','string','max:60'],
            'recebedor_cidade' => ['nullable','string','max:60'],
            'descricao' => ['nullable','string','max:120'],
        ]);
        $path = 'pix_config.json';
        Storage::disk('local')->put($path, json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
        return response()->json(['success' => true]);
    }
}
