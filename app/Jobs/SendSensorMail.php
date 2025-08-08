<?php

namespace App\Jobs;

use App\Mail\SensorMail;
use App\Models\email_log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendSensorMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $to;
    protected $data;
    /**
     * Create a new job instance.
     */
    public function __construct($to, $data)
    {
        $this->to = $to;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->to)->send(new SensorMail($this->data));

            email_log::create([
                'email' => $this->to,
                'subject' => $this->data['subject'] ?? 'No Subject',
                'content' => json_encode($this->data),
                'status' => 'success',
                'sensor_id' => $this->data['sensor_id'],
                'device' => $this->data['device'],
            ]);
        } catch (\Exception $e) {
            email_log::create([
                'email' => $this->to,
                'subject' => $this->data['subject'] ?? 'No Subject',
                'content' => json_encode($this->data),
                'status' => 'failed',
                'sensor_id' => $this->data['sensor_id'],
                'device' => $this->data['device'],
            ]);
        }
    }
}
