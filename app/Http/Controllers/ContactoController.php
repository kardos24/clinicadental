<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactoRequest;
use Illuminate\Support\Facades\Mail;

class ContactoController extends Controller
{
    public function show()
    {
        return view('public.contacto');
    }

    public function send(ContactoRequest $request)
    {
        $data = $request->validated();

        try {
            Mail::raw(
                "Mensaje de contacto web\n\nNombre: {$data['nombre']}\nContacto: {$data['contacto']}\nAsunto: {$data['asunto']}\n\nMensaje:\n{$data['mensaje']}",
                function ($m) use ($data) {
                    $m->to(config('mail.from.address'))
                      ->subject("Web: {$data['asunto']} — {$data['nombre']}");
                }
            );
        } catch (\Throwable $e) {
            logger()->error('Error enviando email de contacto: ' . $e->getMessage());
        }

        return back()->with('contacto_ok', true);
    }
}
