<?php
namespace Tests\Feature\Release;
use App\Models\{Enrollment, Grade, Semester, Student, Subject, TorRecord, User};
use App\Support\DocumentSeal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;
class SealTest extends TestCase
{
    use RefreshDatabase;
    private User $reg; private Student $st;
    private array $f = ['prepared_by_name' => 'A', 'prepared_by_title' => 'Clerk', 'checked_by_name' => 'B', 'checked_by_title' => 'Registrar',
                        'campus_admin_name' => 'C', 'campus_admin_title' => 'Admin'];
    protected function setUp(): void
    {
        parent::setUp(); $this->withoutVite(); $this->seed();
        $this->reg = User::where('email', 'registrar@cogtor.test')->firstOrFail();
        $this->st = Student::firstOrFail();
        $sem = Semester::orderBy('id')->firstOrFail();
        $sub = Subject::where('course_id', $this->st->course_id)->firstOrFail();
        $e = Enrollment::create(['student_id' => $this->st->id, 'subject_id' => $sub->id, 'semester_id' => $sem->id, 'enrolled_by' => $this->reg->id, 'enrollment_date' => now(), 'status' => 'enrolled']);
        Grade::create(['enrollment_id' => $e->id, 'grade' => 1.5, 'status' => 'finalized']);
        $this->actingAs($this->reg);
    }
    private function generate(): TorRecord
    {
        $this->post(route('registrar.students.tor.generate', $this->st), $this->f)->assertOk();
        return TorRecord::latest('id')->firstOrFail();
    }
    private function verify(array $q) { return $this->get(route('registrar.verify-document', $q))->assertOk(); }

    public function test_code_is_stable_and_key_bound(): void
    {
        $tor = $this->generate();
        $code = DocumentSeal::code($tor);
        $this->assertMatchesRegularExpression('/^[0-9A-F]{5}-[0-9A-F]{5}$/', $code);
        $this->assertSame($code, DocumentSeal::code($tor->fresh()), 'same after reload from DB');
        config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]);
        $this->assertNotSame($code, DocumentSeal::code($tor->fresh()), 'needs the server key');
    }

    public function test_pdf_contains_code(): void
    {
        $r = $this->post(route('registrar.students.tor.generate', $this->st), $this->f)->assertOk();
        $tor = TorRecord::latest('id')->first();
        $this->assertStringStartsWith('%PDF', $r->getContent());
        // preview (not a saved record) has no seal and does not crash
        $this->postJson(route('registrar.students.tor.preview', $this->st), $this->f)->assertOk()->assertJsonMissing(['Verify code']);
        $this->assertStringNotContainsString('Verify code', $this->postJson(route('registrar.students.tor.preview', $this->st), $this->f)->json('html'));
    }

    public function test_verify_states(): void
    {
        $old = $this->generate();
        // Issued yesterday, so a database clock rewrite of generated_at on supersede would show.
        TorRecord::whereKey($old->id)->update(['generated_at' => now()->subDay()->startOfSecond()]);
        $old = $old->fresh();
        $code = DocumentSeal::code($old);
        $scan = DocumentSeal::payload($old);

        $this->verify(['q' => $scan])->assertSee('VALID — CURRENT')->assertSee($this->st->student_number);
        $this->verify(['q' => $old->document_number, 'code' => strtolower(str_replace('-', '', $code))])->assertSee('VALID — CURRENT');
        $this->verify(['q' => $old->document_number, 'code' => 'AAAAA-BBBBB'])->assertSee('DOES NOT MATCH')->assertDontSee($this->st->student_number);
        $this->verify(['q' => $old->document_number])->assertSee('DOES NOT MATCH');
        $this->verify(['q' => str_replace($code, 'AAAAA-BBBBB', $scan)])->assertSee('DOES NOT MATCH');
        $this->verify(['q' => 'TOR-NOPE', 'code' => $code])->assertSee('NOT FOUND');

        // Later generation: the old record is superseded and must keep its generation time (seal facts).
        $new = $this->generate();
        $this->assertEquals($old->generated_at, $old->fresh()->generated_at, 'superseding must not change generated_at');
        $this->verify(['q' => $scan])->assertSee('VALID — REPLACED')->assertSee($new->document_number);
        $this->verify(['q' => DocumentSeal::payload($new)])->assertSee('VALID — CURRENT');
        $this->assertSame(8, Activity::where('event', 'verified')->count());
    }

    public function test_page_and_access(): void
    {
        $this->get(route('registrar.verify-document'))->assertOk()->assertSee('QR text or document number')->assertSee(route('registrar.verify-document'), false);
        auth()->logout();
        $this->actingAs(User::where('email', 'admin@cogtor.test')->firstOrFail());
        $this->get(route('registrar.verify-document'))->assertForbidden();
    }
}
