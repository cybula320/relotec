<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LogowanieNotification extends Mailable
{
    use Queueable, SerializesModels;

    public array $logInfo;

    public function __construct(array $logInfo)
    {
        $this->logInfo = $logInfo;
    }

    public function build()
    {
        return $this->subject('🔐 Nowe logowanie do systemu')
            ->view('emails.logowanie');
    }
}