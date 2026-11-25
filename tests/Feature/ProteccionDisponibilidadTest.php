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
use Tests\TestCase;

class ProteccionDisponibilidadTest extends TestCase
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

    private function modificar(Disponibilidad $franja, array $cambios)
    {
        return $this->actingAs($this->usuarioMedico)
            ->from(route('medico.disponibilidades.edit', $franja->id))
            ->put(route('medico.disponibilidades.update', $franja->id), array_merge([
                'especialidad_id' => $this->cardiologia->id,
                'dia_semana' => 1,
                'hora_inicio' => '08:00',
                'hora_fin' => '11:00',
            ], $cambios));
    }

    public function test_no_elimina_disponibilidad_con_turno_pendiente(): void
    {
        $franja = $this->franja($this->cardiologia);
        $turno = $this->reservar($this->cardiologia, '09:00', 30);

        $this->actingAs($this->usuarioMedico)
            ->delete(route('medico.disponibilidades.delete', $franja->id))
            ->assertRedirect(route('medico.disponibilidades.index'))
            ->assertSessionHasErrors('disponibilidad');

        $this->assertDatabaseHas('disponibilidades', ['id' => $franja->id]);
        $this->assertDatabaseHas('turnos', ['id' => $turno->id, 'estado' => 'pendiente']);
    }

    public function test_no_reduce_horario_si_el_turno_queda_fuera(): void
    {
        $franja = $this->franja($this->cardiologia);
        $this->reservar($this->cardiologia, '09:00', 30);

        $this->modificar($franja, ['hora_fin' => '09:15'])->assertSessionHasErrors('hora_inicio');
        $this->assertSame('11:00', $franja->fresh()->hora_fin->format('H:i'));
    }

    public function test_no_cambia_dia_o_especialidad_con_turnos_pendientes(): void
    {
        $franja = $this->franja($this->cardiologia);
        $this->reservar($this->cardiologia, '09:00', 30);

        $this->modificar($franja, ['dia_semana' => 2])->assertSessionHasErrors('hora_inicio');
        $this->modificar($franja, ['especialidad_id' => $this->pediatria->id])->assertSessionHasErrors('hora_inicio');
        $this->assertDatabaseHas('disponibilidades', [
            'id' => $franja->id, 'dia_semana' => 1, 'especialidad_id' => $this->cardiologia->id,
        ]);
    }

    public function test_permite_modificar_si_conserva_el_turno_completo(): void
    {
        $franja = $this->franja($this->cardiologia);
        $turno = $this->reservar($this->cardiologia, '09:00', 30);

        $this->modificar($franja, ['hora_inicio' => '09:00', 'hora_fin' => '12:00'])
            ->assertRedirect(route('medico.disponibilidades.index'))->assertSessionHasNoErrors();
        $this->assertSame('09:00', $franja->fresh()->hora_inicio->format('H:i'));
        $this->assertSame('12:00', $franja->fresh()->hora_fin->format('H:i'));
        $this->assertDatabaseHas('turnos', ['id' => $turno->id, 'estado' => 'pendiente']);
    }

    public function test_permite_eliminar_si_el_turno_esta_cancelado(): void
    {
        $franja = $this->franja($this->cardiologia);
        $turno = $this->reservar($this->cardiologia, '09:00', 30);
        $turno->cancelar();

        $this->actingAs($this->usuarioMedico)
            ->delete(route('medico.disponibilidades.delete', $franja->id))
            ->assertRedirect(route('medico.disponibilidades.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('disponibilidades', ['id' => $franja->id]);
        $this->assertDatabaseHas('turnos', ['id' => $turno->id, 'estado' => 'cancelado']);
    }

    public function test_permite_eliminar_si_el_turno_ya_paso(): void
    {
        $franja = $this->franja($this->cardiologia);
        $turno = $this->reservar($this->cardiologia, '09:00', 30);
        Carbon::setTestNow('2026-12-07 10:00:00');

        $this->actingAs($this->usuarioMedico)
            ->delete(route('medico.disponibilidades.delete', $franja->id))
            ->assertRedirect(route('medico.disponibilidades.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('disponibilidades', ['id' => $franja->id]);
        $this->assertDatabaseHas('turnos', ['id' => $turno->id]);
    }

    public function test_permite_eliminar_franja_sin_reservas(): void
    {
        $franja = $this->franja($this->cardiologia);
        $this->actingAs($this->usuarioMedico)
            ->delete(route('medico.disponibilidades.delete', $franja->id))
            ->assertRedirect(route('medico.disponibilidades.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('disponibilidades', ['id' => $franja->id]);
    }
}
