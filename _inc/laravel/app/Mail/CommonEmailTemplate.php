<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CommonEmailTemplate extends Mailable
{
    use Queueable, SerializesModels;

    public $template;
    public $settings;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($template, $settings)
    {

        $this->template = $template;
        $this->settings = $settings;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $fromAddress = is_array($this->settings) ? ($this->settings['mail_from_address'] ?? config('mail.from.address')) : config('mail.from.address');
        $fromName = is_object($this->template) ? ($this->template->from ?? config('mail.from.name')) : config('mail.from.name');
        $subject = is_object($this->template) ? ($this->template->subject ?? '') : '';
        $content = is_object($this->template) ? ($this->template->content ?? '') : '';
        return $this->from($fromAddress, $fromName)->markdown('email.common_email_template')->subject($subject)->with('content', $content);
    }
}
