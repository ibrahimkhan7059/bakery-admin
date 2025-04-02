<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailLogging extends Command
{
    protected $signature = 'email:test-logging';
    protected $description = 'Test if email logging is working correctly';

    public function handle()
    {
        $this->info('Sending test email to log...');
        
        try {
            Mail::raw('This is a test email to verify logging is working', function ($message) {
                $message->to('test@example.com')
                    ->subject('Test Email Logging');
            });
            
            $this->info('Test email sent! Check logs at: ' . config('mail.mailers.log.path'));
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Error sending test email: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 