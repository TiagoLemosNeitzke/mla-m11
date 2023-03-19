<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\ClientResource;
use App\Http\Resources\ClientCollection;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;

class ClientController extends Controller
{
    public function index()
    {
        abort_if(!auth()->user()->tokenCan('client:index'), 401, 'Unauthorized.');

        return new ClientCollection(Client::with('user')->paginate());
    }

    public function store(StoreClientRequest $request)
    {
        abort_if(!auth()->user()->tokenCan('client:admin'), 401, 'Unauthorized.');
        
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
        abort_if(!auth()->user()->tokenCan('client:admin'), 401, 'Unauthorized.');

        DB::transaction(function () use ($request, $client) {
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
        abort_if(!auth()->user()->tokenCan('client:admin'), 401, 'Unauthorized.');

        $client->delete();

        return response()->json(status: JsonResponse::HTTP_NO_CONTENT);
    }
}
