<?php
namespace Tests\Feature\Release;
use App\Models\{SchoolYear, Semester, Student, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;
class DashboardTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp(); $this->withoutVite(); $this->seed();
        $this->actingAs(User::where('email', 'admin@cogtor.test')->firstOrFail());
    }
    public function test_cards_removed_header_fixed(): void
    {
        $sy = SchoolYear::where('status', 'active')->firstOrFail();
        $sem = Semester::where('school_year_id', $sy->id)->where('status', 'active')->firstOrFail();
        $r = $this->get(route('admin.dashboard'))->assertOk();
        $r->assertSee("Active: SY {$sy->year_code} · {$sem->semester_name}")
          ->assertDontSee('Academic Setup')->assertDontSee('User Management')->assertDontSee('heads of department')
          ->assertDontSee(route('admin.courses.index'), false)->assertSee('Recent Activity')->assertSee('Recent Users')->assertSee('Total Students');
        Semester::query()->update(['status' => 'completed']);
        $this->get(route('admin.dashboard'))->assertSee('no active semester');
        SchoolYear::query()->update(['status' => 'completed']);
        $this->get(route('admin.dashboard'))->assertSee('No active school year set');
    }
    public function test_recent_activity(): void
    {
        Activity::query()->delete();
        $this->get(route('admin.dashboard'))->assertSee('No activity recorded yet.');
        $s = Student::firstOrFail();
        $s->update(['first_name' => 'Renamed']);              // logged by RecordsActivity
        for ($i = 1; $i <= 12; $i++) activity('test')->log("Entry $i");
        $r = $this->get(route('admin.dashboard'))->assertOk();
        $r->assertSee('Entry 12')->assertSee('Entry 3')->assertDontSee('Entry 2<', false)->assertDontSee('Renamed (');
        $this->assertSame(10, substr_count($r->getContent(), 'width:110px;flex-shrink:0'));
        Activity::query()->where('description', 'like', 'Entry%')->delete();
        $this->get(route('admin.dashboard'))->assertSee('Renamed')->assertSee($s->student_number)->assertSee('Updated student');
    }
}
