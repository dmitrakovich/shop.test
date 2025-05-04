<?php

namespace App\Http\Controllers\Api;

use App\Data\User\UserData;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;

class UserController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(): UserResource
    {
        return new UserResource(user());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserData $userData): UserResource
    {
        user()->update($userData->toArray());
        user()->lastAddress()->updateOrCreate([], $userData->address->toArray());

        return new UserResource(user()->refresh());
    }
}
