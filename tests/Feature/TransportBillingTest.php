<?php

namespace ME\SchoolManagement\Tests\Feature;

use ME\SchoolManagement\Models\AcademicYear;
use ME\SchoolManagement\Models\FeeCollection;
use ME\SchoolManagement\Models\RouteStop;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Models\StudentDiscount;
use ME\SchoolManagement\Models\TransportRoute;
use ME\SchoolManagement\Services\FeeBilling;
use ME\SchoolManagement\Tests\TestCase;

/** bug.md 4.3 — students can be put on a route and are billed for it. */
class TransportBillingTest extends TestCase
{
    private TransportRoute $route;
    private RouteStop $stop;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->admin());
        AcademicYear::query()->update(['is_current' => false]);
        AcademicYear::query()->orderBy('id')->first()->update(['is_current' => true]);
        $this->route = TransportRoute::create(['route_name' => 'Test Route ' . uniqid(), 'start_point' => 'A', 'end_point' => 'B', 'fee' => 600, 'is_active' => true]);
        $this->stop = RouteStop::create(['route_id' => $this->route->id, 'stop_name' => 'Far Stop', 'arrival_time' => '07:30', 'extra_fee' => 150]);
    }

    private function rider(): Student
    {
        $s = Student::where('is_active', true)->whereNotNull('academic_year_id')->first();
        $s->update(['transport_route_id' => $this->route->id, 'route_stop_id' => $this->stop->id,
            'academic_year_id' => AcademicYear::where('is_current', true)->value('id')]);
        StudentDiscount::where('student_id', $s->id)->delete();
        Student::where('id', '!=', $s->id)->update(['transport_route_id' => null, 'route_stop_id' => null]);
        return $s->fresh();
    }

    public function test_student_profile_saves_route_and_stop(): void
    {
        $s = Student::first();
        $this->put("/students/{$s->id}", [
            'first_name' => $s->first_name, 'last_name' => $s->last_name, 'gender' => strtolower($s->gender) ?: 'male',
            'date_of_birth' => $s->date_of_birth?->toDateString() ?? '2010-01-01', 'class_id' => $s->class_id,
            'transport_route_id' => $this->route->id, 'route_stop_id' => $this->stop->id,
        ])->assertSessionHasNoErrors();
        $this->assertSame([$this->route->id, $this->stop->id], [$s->fresh()->transport_route_id, $s->fresh()->route_stop_id]);
    }

    public function test_stop_must_belong_to_the_route(): void
    {
        $other = TransportRoute::create(['route_name' => 'Other ' . uniqid(), 'start_point' => 'C', 'end_point' => 'D', 'fee' => 100, 'is_active' => true]);
        $s = Student::first();
        $this->put("/students/{$s->id}", ['first_name' => 'X', 'last_name' => 'Y', 'gender' => 'male', 'date_of_birth' => '2010-01-01',
            'class_id' => $s->class_id, 'transport_route_id' => $other->id, 'route_stop_id' => $this->stop->id])
            ->assertSessionHasErrors('route_stop_id');
    }

    public function test_generate_bills_route_fee_plus_stop_extra_once_per_month(): void
    {
        $s = $this->rider();
        $this->post('/transport/fee/generate', ['month' => 4])->assertSessionHas('success');
        $type = FeeBilling::transportFeeType();
        $inv = FeeCollection::where(['student_id' => $s->id, 'fee_type_id' => $type->id, 'month' => '4'])->get();
        $this->assertCount(1, $inv);
        $this->assertEquals(750, (float) $inv->first()->amount); // 600 + 150

        $this->post('/transport/fee/generate', ['month' => 4])->assertSessionHas('error'); // already billed
        $this->assertSame(1, FeeCollection::where(['student_id' => $s->id, 'fee_type_id' => $type->id, 'month' => '4'])->count());
    }

    public function test_monthly_generation_with_a_transport_fee_type_uses_routes(): void
    {
        $s = $this->rider();
        $type = FeeBilling::transportFeeType();
        $this->post('/fees/generate-monthly', ['class_id' => $s->class_id, 'academic_year_id' => $s->academic_year_id,
            'fee_type_id' => $type->id, 'month' => 5])->assertSessionHas('success');
        $this->assertEquals(750, (float) FeeCollection::where(['student_id' => $s->id, 'fee_type_id' => $type->id, 'month' => '5'])->value('amount'));
    }

    public function test_transport_fee_page_shows_riders(): void
    {
        $this->rider();
        $this->get('/transport/fee')->assertOk()->assertSee($this->route->route_name);
    }
}
