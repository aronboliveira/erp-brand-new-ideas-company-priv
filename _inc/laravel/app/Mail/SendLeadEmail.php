<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendLeadEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $lArr;
    public $settings;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($lArr, $settings = null)
    {
        $this->lArr = $lArr;
        $this->settings = $settings;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->view('email.lead_mail')->with('lArr', $this->lArr)->subject($this->subject ?? 'Lead Notification');
    }
}
