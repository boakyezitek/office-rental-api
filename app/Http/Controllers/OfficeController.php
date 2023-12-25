<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Http\Requests\StoreOfficeRequest;
use App\Http\Requests\UpdateOfficeRequest;
use App\Http\Resources\OfficeResource;
use App\Models\Reservation;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OfficeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index():AnonymousResourceCollection
    {
        $offices = Office::query()
                    ->where('approval_status', Office::APPROVAL_APPROVED)
                    ->where('hidden', false)
                    ->when(request('host_id'), fn ($builder) => $builder->whereUserId(request('host_id')))
                    ->when(request('user_id'),
                    fn($builder) => $builder->whereRelation('reservations', 'user_id', '=', request('user_id')))
                    ->when(request('lat') && request('lng'), fn($builder) => $builder->nearestTo(request('lat'), request('lng')),
                    fn($builder) => $builder->orderBy('id', 'ASC'))
                    ->with('images', 'tags', 'user')
                    ->withCount(['reservations' => fn ($builder) => $builder->where('status', Reservation::STATUS_ACTIVE)])
                    ->paginate(20);

        return OfficeResource::collection($offices);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOfficeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Office $office)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Office $office)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOfficeRequest $request, Office $office)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Office $office)
    {
        //
    }
}
