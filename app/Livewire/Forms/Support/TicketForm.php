<?php

namespace App\Livewire\Forms\Support;

use App\Enums\Support\TicketPriorityEnum;
use App\Enums\Support\TicketStatusEnum;
use App\Models\Support\Ticket;
use App\Models\Support\TicketReply;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

class TicketForm extends Form
{
    public string $title = '';

    public string $body = '';

    public string $priority = '';

    public ?TemporaryUploadedFile $file = null;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => [
                'required',
                'string',
                'max:50000',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (trim(strip_tags((string) $value)) === '') {
                        $fail(__('validation.required', ['attribute' => __('general.ticket_message')]));
                    }
                },
            ],
            'priority' => ['required', 'string', Rule::enum(TicketPriorityEnum::class)],
            'file' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,gif,webp,pdf,zip,doc,docx'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'title' => __('general.ticket_title'),
            'body' => __('general.ticket_message'),
            'priority' => __('general.ticket_priority'),
            'file' => __('general.ticket_attachment'),
        ];
    }

    public function store(): Ticket
    {
        if ($this->priority === '') {
            $this->priority = TicketPriorityEnum::Medium->value;
        }

        $this->validate();

        return DB::transaction(function (): Ticket {
            $now = now();

            $ticket = Ticket::query()->create([
                'user_id' => Auth::id(),
                'title' => $this->title,
                'status' => TicketStatusEnum::New,
                'priority' => $this->priority,
                'last_replied_at' => $now,
            ]);

            $filePath = null;
            $fileName = null;

            if ($this->file instanceof TemporaryUploadedFile) {
                $filePath = $this->file->store('tickets/'.$ticket->id, 'public');
                $fileName = $this->file->getClientOriginalName();
            }

            TicketReply::query()->create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'body' => $this->body,
                'file' => $filePath,
                'file_name' => $fileName,
                'ip_address' => request()->ip(),
            ]);

            return $ticket;
        });
    }
}
