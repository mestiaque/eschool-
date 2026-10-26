<?php

namespace ME\SchoolManagement\Http\Controllers;
use ME\Http\Controllers\Controller;
use ME\SchoolManagement\Http\Controllers\Concerns\GuardsDeletes;

use Illuminate\Http\Request;
use ME\SchoolManagement\Models\TransportRoute;
use ME\SchoolManagement\Models\Vehicle;
use ME\SchoolManagement\Models\RouteStop;
use ME\SchoolManagement\Models\RouteSchedule;
use ME\SchoolManagement\Models\VehicleRoute;

class TransportController extends Controller
{
    use GuardsDeletes;

    // Routes
    public function routeIndex()
    {
        $routes   = TransportRoute::with(['stops','schedules.vehicle'])->orderBy('route_name')->paginate(15)->withQueryString();
        $vehicles = Vehicle::where('is_active', true)->orderBy('vehicle_number')->get();
        return view('school-management::transport.routes.index', compact('routes', 'vehicles'));
    }

    // Schedule
    public function scheduleStore(Request $request)
    {
        $request->validate([
            'route_id'       => 'required|exists:transport_routes,id',
            'departure_time' => 'required',
        ]);
        $data = $request->only(['route_id','vehicle_id','departure_time','arrival_time','direction','notes']);
        $data['days']      = $request->days ?? [];
        $data['is_active'] = 1;
        RouteSchedule::create($data);
        return back()->with('success', 'Schedule added.');
    }

    public function scheduleUpdate(Request $request, $id)
    {
        $request->validate(['departure_time' => 'required']);
        $data = $request->only(['vehicle_id','departure_time','arrival_time','direction','notes']);
        $data['days'] = $request->days ?? [];
        RouteSchedule::findOrFail($id)->update($data);
        return back()->with('success', 'Schedule updated.');
    }

    public function scheduleDestroy($id)
    {
        RouteSchedule::findOrFail($id)->delete();
        return back()->with('success', 'Schedule deleted.');
    }

    public function scheduleChart()
    {
        $routes = TransportRoute::with(['schedules.vehicle'])
            ->where('is_active', true)
            ->orderBy('route_name')
            ->get();
        $directions = RouteSchedule::directions();
        $allDays    = RouteSchedule::allDays();
        return view('school-management::transport.routes.schedule', compact('routes','directions','allDays'));
    }

    public function scheduleChartPrint()
    {
        $routes = TransportRoute::with(['schedules.vehicle'])
            ->where('is_active', true)
            ->orderBy('route_name')
            ->get();
        $directions = RouteSchedule::directions();
        $allDays    = RouteSchedule::allDays();
        return view('school-management::transport.routes.schedule-print', compact('routes','directions','allDays'));
    }

    public function routeStore(Request $request)
    {
        TransportRoute::create($this->validateRoute($request));
        return back()->with('success', 'Route created');
    }

    public function routeUpdate(Request $request, $id)
    {
        TransportRoute::findOrFail($id)->update($this->validateRoute($request));
        return back()->with('success', 'Route updated');
    }

    public function routeDestroy($id)
    {
        $route = TransportRoute::findOrFail($id);
        return $this->deleteUnlessUsed($route, [
            'vehicle assignments' => $this->uses('vehicle_routes', 'route_id', $id),
            'schedules'           => $this->uses('route_schedules', 'route_id', $id),
        ], $route->route_name, 'Route deleted');
    }

    // Vehicles
    public function vehicleIndex()
    {
        $vehicles = Vehicle::orderBy('id', 'desc')->paginate(20);
        return view('school-management::transport.vehicles.index', compact('vehicles'));
    }

    public function vehicleStore(Request $request)
    {
        Vehicle::create($this->validateVehicle($request));
        return back()->with('success', 'Vehicle added');
    }

    public function vehicleUpdate(Request $request, $id)
    {
        Vehicle::findOrFail($id)->update($this->validateVehicle($request, $id));
        return back()->with('success', 'Vehicle updated');
    }

    public function vehicleDestroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        return $this->deleteUnlessUsed($vehicle, [
            'route assignments' => $this->uses('vehicle_routes', 'vehicle_id', $id),
            'schedules'         => $this->uses('route_schedules', 'vehicle_id', $id),
        ], $vehicle->vehicle_number, 'Vehicle deleted');
    }

    // Route Stops
    public function stopStore(Request $request)
    {
        $data = $request->validate([
            'route_id'       => 'required|exists:transport_routes,id',
            'stop_name'      => 'required|string|max:255',
            'arrival_time'   => 'required',
            'departure_time' => 'nullable',
            'latitude'       => 'nullable|numeric',
            'longitude'      => 'nullable|numeric',
            'extra_fee'      => 'nullable|numeric|min:0',
        ]);
        $data['extra_fee'] = $data['extra_fee'] ?? 0;
        RouteStop::create($data);
        return back()->with('success', 'Stop added');
    }

    public function stopDestroy($id)
    {
        RouteStop::findOrFail($id)->delete();
        return back()->with('success', 'Stop deleted');
    }

    // Vehicle Route Assignment
    public function assignmentIndex(Request $request)
    {
        $query = VehicleRoute::with(['vehicle', 'route']);
        if ($request->route_id) {
            $query->where('route_id', $request->route_id);
        }
        $assignments = $query->paginate(20)->withQueryString();
        $vehicles = Vehicle::where('is_active', true)->get();
        $routes = TransportRoute::where('is_active', true)->get();
        return view('school-management::transport.assignments', compact('assignments', 'vehicles', 'routes'));
    }

    public function assignmentStore(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'route_id'   => 'required|exists:transport_routes,id',
        ]);
        VehicleRoute::firstOrCreate($data, ['is_active' => true]);
        return back()->with('success', 'Assignment created');
    }

    public function assignmentDestroy($id)
    {
        VehicleRoute::findOrFail($id)->delete();
        return back()->with('success', 'Assignment deleted');
    }

    // Drivers
    public function driverIndex()
    {
        $drivers = Vehicle::whereNotNull('driver_name')->orderBy('id', 'desc')->paginate(20);
        $vehicles = Vehicle::where('is_active', true)->orderBy('vehicle_number')->get();
        return view('school-management::transport.drivers', compact('drivers', 'vehicles'));
    }

    public function driverStore(Request $request)
    {
        $request->validate(['vehicle_id' => 'required|exists:vehicles,id', 'driver_name' => 'required|string|max:255']);
        Vehicle::findOrFail($request->vehicle_id)->update([
            'driver_name'    => $request->driver_name,
            'driver_phone'   => $request->driver_phone,
            'driver_license' => $request->driver_license,
        ]);
        return back()->with('success', 'Driver added');
    }

    public function driverUpdate(Request $request, $id)
    {
        $request->validate(['driver_name' => 'required']);
        Vehicle::findOrFail($id)->update([
            'driver_name'    => $request->driver_name,
            'driver_phone'   => $request->driver_phone,
            'driver_license' => $request->driver_license,
        ]);
        return back()->with('success', 'Driver updated');
    }

    public function driverDestroy($id)
    {
        Vehicle::findOrFail($id)->update(['driver_name' => null, 'driver_phone' => null, 'driver_license' => null]);
        return back()->with('success', 'Driver removed');
    }

    // Transport Fee
    public function transportFeeIndex()
    {
        $routes = TransportRoute::with('stops')->withCount(['students' => fn ($q) => $q->where('is_active', true)])
            ->where('is_active', true)->get();
        return view('school-management::transport.fee', compact('routes'));
    }

    /** Bill one month of transport for every student on a route (route fee + stop extra fee). */
    public function transportFeeGenerate(Request $request)
    {
        $request->validate([
            'month'    => 'required|integer|min:1|max:12',
            'due_date' => 'nullable|date',
        ]);
        $count = \ME\SchoolManagement\Services\FeeBilling::generateTransport((int) $request->month, null,
            \ME\SchoolManagement\Models\AcademicYear::where('is_current', true)->value('id'), $request->due_date);

        return back()->with($count ? 'success' : 'error', $count
            ? "Transport fee invoices created for {$count} student(s)."
            : 'Nobody to bill: no students on a route, or this month is already billed.');
    }

    public function transportFeeStore(Request $request)
    {
        $request->validate([
            'route_id' => 'required|exists:transport_routes,id',
            'fee'      => 'required|numeric|min:0',
        ]);
        TransportRoute::findOrFail($request->route_id)->update(['fee' => $request->fee]);
        return back()->with('success', 'Transport fee updated');
    }

    private function validateRoute(Request $request): array
    {
        $data = $request->validate([
            'route_name'  => 'required|string|max:255',
            'start_point' => 'required|string|max:255',
            'end_point'   => 'required|string|max:255',
            'distance'    => 'nullable|numeric|min:0',
            'fee'         => 'nullable|numeric|min:0',
        ]);
        $data['fee']       = $data['fee'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');
        return $data;
    }

    private function validateVehicle(Request $request, $id = null): array
    {
        return $request->validate([
            'vehicle_number' => 'required|string|max:255|unique:vehicles,vehicle_number' . ($id ? ",$id" : ''),
            'model'          => 'nullable|string|max:255',
            'type'           => 'required|string|max:255',
            'capacity'       => 'required|integer|min:1',
            'driver_name'    => 'nullable|string|max:255',
            'driver_phone'   => 'nullable|string|max:50',
            'driver_license' => 'nullable|string|max:255',
        ]);
    }
}
