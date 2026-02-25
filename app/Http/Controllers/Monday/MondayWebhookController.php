<?php

namespace App\Http\Controllers\Monday;

use App\Actions\Task\CreateTask;
use App\Http\Controllers\Controller;
use App\Models\MondayIntegration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class MondayWebhookController extends Controller
{
    public function handle(Request $request, string $secret): JsonResponse
    {
        if ($request->has('challenge')) {
            return response()->json(['challenge' => $request->input('challenge')]);
        }

        $integration = MondayIntegration::where('secret', $secret)
            ->with(['board.project', 'taskGroup', 'assignedUser'])
            ->first();

        if (! $integration) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $event = $request->input('event', []);

        if (($event['type'] ?? null) !== 'update_column_value' || ($event['columnType'] ?? null) !== 'multiple-person') {
            return response()->json(['status' => 'ignored']);
        }

        $newAssignees = collect(data_get($event, 'value.personsAndTeams', []))->pluck('id')->toArray();
        $prevAssignees = collect(data_get($event, 'previousValue.personsAndTeams', []))->pluck('id')->toArray();

        $mondayUserId = (int) $integration->monday_user_id;

        if (! in_array($mondayUserId, $newAssignees)) {
            return response()->json(['status' => 'ignored']);
        }

        if (in_array($mondayUserId, $prevAssignees)) {
            return response()->json(['status' => 'ignored']);
        }

        $project = $integration->board->project;

        try {
            Auth::loginUsingId($integration->assigned_user_id);

            (new CreateTask)->create($project, [
                'group_id' => $integration->task_group_id,
                'assigned_to_user_id' => $integration->assigned_user_id,
                'name' => $event['pulseName'] ?? 'Untitled task',
                'description' => null,
                'due_on' => null,
                'estimation' => null,
                'priority_id' => null,
                'pricing_type' => $project->default_pricing_type->value,
                'fixed_price' => null,
                'hidden_from_clients' => false,
                'billable' => false,
            ]);
        } catch (Throwable $e) {
            Log::error('Monday webhook task creation failed', [
                'secret' => substr($secret, 0, 8).'...',
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Task creation failed'], 500);
        } finally {
            Auth::logout();
        }

        return response()->json(['status' => 'ok']);
    }
}
