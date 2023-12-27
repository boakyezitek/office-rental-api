<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Http\Requests\StoreOfficeRequest;
use App\Http\Requests\UpdateOfficeRequest;
use App\Http\Resources\OfficeResource;
use App\Models\Reservation;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
                    ->when(request('user_id'), fn ($builder) => $builder->whereUserId(request('user_id')))
                    ->when(request('visitor_id'),
                    fn($builder) => $builder->whereRelation('reservations', 'user_id', '=', request('visitor_id')))
                    ->when(request('lat') && request('lng'), fn($builder) => $builder->nearestTo(request('lat'), request('lng')),
                    fn($builder) => $builder->orderBy('id', 'ASC'))
                    ->with(['images', 'tags', 'user'])
                    ->withCount(['reservations' => fn ($builder) => $builder->where('status', Reservation::STATUS_ACTIVE)])
                    ->paginate(20);

        return OfficeResource::collection($offices);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function create():JsonResource
    {
        $abort = abort_unless(auth()->user()->tokenCan('office.create'),
            Response::HTTP_FORBIDDEN
        );

        dd($abort);
        $attributes = validator(
            request()->all(),
            [
                'title' => ['required', 'string'],
                'description' => ['required', 'string'],
                'lat' => ['required', 'numeric'],
                'lng' => ['required', 'numeric'],
                'address_line1' => ['required', 'string'],
                'hidden' => ['bool'],
                'price_per_day' => ['required', 'integer', 'min:100'],
                'monthly_discount' => ['integer', 'min:0'],
                'tags' => ['array'],
                'tags.*' => ['integer', Rule::exists('tags', 'id')]
            ]
        )->validate();

        $attributes['approval_status'] = Office::APPROVAL_PENDING;



        $office = DB::transaction(function () use ($attributes) {
            $office = Auth::user()->offices()->create(
                Arr::except($attributes, ['tags'])
            );

            $office->tags()->attach($attributes['tags']);
            return $office;
        });


        return OfficeResource::make($office);
    }

    /**
     * Display the specified resource.
     */
    public function show(Office $office)
    {
        $office->loadCount(['reservations' => fn ($builder) => $builder->where('status', Reservation::STATUS_ACTIVE)])
        ->load(['images', 'tags', 'user']);

        return OfficeResource::make($office);
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
