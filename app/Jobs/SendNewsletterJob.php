<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNewsletterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;
    protected $subject;
    protected $bodyContent;

    /**
     * Create a new job instance.
     */
    public function __construct($email, $subject, $bodyContent)
    {
        $this->email = $email;
        $this->subject = $subject;
        $this->bodyContent = $bodyContent;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Illuminate\Support\Facades\Mail::to($this->email)->send(
            new \App\Mail\NewsletterMail($this->subject, $this->bodyContent, $this->email)
        );
    }
}
