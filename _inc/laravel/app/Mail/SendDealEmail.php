<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendDealEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $dArr;
    public $settings;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($dArr, $settings = null)
    {
        $this->dArr = $dArr;
        $this->settings = $settings;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {


        return $this->view('email.deal_mail')->with('dArr', $this->dArr)->subject($this->subject ?? 'Deal Notification');
    }
}
