<?php

namespace Database\Seeders;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Dentadura;
use App\Models\HistorialClinico;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoClientesSeeder extends Seeder
{
    public function run(): void
    {
        $gestor = User::where('role', 'gestor')->first();

        if (! $gestor) {
            $this->command->error('No hay gestor. Ejecuta DatabaseSeeder primero.');
            return;
        }

        $pacientes = [
            [
                'user' => [
                    'name'  => 'María Fernández Ruiz',
                    'email' => 'maria.fernandez@example.com',
                ],
                'cliente' => [
                    'apellidos'  => 'Fernández Ruiz',
                    'nombre'     => 'María',
                    'edad'       => 35,
                    'profesion'  => 'Enfermera',
                    'direccion'  => 'Avda. Constitución, 8',
                    'cp'         => '30170',
                    'telefono'   => '666 222 333',
                    'observaciones' => 'Alergia a la penicilina. Preferiblemente citas por la mañana.',
                ],
                'problemas' => [
                    '14' => ['cara_oclusal' => 'sellante'],
                    '24' => ['cara_oclusal' => 'sellante'],
                    '36' => ['cara_mesial' => 'caries'],
                ],
                'historial' => [
                    ['dia' => 10, 'mes' => 1, 'anio' => 2025, 'signo' => 'PRV', 'diagnostico' => 'Revisión anual. Sin incidencias destacables.', 'num_sesiones' => 1, 'importe' => 40.00, 'debe' => 40.00, 'haber' => 40.00, 'tratamiento_realizado' => 'Revisión y limpieza'],
                ],
                'citas' => [
                    ['fecha' => '2026-05-07 09:30', 'duracion' => 30, 'motivo' => 'Revisión semestral', 'estado' => 'realizada'],
                    ['fecha' => '2026-05-20 10:00', 'duracion' => 60, 'motivo' => 'Empaste diente 36', 'estado' => 'confirmada'],
                    ['fecha' => '2026-06-17 09:00', 'duracion' => 45, 'motivo' => 'Control post-empaste', 'estado' => 'pendiente'],
                ],
            ],
            [
                'user' => [
                    'name'  => 'Carlos Martínez Soler',
                    'email' => 'carlos.martinez@example.com',
                ],
                'cliente' => [
                    'apellidos'  => 'Martínez Soler',
                    'nombre'     => 'Carlos',
                    'edad'       => 52,
                    'profesion'  => 'Fontanero',
                    'direccion'  => 'C/ San Roque, 22',
                    'cp'         => '30170',
                    'telefono'   => '666 333 444',
                    'observaciones' => 'Hipertenso. Control previo antes de anestesia.',
                ],
                'problemas' => [
                    '16' => ['estado_pieza' => 'corona'],
                    '26' => ['estado_pieza' => 'corona'],
                    '46' => ['estado_pieza' => 'ausente'],
                    '36' => ['cara_oclusal' => 'obturacion', 'cara_mesial' => 'obturacion'],
                    '11' => ['cara_vestibular' => 'desgaste'],
                ],
                'historial' => [
                    ['dia' => 5, 'mes' => 11, 'anio' => 2024, 'signo' => 'PER', 'diagnostico' => 'Periodontitis leve. Necesita tartrectomía.', 'num_sesiones' => 2, 'importe' => 180.00, 'debe' => 180.00, 'haber' => 90.00, 'tratamiento_realizado' => 'Tartrectomía supragingival'],
                    ['dia' => 12, 'mes' => 2, 'anio' => 2025, 'signo' => 'PRV', 'diagnostico' => 'Control de periodontitis. Mejoría.', 'num_sesiones' => 1, 'importe' => 40.00, 'debe' => 40.00, 'haber' => 130.00, 'tratamiento_realizado' => 'Revisión y pulido'],
                ],
                'citas' => [
                    ['fecha' => '2026-05-06 11:00', 'duracion' => 90, 'motivo' => 'Limpieza profunda e implante 46', 'estado' => 'realizada'],
                    ['fecha' => '2026-05-27 10:30', 'duracion' => 60, 'motivo' => 'Revisión implante', 'estado' => 'confirmada'],
                    ['fecha' => '2026-06-10 11:00', 'duracion' => 45, 'motivo' => 'Control y mantenimiento periodontal', 'estado' => 'pendiente'],
                    ['fecha' => '2026-06-24 10:00', 'duracion' => 30, 'motivo' => 'Revisión rutinaria', 'estado' => 'pendiente'],
                ],
            ],
            [
                'user' => [
                    'name'  => 'Ana Belén López Navarro',
                    'email' => 'ana.lopez@example.com',
                ],
                'cliente' => [
                    'apellidos'  => 'López Navarro',
                    'nombre'     => 'Ana Belén',
                    'edad'       => 28,
                    'profesion'  => 'Maestra',
                    'direccion'  => 'C/ Real, 45, 2ºB',
                    'cp'         => '30170',
                    'telefono'   => '666 444 555',
                    'observaciones' => 'Bruxismo. Lleva férula nocturna.',
                ],
                'problemas' => [
                    '11' => ['cara_vestibular' => 'desgaste'],
                    '12' => ['cara_vestibular' => 'desgaste'],
                    '21' => ['cara_vestibular' => 'desgaste'],
                    '22' => ['cara_vestibular' => 'desgaste'],
                    '17' => ['cara_oclusal' => 'sellante'],
                    '27' => ['cara_oclusal' => 'sellante'],
                ],
                'historial' => [
                    ['dia' => 20, 'mes' => 9, 'anio' => 2024, 'signo' => 'FER', 'diagnostico' => 'Bruxismo severo. Se confecciona férula de descarga.', 'num_sesiones' => 2, 'importe' => 350.00, 'debe' => 350.00, 'haber' => 175.00, 'tratamiento_realizado' => 'Férula de descarga Michigan'],
                ],
                'citas' => [
                    ['fecha' => '2026-05-14 12:00', 'duracion' => 30, 'motivo' => 'Control férula de descarga', 'estado' => 'confirmada'],
                    ['fecha' => '2026-05-28 12:30', 'duracion' => 45, 'motivo' => 'Ajuste oclusión y revisión', 'estado' => 'pendiente'],
                    ['fecha' => '2026-06-11 12:00', 'duracion' => 30, 'motivo' => 'Revisión semestral', 'estado' => 'pendiente'],
                ],
            ],
            [
                'user' => [
                    'name'  => 'Pedro Sánchez Alarcón',
                    'email' => 'pedro.sanchez@example.com',
                ],
                'cliente' => [
                    'apellidos'  => 'Sánchez Alarcón',
                    'nombre'     => 'Pedro',
                    'edad'       => 67,
                    'profesion'  => 'Jubilado',
                    'direccion'  => 'Plaza España, 3',
                    'cp'         => '30170',
                    'telefono'   => '666 555 666',
                    'observaciones' => 'Diabético tipo 2. Cicatrización lenta. Toma anticoagulantes.',
                ],
                'problemas' => [
                    '14' => ['estado_pieza' => 'ausente'],
                    '15' => ['estado_pieza' => 'ausente'],
                    '24' => ['estado_pieza' => 'puente'],
                    '25' => ['estado_pieza' => 'puente'],
                    '36' => ['estado_pieza' => 'implante'],
                    '46' => ['estado_pieza' => 'implante'],
                    '18' => ['estado_pieza' => 'ausente'],
                    '28' => ['estado_pieza' => 'ausente'],
                    '38' => ['estado_pieza' => 'ausente'],
                    '48' => ['estado_pieza' => 'ausente'],
                    '16' => ['cara_oclusal' => 'obturacion', 'cara_distal' => 'obturacion'],
                ],
                'historial' => [
                    ['dia' => 8, 'mes' => 4, 'anio' => 2024, 'signo' => 'IMP', 'diagnostico' => 'Implante en diente 36. Paciente con control glucémico adecuado.', 'num_sesiones' => 3, 'importe' => 1200.00, 'debe' => 1200.00, 'haber' => 800.00, 'tratamiento_realizado' => 'Implante dental diente 36'],
                    ['dia' => 15, 'mes' => 10, 'anio' => 2024, 'signo' => 'IMP', 'diagnostico' => 'Implante en diente 46. Excelente osteointegración.', 'num_sesiones' => 3, 'importe' => 1200.00, 'debe' => 1200.00, 'haber' => 800.00, 'tratamiento_realizado' => 'Implante dental diente 46'],
                    ['dia' => 3, 'mes' => 3, 'anio' => 2025, 'signo' => 'PRV', 'diagnostico' => 'Control anual. Implantes en buen estado.', 'num_sesiones' => 1, 'importe' => 50.00, 'debe' => 50.00, 'haber' => 450.00, 'tratamiento_realizado' => 'Revisión implantes y limpieza'],
                ],
                'citas' => [
                    ['fecha' => '2026-05-05 09:00', 'duracion' => 45, 'motivo' => 'Control anual implantes', 'estado' => 'realizada'],
                    ['fecha' => '2026-05-19 09:30', 'duracion' => 60, 'motivo' => 'Tartrectomía y mantenimiento', 'estado' => 'confirmada'],
                    ['fecha' => '2026-06-02 09:00', 'duracion' => 30, 'motivo' => 'Revisión encías', 'estado' => 'pendiente'],
                    ['fecha' => '2026-06-16 09:30', 'duracion' => 45, 'motivo' => 'Control semestral', 'estado' => 'pendiente'],
                ],
            ],
            [
                'user' => [
                    'name'  => 'Laura Giménez Pardo',
                    'email' => 'laura.gimenez@example.com',
                ],
                'cliente' => [
                    'apellidos'  => 'Giménez Pardo',
                    'nombre'     => 'Laura',
                    'edad'       => 19,
                    'profesion'  => 'Estudiante',
                    'direccion'  => 'C/ Nueva, 7, 1ºA',
                    'cp'         => '30170',
                    'telefono'   => '666 666 777',
                    'observaciones' => 'Primera visita. Derivada por ortodoncia inacabada.',
                ],
                'problemas' => [
                    '17' => ['cara_oclusal' => 'caries'],
                    '27' => ['cara_oclusal' => 'caries'],
                    '37' => ['cara_oclusal' => 'caries'],
                    '47' => ['cara_oclusal' => 'caries'],
                    '13' => ['cara_vestibular' => 'fractura'],
                ],
                'historial' => [],
                'citas' => [
                    ['fecha' => '2026-05-08 17:00', 'duracion' => 30, 'motivo' => 'Primera visita y exploración', 'estado' => 'realizada'],
                    ['fecha' => '2026-05-22 17:30', 'duracion' => 60, 'motivo' => 'Empastes molares superiores (17 y 27)', 'estado' => 'confirmada'],
                    ['fecha' => '2026-06-05 17:00', 'duracion' => 60, 'motivo' => 'Empastes molares inferiores (37 y 47)', 'estado' => 'pendiente'],
                    ['fecha' => '2026-06-19 17:00', 'duracion' => 45, 'motivo' => 'Reconstrucción diente 13', 'estado' => 'pendiente'],
                ],
            ],
            [
                'user' => [
                    'name'  => 'Roberto Cánovas Egea',
                    'email' => 'roberto.canovas@example.com',
                ],
                'cliente' => [
                    'apellidos'  => 'Cánovas Egea',
                    'nombre'     => 'Roberto',
                    'edad'       => 44,
                    'profesion'  => 'Transportista',
                    'direccion'  => 'Camino del Molino, 12',
                    'cp'         => '30170',
                    'telefono'   => '666 777 888',
                    'observaciones' => 'Fumador. Alto riesgo periodontal. Revisiones cada 4 meses.',
                ],
                'problemas' => [
                    '11' => ['cara_vestibular' => 'desgaste', 'cara_lingual' => 'desgaste'],
                    '21' => ['cara_vestibular' => 'desgaste', 'cara_lingual' => 'desgaste'],
                    '36' => ['cara_oclusal' => 'obturacion'],
                    '46' => ['cara_oclusal' => 'obturacion'],
                    '18' => ['estado_pieza' => 'ausente'],
                    '28' => ['estado_pieza' => 'ausente'],
                ],
                'historial' => [
                    ['dia' => 22, 'mes' => 1, 'anio' => 2025, 'signo' => 'PER', 'diagnostico' => 'Periodontitis moderada asociada a tabaquismo.', 'num_sesiones' => 3, 'importe' => 220.00, 'debe' => 220.00, 'haber' => 220.00, 'tratamiento_realizado' => 'Raspado y alisado radicular'],
                ],
                'citas' => [
                    ['fecha' => '2026-05-13 16:00', 'duracion' => 45, 'motivo' => 'Revisión periodontal trimestral', 'estado' => 'realizada'],
                    ['fecha' => '2026-05-29 16:30', 'duracion' => 60, 'motivo' => 'Limpieza y tartrectomía', 'estado' => 'confirmada'],
                    ['fecha' => '2026-06-26 16:00', 'duracion' => 45, 'motivo' => 'Control periodontal', 'estado' => 'pendiente'],
                ],
            ],
            [
                'user' => [
                    'name'  => 'Isabel Romero Hernández',
                    'email' => 'isabel.romero@example.com',
                ],
                'cliente' => [
                    'apellidos'  => 'Romero Hernández',
                    'nombre'     => 'Isabel',
                    'edad'       => 39,
                    'profesion'  => 'Comerciante',
                    'direccion'  => 'C/ Corredera, 18',
                    'cp'         => '30170',
                    'telefono'   => '666 888 999',
                    'observaciones' => 'Interesada en blanqueamiento dental.',
                ],
                'problemas' => [
                    '15' => ['cara_oclusal' => 'obturacion'],
                    '25' => ['cara_oclusal' => 'obturacion'],
                    '45' => ['cara_oclusal' => 'obturacion'],
                ],
                'historial' => [
                    ['dia' => 14, 'mes' => 6, 'anio' => 2024, 'signo' => 'BLA', 'diagnostico' => 'Blanqueamiento domiciliario con férulas. Resultado satisfactorio.', 'num_sesiones' => 1, 'importe' => 250.00, 'debe' => 250.00, 'haber' => 250.00, 'tratamiento_realizado' => 'Kit blanqueamiento domiciliario'],
                ],
                'citas' => [
                    ['fecha' => '2026-05-15 18:00', 'duracion' => 30, 'motivo' => 'Revisión y consulta blanqueamiento', 'estado' => 'confirmada'],
                    ['fecha' => '2026-06-03 18:00', 'duracion' => 60, 'motivo' => 'Blanqueamiento en clínica', 'estado' => 'pendiente'],
                    ['fecha' => '2026-06-30 18:00', 'duracion' => 30, 'motivo' => 'Control resultado blanqueamiento', 'estado' => 'pendiente'],
                ],
            ],
        ];

        $dientes = array_merge(Dentadura::DIENTES_SUPERIORES, Dentadura::DIENTES_INFERIORES);

        // Start num_filiacion counter from the highest existing one
        $maxExistente = \App\Models\Cliente::withTrashed()
            ->whereRaw("num_filiacion REGEXP '^MUL-[0-9]+$'")
            ->selectRaw("MAX(CAST(SUBSTRING(num_filiacion, 5) AS UNSIGNED)) as max_num")
            ->value('max_num') ?? 0;

        $contador = (int) $maxExistente;

        foreach ($pacientes as $index => $datos) {
            $email = $datos['user']['email'];

            if (User::where('email', $email)->exists()) {
                $this->command->warn("  Saltando {$email} (ya existe)");
                continue;
            }

            $contador++;
            $numFiliacion = 'MUL-' . str_pad($contador, 5, '0', STR_PAD_LEFT);

            // Usuario
            $user = User::create([
                'name'     => $datos['user']['name'],
                'email'    => $email,
                'password' => Hash::make('Cliente1234!'),
                'role'     => 'cliente',
                'activo'   => true,
            ]);

            // Cliente
            $cliente = Cliente::create(array_merge($datos['cliente'], [
                'user_id'       => $user->id,
                'num_filiacion' => $numFiliacion,
            ]));

            // Dentadura
            foreach ($dientes as $num) {
                Dentadura::create([
                    'cliente_id'          => $cliente->id,
                    'num_diente'          => (string) $num,
                    'estado_pieza'        => 'presente',
                    'fecha_actualizacion' => now(),
                ]);
            }

            // Problemas dentales
            foreach ($datos['problemas'] as $diente => $campos) {
                $campos['fecha_actualizacion'] = now();
                Dentadura::where('cliente_id', $cliente->id)
                         ->where('num_diente', $diente)
                         ->update($campos);
            }

            // Historial
            foreach ($datos['historial'] as $entrada) {
                HistorialClinico::create(array_merge($entrada, [
                    'cliente_id' => $cliente->id,
                    'recibo'     => 'REC-' . $entrada['anio'] . '-' . str_pad($cliente->id * 10 + rand(1, 9), 3, '0', STR_PAD_LEFT),
                    'gestor_id'  => $gestor->id,
                ]));
            }

            // Citas
            foreach ($datos['citas'] as $cita) {
                Cita::create([
                    'cliente_id'       => $cliente->id,
                    'fecha_hora'       => $cita['fecha'],
                    'duracion_minutos' => $cita['duracion'],
                    'motivo'           => $cita['motivo'],
                    'estado'           => $cita['estado'],
                    'gestor_id'        => $gestor->id,
                ]);
            }

            $this->command->info("  ✅ {$datos['cliente']['nombre']} {$datos['cliente']['apellidos']} — {$cliente->num_filiacion}");
        }

        $this->command->info('');
        $this->command->info('Todos los pacientes usan contraseña: Cliente1234!');
    }
}
