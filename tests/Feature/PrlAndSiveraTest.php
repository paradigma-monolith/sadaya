<?php

namespace Tests\Feature;

use App\Models\PrlInspeksiTpp;
use App\Models\PrlSiveraVektor;
use App\Models\PrlTpp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrlAndSiveraTest extends TestCase
{
    use RefreshDatabase;

    public function test_sanitasi_and_sivera_require_authentication(): void
    {
        $this->get('/sanitasi-lingkungan')->assertRedirect(route('login'));
        $this->get('/sivera')->assertRedirect(route('login'));
        $this->get('/prl')->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_render_sanitasi_lingkungan_tabs_and_prl_alias(): void
    {
        $user = User::factory()->create();

        foreach ([
            '/sanitasi-lingkungan',
            '/sanitasi-lingkungan/tpp',
            '/sanitasi-lingkungan/sab',
            '/sanitasi-lingkungan/hsbu',
            '/prl',
        ] as $url) {
            $this->actingAs($user)->get($url)->assertStatus(200);
        }
    }

    public function test_authenticated_user_can_create_tpp_sarana_inspeksi_and_delete(): void
    {
        $user = User::factory()->create();

        $saranaRes = $this->actingAs($user)->postJson('/sanitasi-lingkungan/tpp', [
            'wilker' => 'Induk BKK Bandung',
            'nama_tpp' => 'Kantin Sehat Terminal A',
            'penanggung_jawab' => 'Budi Santoso',
            'jml_karyawan' => '12',
            'kapasitas_produksi' => '150 porsi/hari',
        ]);

        $saranaRes->assertStatus(200)->assertJson([
            'success' => true,
        ]);

        $tpp = PrlTpp::where('nama_tpp', 'Kantin Sehat Terminal A')->first();
        $this->assertNotNull($tpp);

        $inspRes = $this->actingAs($user)->postJson('/sanitasi-lingkungan/inspeksi/tpp', [
            'wilker' => 'Induk BKK Bandung',
            'tanggal_inspeksi' => now()->toDateString(),
            'nama_tpp' => 'Kantin Sehat Terminal A',
            'prl_tpp_id' => $tpp->id,
            'nama_pemeriksa' => $user->name,
            'hasil' => 86,
            'tingkat_risiko' => 'Rendah',
            'kesimpulan' => 'Memenuhi syarat higiene sanitasi',
        ]);

        $inspRes->assertStatus(200)->assertJson([
            'success' => true,
        ]);

        $inspId = $inspRes->json('data.id');
        $this->assertDatabaseHas('prl_inspeksi_tpp', [
            'id' => $inspId,
            'prl_tpp_id' => $tpp->id,
            'tingkat_risiko' => 'Rendah',
        ]);

        $this->actingAs($user)
            ->deleteJson("/sanitasi-lingkungan/inspeksi/tpp/{$inspId}")
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertNull(PrlInspeksiTpp::find($inspId));
    }

    public function test_authenticated_user_can_crud_sivera_surveillance_and_pengendalian(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/sivera')->assertStatus(200);

        $createRes = $this->actingAs($user)->postJson('/sivera', [
            'jenis_vektor' => 'Lalat',
            'wilker' => 'Wilker Cirebon',
            'lokasi' => 'Area TPS Pelabuhan',
            'tanggal' => now()->toDateString(),
            'parameter_utama' => 'Fly Grill Index',
            'nilai_indeks' => 4.2,
            'baku_mutu_status' => 'Melebihi Baku Mutu',
            'tindakan_pengendalian' => 'Larvasidasi awal',
        ]);

        $createRes->assertStatus(200)->assertJson(['success' => true]);
        $id = $createRes->json('data.id');

        $controlRes = $this->actingAs($user)->postJson("/sivera/{$id}/pengendalian", [
            'tindakan_pengendalian' => 'Penyemprotan residual dan sanitasi TPS',
            'pengendalian_detail' => [
                'metode' => 'ULV & kebersihan lingkungan',
                'petugas' => $user->name,
            ],
        ]);

        $controlRes->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('prl_sivera_vektor', [
            'id' => $id,
            'tindakan_pengendalian' => 'Penyemprotan residual dan sanitasi TPS',
        ]);

        $this->actingAs($user)
            ->deleteJson("/sivera/{$id}")
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertNull(PrlSiveraVektor::find($id));
    }
}
