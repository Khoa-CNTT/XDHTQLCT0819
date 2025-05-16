<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Savingoal;

class GoalCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $goal;

    public function __construct(Savingoal $goal)
    {
        $this->goal = $goal;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 Chúc mừng! Bạn đã hoàn thành mục tiêu tiết kiệm'
        );
    }
    public function content(): Content
    {
        return new Content(
            view: 'emails.goal_completed',
            with: [
                'goal' => $this->goal
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
