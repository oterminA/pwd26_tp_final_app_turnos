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

class ReglasFinalesTurnosTest extends TestCase
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

    public function test_no_cambia_estado_antes_de_inicio(): void
    {
        $this->franja($this->cardiologia);
        $turno = $this->reservar($this->cardiologia, '09:00', 30);
        $this->actingAs($this->usuarioMedico);
        foreach (['finalizado', 'ausente'] as $estado) {
            $this->put(route('turnos.update', $turno->id), ['estado' => $estado])
                ->assertSessionHasErrors('estado');
            $this->assertDatabaseHas('turnos', ['id' => $turno->id, 'estado' => 'pendiente']);
        }
        $this->get(route('turnos.edit', $turno->id))
            ->assertRedirect(route('usuarios.medico.panel'));
    }

    public function test_puede_finalizar_desde_el_inicio(): void
    {
        $this->franja($this->cardiologia);
        $turno = $this->reservar($this->cardiologia, '09:00', 30);
        Carbon::setTestNow('2026-12-07 09:00:00');
        $this->actingAs($this->usuarioMedico)
            ->put(route('turnos.update', $turno->id), ['estado' => 'finalizado'])
            ->assertRedirect(route('usuarios.medico.panel'));
        $this->assertDatabaseHas('turnos', ['id' => $turno->id, 'estado' => 'finalizado']);
    }

    public function test_ausente_solo_desde_el_fin_del_turno(): void
    {
        $this->franja($this->cardiologia);
        $turno = $this->reservar($this->cardiologia, '09:00', 30);
        $this->actingAs($this->usuarioMedico);
        Carbon::setTestNow('2026-12-07 09:29:59');
        $this->put(route('turnos.update', $turno->id), ['estado' => 'ausente'])
            ->assertSessionHasErrors('estado');
        $this->assertDatabaseHas('turnos', ['id' => $turno->id, 'estado' => 'pendiente']);
        Carbon::setTestNow('2026-12-07 09:30:00');
        $this->put(route('turnos.update', $turno->id), ['estado' => 'ausente'])
            ->assertRedirect(route('usuarios.medico.panel'));
        $this->assertDatabaseHas('turnos', ['id' => $turno->id, 'estado' => 'ausente']);
    }

    public function test_no_se_cambia_el_estado_de_un_turno_cancelado(): void
    {
        $this->franja($this->cardiologia);
        $turno = $this->reservar($this->cardiologia, '09:00', 30);
        $turno->cancelar();
        Carbon::setTestNow('2026-12-07 10:00:00');
        $this->actingAs($this->usuarioMedico)
            ->put(route('turnos.update', $turno->id), ['estado' => 'ausente'])
            ->assertSessionHasErrors('estado');
        $this->assertDatabaseHas('turnos', ['id' => $turno->id, 'estado' => 'cancelado']);
    }

    public function test_reserva_solo_horarios_ofrecidos_por_ajax(): void
    {
        $this->franja($this->cardiologia);
        $this->actingAs($this->usuarioPaciente);
        $ruta = route('turnos.ajax.disponibilidad', [
            'medicoId' => $this->medico->id,
            'especialidad' => $this->cardiologia->id,
        ]);
        $horarios = $this->getJson($ruta)->assertOk()->json();
        $this->assertContains('09:00', $horarios['2026-12-07']);
        $this->assertNotContains('09:07', $horarios['2026-12-07']);
        $datos = ['medico' => $this->medico->id, 'especialidad' => $this->cardiologia->id];
        foreach (['2026-12-07 09:07:00', '2026-12-07 09:00:15', '2026-12-28 09:00:00'] as $fecha) {
            $this->post(route('store.turno'), $datos + ['fecha_hora' => $fecha])
                ->assertSessionHasErrors('fecha_hora');
        }
        $this->assertDatabaseCount('turnos', 0);
        $this->post(route('store.turno'), $datos + ['fecha_hora' => '2026-12-07 09:00:00'])
            ->assertRedirect(route('usuarios.paciente.panel'));
        $this->assertDatabaseCount('turnos', 1);
    }

    public function test_historial_conserva_medico_y_especialidad_dados_de_baja(): void
    {
        $this->franja($this->cardiologia);
        $turno = $this->reservar($this->cardiologia, '09:00', 30);
        $turno->cancelar();
        $this->medico->delete();
        $this->cardiologia->delete();
        $historico = $turno->fresh(['medico.user', 'especialidad']);
        $this->assertTrue($historico->medico->trashed());
        $this->assertTrue($historico->especialidad->trashed());
        $this->assertEquals($this->usuarioMedico->id, $historico->medico->user->id);
        $this->actingAs($this->usuarioPaciente)
            ->get(route('usuarios.paciente.panel'))->assertOk()->assertSee($turno->codigo);
        $this->assertDatabaseHas('turnos', ['id' => $turno->id, 'estado' => 'cancelado']);
    }

    public function test_medico_inactivo_no_aparece_ni_permite_reservar(): void
    {
        $this->franja($this->cardiologia);
        $this->medico->delete();
        $this->actingAs($this->usuarioPaciente)
            ->getJson(route('turnos.ajax.medicos', $this->cardiologia->id))
            ->assertOk()->assertExactJson([]);
        $this->post(route('store.turno'), [
            'medico' => $this->medico->id,
            'especialidad' => $this->cardiologia->id,
            'fecha_hora' => '2026-12-07 09:00:00',
        ])->assertNotFound();
        $this->assertDatabaseCount('turnos', 0);
        $this->actingAs($this->usuarioMedico)
            ->get(route('medico.disponibilidades.index'))->assertForbidden();
    }
}
