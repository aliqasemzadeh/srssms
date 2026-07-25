<?php

namespace App\Livewire\Forms\Support;

use App\Enums\Support\TicketStatusEnum;
use App\Models\Support\Ticket;
use App\Models\Support\TicketReply;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

class TicketReplyForm extends Form
{
    public ?Ticket $ticket = null;

    public string $body = '';

    public ?TemporaryUploadedFile $file = null;

    public function setTicket(Ticket $ticket): void
    {
        $this->ticket = $ticket;
    }

    public function resetFields(): void
    {
        $this->body = '';
        $this->file = null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
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
            'file' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,gif,webp,pdf,zip,doc,docx'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'body' => __('general.ticket_message'),
            'file' => __('general.ticket_attachment'),
        ];
    }

    public function store(): TicketReply
    {
        $this->validate();

        if (! $this->ticket) {
            abort(403);
        }

        $this->ticket->refresh();

        if ($this->ticket->isClosed()) {
            abort(403, __('general.ticket_closed'));
        }

        return DB::transaction(function (): TicketReply {
            $filePath = null;
            $fileName = null;

            if ($this->file instanceof TemporaryUploadedFile) {
                $filePath = $this->file->store('tickets/'.$this->ticket->id, 'public');
                $fileName = $this->file->getClientOriginalName();
            }

            $reply = TicketReply::query()->create([
                'ticket_id' => $this->ticket->id,
                'user_id' => Auth::id(),
                'body' => $this->body,
                'file' => $filePath,
                'file_name' => $fileName,
                'ip_address' => request()->ip(),
            ]);

            $isOwner = $this->ticket->isOwnedBy(Auth::user());

            $this->ticket->update([
                'status' => $isOwner
                    ? TicketStatusEnum::CustomerReply
                    : TicketStatusEnum::SupportReply,
                'last_replied_at' => now(),
            ]);

            $this->resetFields();

            return $reply;
        });
    }
}
