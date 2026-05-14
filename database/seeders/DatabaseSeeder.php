<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Cita;
use App\Models\Dentadura;
use App\Models\HistorialClinico;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Usuario gestor principal ────────────────────────────────────────
        $gestor = User::firstOrCreate(
            ['email' => 'admin@clinicamula.es'],
            [
                'name'     => 'Administrador Clínica',
                'password' => Hash::make('Admin1234!'),
                'role'     => 'gestor',
                'activo'   => true,
            ]
        );

        // ── Usuario cliente de prueba ───────────────────────────────────────
        $userCliente = User::firstOrCreate(
            ['email' => 'paciente@example.com'],
            [
                'name'     => 'Juan García López',
                'password' => Hash::make('Cliente1234!'),
                'role'     => 'cliente',
                'activo'   => true,
            ]
        );

        // ── Cliente de prueba ───────────────────────────────────────────────
        $cliente = Cliente::firstOrCreate(
            ['user_id' => $userCliente->id],
            [
                'num_filiacion' => 'MUL-00001',
                'apellidos'     => 'García López',
                'nombre'        => 'Juan',
                'edad'          => 42,
                'profesion'     => 'Agricultor',
                'direccion'     => 'C/ Mayor, 15',
                'cp'            => '30170',
                'telefono'      => '666 111 222',
                'observaciones' => 'Paciente con sensibilidad al frío.',
            ]
        );

        // ── Inicializar dentadura (modelo multi-cara) ─────────────────────
        $dientes = array_merge(
            Dentadura::DIENTES_SUPERIORES,
            Dentadura::DIENTES_INFERIORES
        );

        foreach ($dientes as $num) {
            Dentadura::firstOrCreate(
                ['cliente_id' => $cliente->id, 'num_diente' => (string)$num],
                ['estado_pieza' => 'presente', 'fecha_actualizacion' => now()]
            );
        }

        // Algunos dientes con problemas de demo (estados por cara)
        $problemasDemo = [
            '16' => ['cara_oclusal' => 'caries', 'cara_mesial' => 'obturacion'],           // molar con caries en oclusal y obturación en mesial
            '36' => ['cara_vestibular' => 'obturacion'],                                     // molar con obturación en vestibular
            '46' => ['estado_pieza' => 'implante'],                                          // pieza sustituida por implante
            '11' => ['cara_vestibular' => 'fractura'],                                       // incisivo con fractura en vestibular
            '27' => ['estado_pieza' => 'corona'],                                            // molar con corona
            '34' => ['cara_distal' => 'caries', 'cara_oclusal' => 'sellante'],              // premolar con caries distal y sellante oclusal
        ];
        foreach ($problemasDemo as $diente => $campos) {
            $campos['fecha_actualizacion'] = now();
            Dentadura::where('cliente_id', $cliente->id)
                     ->where('num_diente', $diente)
                     ->update($campos);
        }

        // ── Historial clínico de ejemplo ────────────────────────────────────
        if ($cliente->historialClinico()->count() === 0) {
            HistorialClinico::create([
                'cliente_id'           => $cliente->id,
                'dia'                  => 15,
                'mes'                  => 3,
                'anio'                 => 2024,
                'signo'                => 'CAR',
                'diagnostico'          => 'Caries en diente 16, tratamiento con empaste composite.',
                'num_sesiones'         => 2,
                'importe'              => 120.00,
                'tratamiento_realizado'=> 'Empaste composite clase II',
                'recibo'               => 'REC-2024-001',
                'debe'                 => 120.00,
                'haber'                => 120.00,
                'gestor_id'            => $gestor->id,
            ]);
        }

        // ── Cita futura de ejemplo ──────────────────────────────────────────
        if ($cliente->citas()->count() === 0) {
            Cita::create([
                'cliente_id'       => $cliente->id,
                'fecha_hora'       => now()->addDays(7)->setTime(10, 0),
                'duracion_minutos' => 45,
                'motivo'           => 'Revisión semestral',
                'estado'           => 'confirmada',
                'gestor_id'        => $gestor->id,
            ]);
        }

        $this->command->info('✅ Seeder ejecutado. Gestor: admin@clinicamula.es / Admin1234!');
        $this->command->info('✅ Cliente demo: paciente@example.com / Cliente1234!');
    }
}
