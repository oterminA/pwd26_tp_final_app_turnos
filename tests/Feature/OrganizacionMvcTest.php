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

class OrganizacionMvcTest extends TestCase
{
    use RefreshDatabase;

    private Medico $medico;
    private Paciente $paciente;
    private Especialidad $clinica;
    private Especialidad $cardio;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-25 09:00:00');
        $this->admin = User::create(['name' => 'Admin', 'email' => 'admin@test.local', 'password' => 'password', 'rol' => 'admin']);
        $usuario = User::create(['name' => 'Juan Pablo | Perez Gomez', 'email' => 'medico@test.local', 'password' => 'password', 'rol' => 'medico']);
        $this->medico = Medico::create(['user_id' => $usuario->id, 'tipoMat' => 'MP21', 'numMat' => '12345', 'matricula' => 'MP21-12345', 'obra_social' => 'osde', 'obras_sociales' => ['osde']]);
        $usuario = User::create(['name' => 'Ana | Ruiz', 'email' => 'paciente@test.local', 'password' => 'password', 'rol' => 'paciente']);
        $this->paciente = Paciente::create(['user_id' => $usuario->id, 'dni' => '12345678', 'telefono' => '2994000000', 'fecha_nacimiento' => '2000-01-01', 'obra_social' => 'osde']);
        $this->clinica = Especialidad::create(['nombre' => 'Clinica', 'duracion_turno' => 30]);
        $this->cardio = Especialidad::create(['nombre' => 'Cardio', 'duracion_turno' => 30]);
        $this->medico->especialidades()->attach([$this->clinica->id, $this->cardio->id]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function datosMedico(): array
    {
        return ['nombre' => 'Juan Pablo', 'apellido' => 'Perez Gomez', 'email' => 'medico@test.local', 'obras_sociales' => ['osde', 'issn'], 'especialidades' => [$this->clinica->id]];
    }

    private function turno(string $fecha, string $estado = 'pendiente'): Turno
    {
        return Turno::create(['paciente_id' => $this->paciente->id, 'medico_id' => $this->medico->id, 'especialidad_id' => $this->cardio->id, 'fecha' => $fecha, 'hora_inicio' => '10:00:00', 'hora_fin' => '10:30:00', 'estado' => $estado, 'codigo' => 'TESTMVC1']);
    }

    public function test_medico_no_quita_especialidad_con_disponibilidad(): void
    {
        Disponibilidad::create(['medico_id' => $this->medico->id, 'especialidad_id' => $this->cardio->id, 'dia_semana' => 1, 'hora_inicio' => '08:00', 'hora_fin' => '12:00']);
        $this->actingAs($this->medico->user)->put(route('medico.update'), $this->datosMedico())->assertSessionHasErrors('especialidades');
        $this->assertTrue($this->medico->especialidades()->whereKey($this->cardio->id)->exists());
    }

    public function test_admin_no_quita_especialidad_con_turno_pendiente(): void
    {
        $this->turno('2026-12-01');
        $this->actingAs($this->admin)->put(route('admin.medicos.update', $this->medico->id), $this->datosMedico() + ['tipoMat' => 'MP21', 'numMat' => '12345', 'estado' => 'activo'])->assertSessionHasErrors('especialidades');
        $this->assertTrue($this->medico->especialidades()->whereKey($this->cardio->id)->exists());
    }

    public function test_medico_actualiza_coberturas_y_quita_especialidad_sin_reservas(): void
    {
        $this->actingAs($this->medico->user)->put(route('medico.update'), $this->datosMedico())->assertSessionHasNoErrors();
        $this->assertSame(['osde', 'issn'], $this->medico->fresh()->obras_sociales);
        $this->assertSame([$this->clinica->id], $this->medico->especialidades()->pluck('especialidades.id')->all());
        $this->assertSame('Juan Pablo', $this->medico->fresh()->nombre);
    }

    public function test_medico_no_modifica_matricula(): void
    {
        $this->actingAs($this->medico->user)->put(route('medico.update'), $this->datosMedico() + ['numMat' => '99999'])->assertSessionHasErrors('numMat');
        $this->assertSame('MP21-12345', $this->medico->fresh()->matricula);
    }

    public function test_especialidad_con_turno_futuro_no_se_desactiva_ni_cambia_duracion(): void
    {
        $this->turno('2026-12-01');
        $this->actingAs($this->admin)->delete(route('admin.especialidades.delete', $this->cardio->id))->assertSessionHas('error');
        $this->put(route('admin.especialidades.update', $this->cardio->id), ['nombre' => 'Cardio', 'duracion_turno' => 20, 'estado' => 'activa'])->assertSessionHasErrors('duracion_turno');
        $this->assertFalse($this->cardio->fresh()->trashed());
        $this->assertEquals(30, $this->cardio->fresh()->duracion_turno);
    }

    public function test_baja_con_historial_no_permite_nuevas_reservas(): void
    {
        $turno = $this->turno('2026-11-01', 'finalizado');
        $this->actingAs($this->admin)->delete(route('admin.especialidades.delete', $this->cardio->id))->assertRedirect(route('admin.especialidades.index'));
        $this->assertSoftDeleted('especialidades', ['id' => $this->cardio->id]);
        $this->assertSame('Cardio', $turno->fresh()->especialidad->nombre);
        $this->actingAs($this->paciente->user)->getJson(route('turnos.ajax.medicos', $this->cardio->id))->assertOk()->assertJsonCount(0);
        $this->post(route('store.turno'), ['medico' => $this->medico->id, 'especialidad' => $this->cardio->id, 'fecha_hora' => '2026-12-01 10:00:00'])->assertNotFound();
    }

    public function test_paciente_valida_dni_fecha_y_cobertura(): void
    {
        $this->post(route('store.paciente'), ['nombre' => 'Ana', 'apellido' => 'Ruiz', 'email' => 'nuevo@test.local', 'password' => 'password', 'password_confirmation' => 'password', 'dni' => 'abc', 'fecha_nacimiento' => '2027-01-01', 'telefono' => '2994000000', 'obra_social' => 'inventada'])->assertSessionHasErrors(['dni', 'fecha_nacimiento', 'obra_social']);
        $this->assertDatabaseMissing('users', ['email' => 'nuevo@test.local']);
    }

    public function test_dashboard_incluye_ultimo_dia_del_mes(): void
    {
        $this->turno('2026-11-30');
        $resumen = Turno::resumenMensual('2026-11', Carbon::parse('2026-11-01'), Carbon::parse('2026-11-30')->endOfDay(), $this->medico->id);
        $this->assertSame(1, $resumen['total']);
        $this->assertSame(1, $resumen['cantidadDia'][29]);
    }
}
