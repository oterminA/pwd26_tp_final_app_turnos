<?php

namespace Database\Seeders;

use App\Models\Disponibilidad;
use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Turno;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ClinicaSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->usuario('Admin | Demo', 'admin', 'admin');

            $especialidades = [];
            foreach ([
                ['Clinica medica', 'Atencion general de adultos', 30],
                ['Cardiologia', 'Atencion cardiovascular', 30],
                ['Pediatria', 'Atencion de ninos y adolescentes', 20],
                ['Dermatologia', 'Atencion de la piel', 20],
            ] as $datos) {
                $especialidad = Especialidad::withTrashed()->firstOrCreate(
                    ['nombre' => $datos[0] . ' (demo)'],
                    ['descripcion' => $datos[1], 'duracion_turno' => $datos[2]]
                );
                if ($especialidad->trashed()) {
                    throw new RuntimeException('Una especialidad demo esta inactiva. No se reactivara automaticamente.');
                }
                $especialidades[] = $especialidad;
            }

            $pacientes = [];
            $nombres = ['Ana Maria | Torres', 'Juan Pablo | Ruiz', 'Sofia | Luna', 'Mateo | Vega',
                'Valentina | Rios', 'Lucas | Molina', 'Camila | Castro', 'Martin | Silva'];
            $coberturas = ['osde', 'issn', 'ipross', 'particular', 'swiss', 'osecac', 'galeno', 'sancor'];
            foreach ($nombres as $i => $nombre) {
                $usuario = $this->usuario($nombre, 'paciente' . ($i + 1), 'paciente');
                $pacientes[] = Paciente::firstOrCreate(['user_id' => $usuario->id], [
                    'dni' => (string) (99000001 + $i),
                    'telefono' => '299000000' . ($i + 1),
                    'fecha_nacimiento' => Carbon::today()->subYears(22 + $i)->toDateString(),
                    'obra_social' => $coberturas[$i],
                ]);
            }

            $datosMedicos = [
                ['Laura | Perez', [0, 1], ['osde', 'issn']],
                ['Diego | Romero', [1], ['ipross', 'swiss']],
                ['Maria Elena | Garcia', [2], ['osde', 'osecac']],
                ['Pablo | Fernandez', [3], ['galeno', 'sancor']],
                ['Luciana | Diaz', [0, 3], ['issn', 'ipross']],
            ];

            foreach ($datosMedicos as $i => $datos) {
                $usuario = $this->usuario($datos[0], 'medico' . ($i + 1), 'medico');
                $numero = (string) (990001 + $i);
                $medico = Medico::withTrashed()->firstOrCreate(['user_id' => $usuario->id], [
                    'tipoMat' => 'MP21',
                    'numMat' => $numero,
                    'matricula' => 'MP21-' . $numero,
                    'obra_social' => $datos[2][0],
                    'obras_sociales' => $datos[2],
                ]);
                if ($medico->trashed()) {
                    throw new RuntimeException('Un medico demo esta inactivo. No se reactivara automaticamente.');
                }

                $principal = $especialidades[$datos[1][0]];
                // Solo configuramos las franjas al crear el medico.
                if ($medico->wasRecentlyCreated) {
                    foreach ($datos[1] as $indice) {
                        $medico->especialidades()->attach($especialidades[$indice]->id);
                    }
                    foreach ([1, 3] as $dia) {
                        $this->franja($medico, $principal, $dia, '08:00:00', '12:00:00');
                    }
                    if (isset($datos[1][1])) {
                        $this->franja($medico, $especialidades[$datos[1][1]], 2, '14:00:00', '18:00:00');
                    }
                }

                // Los turnos historicos se cargan directamente porque no son nuevas reservas.
                $lunes = Carbon::today()->startOfWeek(Carbon::MONDAY)->subWeek();
                foreach (['finalizado', 'ausente', 'cancelado'] as $j => $estado) {
                    $codigo = 'DEMO' . ($i + 1) . 'H' . ($j + 1);
                    if (Turno::where('codigo', $codigo)->exists()) {
                        continue;
                    }
                    $inicio = $lunes->copy()->setTime(8, 0)->addMinutes($j * $principal->duracion_turno);
                    $fin = $inicio->copy()->addMinutes($principal->duracion_turno);
                    if (!Disponibilidad::cubreTurno($medico->id, $principal->id, $inicio, $fin)
                        || Turno::hayTurnoSuperpuesto($medico->id, $inicio, $fin)) {
                        throw new RuntimeException('No hay una franja libre para el turno historico demo.');
                    }
                    Turno::create([
                        'paciente_id' => $pacientes[($i + $j) % count($pacientes)]->id,
                        'medico_id' => $medico->id,
                        'especialidad_id' => $principal->id,
                        'fecha' => $inicio->toDateString(),
                        'hora_inicio' => $inicio->format('H:i:s'),
                        'hora_fin' => $fin->format('H:i:s'),
                        'estado' => $estado,
                        'codigo' => $codigo,
                    ]);
                }

                // Las reservas futuras usan la misma validacion que la aplicacion.
                for ($j = 1; $j <= 2; $j++) {
                    $codigo = 'DEMO' . ($i + 1) . 'P' . $j;
                    if (Turno::where('codigo', $codigo)->exists()) {
                        continue;
                    }
                    $horarios = Turno::horariosDisponibles($medico, $principal);
                    $inicio = null;
                    foreach ($horarios as $fecha => $horas) {
                        if ($fecha >= Carbon::today()->addDays(3)->toDateString()) {
                            $inicio = Carbon::parse($fecha . ' ' . $horas[0]);
                            break;
                        }
                    }
                    if (!$inicio) {
                        throw new RuntimeException('No hay horarios disponibles para los turnos demo.');
                    }
                    $turno = Turno::reservarSiDisponible(
                        $medico->id,
                        $principal,
                        $inicio,
                        $inicio->copy()->addMinutes($principal->duracion_turno),
                        $pacientes[($i + $j + 2) % count($pacientes)]->id
                    );
                    $turno->update(['codigo' => $codigo]);
                }
            }
        });
    }

    private function usuario(string $nombre, string $cuenta, string $rol): User
    {
        $usuario = User::firstOrCreate(['email' => $cuenta . '@clinica-demo.test'], [
            'name' => $nombre,
            'password' => 'DemoTurnos2026!',
            'rol' => $rol,
        ]);
        if ($usuario->rol !== $rol) {
            throw new RuntimeException('Ya existe una cuenta demo con otro rol.');
        }
        return $usuario;
    }

    private function franja(Medico $medico, Especialidad $especialidad, int $dia, string $inicio, string $fin): void
    {
        Disponibilidad::create([
            'medico_id' => $medico->id,
            'especialidad_id' => $especialidad->id,
            'dia_semana' => $dia,
            'hora_inicio' => $inicio,
            'hora_fin' => $fin,
        ]);
    }
}
