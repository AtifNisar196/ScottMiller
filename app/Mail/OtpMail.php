<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{

    use Queueable, SerializesModels;

    protected $data;


    public function __construct($data)
    {

        $this->data = $data;
    }

    public function build()
    {
        $data = $this->data;

        return $this->markdown('mails.otp-mail', compact('data'));
    }
}
