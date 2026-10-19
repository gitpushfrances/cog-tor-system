<?php
namespace Tests\Feature\Release;
use App\Models\{Student, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class SuggestTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp(); $this->withoutVite(); $this->seed();
        $this->actingAs(User::where('email', 'registrar@cogtor.test')->firstOrFail());
    }
    private function q($q) { return collect($this->getJson(route('registrar.students.suggest', ['q' => $q]))->assertOk()->json()); }
    public function test_matching(): void
    {
        $s = Student::firstOrFail(); // 2024-00001 Juan S. Dela Cruz
        $this->assertCount(0, $this->q('j'));
        $this->assertSame($s->student_number, $this->q('juan')->first()['number']);
        $this->assertSame($s->student_number, $this->q('dela juan')->first()['number'], 'words in any order');
        $this->assertSame($s->student_number, $this->q('Dela Cruz, Juan')->first()['number']);
        $this->assertSame('2024-00001', $this->q('2024-0000')->first()['number'], 'number prefix first');
        $this->assertLessThanOrEqual(8, $this->q('2024')->count());
        $this->assertCount(0, $this->q('%%'), 'wildcards are literal');
        $this->assertCount(0, $this->q('zzzz'));
        $row = $this->q('juan')->first();
        $this->assertSame(['id','number','name','course','status','profile_url'], array_keys($row));
        $this->assertSame(route('registrar.students.profile', $s), $row['profile_url']);
        $s->update(['status' => 'graduated']);
        $this->assertSame('graduated', $this->q('juan')->first()['status'], 'graduates are found too');
    }
    public function test_wired_and_protected(): void
    {
        foreach (['registrar.students.index', 'registrar.encode-grades', 'registrar.documents.index'] as $r) {
            $this->get(route($r))->assertOk()->assertSee('data-student-suggest="' . route('registrar.students.suggest') . '"', false)->assertSee('ss-box', false);
        }
        $this->get(route('registrar.dashboard', ['tab' => 'documents']))->assertOk()->assertSee('data-suggest-mode="profile"', false);
        auth()->logout();
        $this->actingAs(User::where('email', 'admin@cogtor.test')->firstOrFail());
        $this->getJson(route('registrar.students.suggest', ['q' => 'juan']))->assertForbidden();
        $this->get(route('admin.dashboard'))->assertDontSee('ss-box', false);
    }
}
