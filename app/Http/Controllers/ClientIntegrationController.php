<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\XeroConnection;
use Illuminate\Support\Facades\Auth;

class ClientIntegrationController extends Controller
{
    private $user_id;
    private $role_id;
    private $clientID;
    private $ws_id;

    public function __construct()
    {
        $this->user_id = session('user_id');
        $this->role_id = session('role_id');
        $this->clientID = session('client');
        $this->ws_id = session('workspace');
    } 

    public function show()   // ← no argument
    {
        // get the active client from session (same pattern as your sidebar)
        $clientId = $this->clientID;
        abort_unless($clientId, 400, 'Missing client context');

        $connections = XeroConnection::where('client_id', $clientId)      // scope to current client
            ->orderBy('tenant_name')
            ->get();

        $client = Client::findOrFail($clientId);

        return view('backend.client-integrations.show', compact('connections', 'client'));
    }
}