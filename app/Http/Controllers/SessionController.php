<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Workspace;

class SessionController extends Controller
{
    public function setClient(Request $request)
    {
        $clientId = $request->input('client_id');

        if (Client::find($clientId)) {
            session(['selected_client_id' => $clientId]);
            session()->forget('selected_workspace_id');

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function setWorkspace(Request $request)
    {
        $workspaceId = $request->input('workspace_id');

        if (Workspace::find($workspaceId)) {
            session(['selected_workspace_id' => $workspaceId]);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function dashboard($client_id, $workspace_id)
    {
        // Verify client and workspace match
        $workspace = Workspace::where('id', $workspace_id)->where('client_id', $client_id)->first();

        if (!$workspace) {
            abort(404, 'Workspace not found for the client');
        }

        return view('dashboard', compact('client_id', 'workspace_id'));
    }
}
