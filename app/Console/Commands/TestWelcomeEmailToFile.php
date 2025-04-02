<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\WelcomeCustomerNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

class TestWelcomeEmailToFile extends Command
{
    protected $signature = 'email:save-welcome {email} {name?}';
    protected $description = 'Generate a welcome email and save it to a file';

    public function handle()
    {
        $email = $this->argument('email');
        $name = $this->argument('name') ?? 'Test User';

        $this->info("Generating welcome email for: {$email}");

        // Create a temporary user
        $user = new User([
            'name' => $name,
            'email' => $email,
        ]);

        // Generate the notification
        $notification = new WelcomeCustomerNotification($name);
        
        // Generate the mail message
        $mailMessage = $notification->toMail($user);
        
        // Extract content directly from the mail message
        $content = "TO: {$email}\n";
        $content .= "FROM: " . config('mail.from.address') . " (" . config('mail.from.name') . ")\n";
        $content .= "SUBJECT: " . $mailMessage->subject . "\n";
        $content .= "=== Welcome Email Content ===\n\n";
        
        // Add mail message properties
        $content .= "Greeting: " . $mailMessage->greeting . "\n";
        
        $content .= "Intro Lines:\n";
        foreach ($mailMessage->introLines as $line) {
            $content .= "- {$line}\n";
        }
        
        $content .= "\nOutro Lines:\n";
        foreach ($mailMessage->outroLines as $line) {
            $content .= "- {$line}\n";
        }
        
        $content .= "\nAction: " . $mailMessage->actionText . " -> " . $mailMessage->actionUrl . "\n";
        $content .= "Salutation: " . $mailMessage->salutation . "\n";
        
        // Add metadata
        $content .= "\n=== Mail Configuration ===\n";
        $content .= "Mail Driver: " . config('mail.default') . "\n";
        $content .= "Mail From: " . config('mail.from.address') . " (" . config('mail.from.name') . ")\n";
        
        if (config('mail.default') === 'log') {
            $content .= "Log Path: " . config('mail.mailers.log.path') . "\n";
        }
        
        // Write to text file
        $textFilename = storage_path('app/welcome_email_' . time() . '.txt');
        File::put($textFilename, $content);
        
        // Try to manually create simplified HTML version
        $htmlContent = "<!DOCTYPE html>
<html>
<head>
    <title>Welcome to BakeHub!</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border: 1px solid #e5e5e5; border-radius: 5px; }
        .header { background: #f8f9fa; padding: 20px; text-align: center; border-bottom: 1px solid #e5e5e5; }
        .content { padding: 20px; }
        h1 { color: #333; }
        .button { display: inline-block; background: #3490dc; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { background: #f8f9fa; padding: 20px; border-top: 1px solid #e5e5e5; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>BakeHub</h1>
        </div>
        <div class='content'>
            <h2>" . htmlspecialchars($mailMessage->greeting) . "</h2>";
        
        foreach ($mailMessage->introLines as $line) {
            $htmlContent .= "<p>" . htmlspecialchars($line) . "</p>";
        }
        
        $htmlContent .= "<a href='" . htmlspecialchars($mailMessage->actionUrl) . "' class='button'>" . htmlspecialchars($mailMessage->actionText) . "</a>";
        
        foreach ($mailMessage->outroLines as $line) {
            $htmlContent .= "<p>" . htmlspecialchars($line) . "</p>";
        }
        
        $htmlContent .= "<p>" . $mailMessage->salutation . "</p>
        </div>
        <div class='footer'>
            <p>&copy; " . date('Y') . " BakeHub. All rights reserved.</p>
        </div>
    </div>
</body>
</html>";
        
        // Save HTML version
        $htmlFilename = storage_path('app/welcome_email_' . time() . '.html');
        File::put($htmlFilename, $htmlContent);
        
        $this->info("Text email content saved to: {$textFilename}");
        $this->info("HTML email preview saved to: {$htmlFilename}");
        
        $this->line("\nEmail preview saved. Run the following to view it:");
        $this->line("notepad {$textFilename}");
        $this->line("start {$htmlFilename}  # Open HTML in default browser");
        
        return Command::SUCCESS;
    }
} 