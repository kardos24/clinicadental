<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactoController extends Controller
{
    public function show()
    {
        return view('public.contacto');
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'nombre'   => 'required|string|max:100',
            'contacto' => 'required|string|max:150',
            'asunto'   => 'nullable|string|max:100',
            'mensaje'  => 'required|string|max:2000',
        ]);

        // Enviar email a la clínica (configura MAIL_* en .env)
        try {
            Mail::raw(
                "Mensaje de contacto web\n\n"
                . "Nombre: {$data['nombre']}\n"
                . "Contacto: {$data['contacto']}\n"
                . "Asunto: {$data['asunto']}\n\n"
                . "Mensaje:\n{$data['mensaje']}",
                function ($m) use ($data) {
                    $m->to(config('mail.from.address'))
                      ->subject("Web: {$data['asunto']} — {$data['nombre']}");
                }
            );
        } catch (\Throwable $e) {
            // No interrumpir al usuario si falla el email
            logger()->error('Error enviando email de contacto: ' . $e->getMessage());
        }

        return back()->with('contacto_ok', true);
    }
}
