<?php

namespace App\Http\Requests\Monday;

use Illuminate\Foundation\Http\FormRequest;

class StoreMondayIntegrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'monday_board_id' => ['required', 'integer', 'min:1'],
            'monday_user_id' => ['required', 'integer', 'min:1'],
            'task_group_id' => ['required', 'integer', 'exists:task_groups,id'],
            'assigned_user_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
