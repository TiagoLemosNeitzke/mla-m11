<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    public function index()
    {
        return ClientResource::collection(Client::with('user')->get());
    }

    public function store(StoreClientRequest $request)
    {
        DB::transaction(function () use ($request) {
            $user = User::create([
                'email' => $request->get('email'),
                'password' => Hash::make($request->get('password'))
            ]);

            $user->client()->create([
                
                'name' => $request->get('name')
            ]);
        });


        return response()->json(status: JsonResponse::HTTP_CREATED);
    }

    public function show(Client $client)
    {
        return new ClientResource($client->load('user'));
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        DB::transaction(function() use($request, $client) {
            $clientName = $request->get('name', $client->name);

            $userEmail = $request->get('email', $client->user->email);

            $userPassword = $request->get('password', $client->user->password);

            $client->update([
                'name' => $clientName
            ]);

            $client->user->update([
                'email' => $userEmail,
                'password' => Hash::make($userPassword)
            ]);

            return response()->json(status: JsonResponse::HTTP_NO_CONTENT);
        });
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return response()->json(status: JsonResponse::HTTP_NO_CONTENT);
    }
}
