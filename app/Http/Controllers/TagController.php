<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Http\Resources\TagResource;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * This method is invoked when the controller is accessed, and it returns
     * a collection of TagResource instances representing all Tag records.
     *
     * @return \App\Http\Resources\TagResourceCollection
     */
    public function __invoke()
    {
        return TagResource::collection(Tag::all());
    }

}
