<?php

namespace Tests\Feature;

use App\Models\Disponibilidad;
use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Turno;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TurnosDisponibilidadTest extends TestCase
{
    use RefreshDatabase;

    private User $usuarioMedico;
    private User $usuarioPaciente;
    private Medico $medico;
    private Paciente $paciente;
    private Especialidad $cardiologia;
    private Especialidad $pediatria;
    private Carbon $fecha;

    protected function setUp(): void
    {
        parent::setUp();

        // Fecha fija para que las pruebas no dependan del dia real.
        Carbon::setTestNow('2026-11-25 09:00:00');
        $this->fecha = Carbon::parse('2026-12-07 00:00:00'); // lunes

        $this->usuarioMedico = User::create([
            'name' => 'Medico Prueba',
            'email' => 'medico.prueba@example.test',
            'password' => 'password',
            'rol' => 'medico',
        ]);
        $this->medico = Medico::create([
            'user_id' => $this->usuarioMedico->id,
            'matricula' => 'TEST-MEDICO-1',
            'obra_social' => 'OSDE',
            'obras_sociales' => ['OSDE'],
        ]);
        $this->usuarioPaciente = User::create([
            'name' => 'Paciente Prueba',
            'email' => 'paciente.prueba@example.test',
            'password' => 'password',
            'rol' => 'paciente',
        ]);
        $this->paciente = Paciente::create([
            'user_id' => $this->usuarioPaciente->id,
            'dni' => '12345678',
            'telefono' => '2994000000',
            'fecha_nacimiento' => '2002-01-01',
            'obra_social' => 'OSDE',
        ]);
        $this->cardiologia = Especialidad::create([
            'nombre' => 'Cardiologia prueba',
            'duracion_turno' => 30,
        ]);
        $this->pediatria = Especialidad::create([
            'nombre' => 'Pediatria prueba',
            'duracion_turno' => 20,
        ]);
        $this->medico->especialidades()->attach([
            $this->cardiologia->id,
            $this->pediatria->id,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function franja(Especialidad $especialidad, string $desde = '08:00', string $hasta = '11:00'): Disponibilidad
    {
        return Disponibilidad::create([
            'medico_id' => $this->medico->id,
            'especialidad_id' => $especialidad->id,
            'dia_semana' => 1,
            'hora_inicio' => $desde,
            'hora_fin' => $hasta,
        ]);
    }

    private function fechaHora(string $hora): Carbon
    {
        return Carbon::parse('2026-12-07 ' . $hora . ':00');
    }

    private function reservar(Especialidad $especialidad, string $hora, int $minutos): Turno
    {
        $inicio = $this->fechaHora($hora);
        return Turno::reservarSiDisponible(
            $this->medico->id,
            $especialidad,
            $inicio,
            $inicio->copy()->addMinutes($minutos),
            $this->paciente->id
        );
    }

    public function test_no_permite_disponibilidades_superpuestas_entre_especialidades(): void
    {
        $this->franja($this->cardiologia, '08:00', '11:00');

        $this->actingAs($this->usuarioMedico)
            ->from(route('medico.disponibilidades.create'))
            ->post(route('medico.disponibilidades.store'), [
                'especialidad_id' => $this->pediatria->id,
                'dia_semana' => 1,
                'hora_inicio' => '09:00',
                'hora_fin' => '10:00',
            ])
            ->assertRedirect(route('medico.disponibilidades.create'))
            ->assertSessionHasErrors('hora_inicio');

        $this->assertDatabaseCount('disponibilidades', 1);
    }

    public function test_rechaza_reservas_que_se_cruzan_en_el_mismo_medico(): void
    {
        $this->franja($this->cardiologia);
        $this->reservar($this->cardiologia, '09:00', 30);

        $this->expectException(ValidationException::class);
        $this->reservar($this->cardiologia, '09:15', 30);
    }

    public function test_rechaza_reserva_que_termina_fuera_de_disponibilidad(): void
    {
        $this->franja($this->cardiologia, '08:00', '10:00');

        $this->expectException(ValidationException::class);
        $this->reservar($this->cardiologia, '09:45', 30);
    }

    public function test_cancelar_libera_el_horario_para_otra_reserva(): void
    {
        $this->franja($this->cardiologia);
        $turno = $this->reservar($this->cardiologia, '09:00', 30);

        $this->actingAs($this->usuarioPaciente)
            ->delete(route('turnos.delete', $turno->id))
            ->assertRedirect(route('usuarios.paciente.panel'));

        $this->assertDatabaseHas('turnos', [
            'id' => $turno->id,
            'estado' => 'cancelado',
        ]);

        $nuevo = $this->reservar($this->cardiologia, '09:00', 30);
        $this->assertNotEquals($turno->id, $nuevo->id);
        $this->assertDatabaseCount('turnos', 2); // conserva el cancelado
    }

    public function test_el_ajax_oculta_horarios_ocupados_y_los_recupera_al_cancelar(): void
    {
        $this->franja($this->cardiologia);
        $ruta = route('turnos.ajax.disponibilidad', [
            'medicoId' => $this->medico->id,
            'especialidad' => $this->cardiologia->id,
        ]);

        $this->actingAs($this->usuarioPaciente)
            ->getJson($ruta)
            ->assertOk()
            ->assertJsonPath('2026-12-07.0', '08:00');

        $turno = $this->reservar($this->cardiologia, '09:00', 30);
        $ocupados = $this->getJson($ruta)->assertOk()->json();
        $this->assertNotContains('09:00', $ocupados['2026-12-07'] ?? []);

        $this->delete(route('turnos.delete', $turno->id))
            ->assertRedirect(route('usuarios.paciente.panel'));

        $libres = $this->getJson($ruta)->assertOk()->json();
        $this->assertContains('09:00', $libres['2026-12-07'] ?? []);
    }
}
