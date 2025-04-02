<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\WelcomeCustomerNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class TestWelcomeEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test-welcome {email} {name?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test welcome email to the specified email address';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $name = $this->argument('name') ?? 'Test User';

        $this->info("Sending welcome email to: {$email}");
        
        // Add debug logging
        Log::debug("TestWelcomeEmail: Preparing to send welcome email to {$email}");

        // Create a temporary user or send directly to the email
        $user = new User([
            'name' => $name,
            'email' => $email,
        ]);

        // Send the notification
        try {
            Log::debug("TestWelcomeEmail: Attempting to send WelcomeCustomerNotification");
            Notification::send($user, new WelcomeCustomerNotification($name));
            Log::debug("TestWelcomeEmail: Notification sent successfully");
        } catch (\Exception $e) {
            Log::error("TestWelcomeEmail: Failed to send notification: " . $e->getMessage());
            $this->error("Error sending email: " . $e->getMessage());
            return Command::FAILURE;
        }

        $this->info('Test email sent! Check your logs or email inbox.');
        
        return Command::SUCCESS;
    }
}
