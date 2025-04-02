<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CheckEmailConfig extends Command
{
    protected $signature = 'email:diagnose';
    protected $description = 'Diagnose email configuration issues';

    public function handle()
    {
        $this->info("=== Email Configuration Diagnosis ===");
        
        // Check mail configuration
        $this->info("\n📧 Mail Configuration:");
        $mailDriver = config('mail.default');
        $this->line("Driver: " . $mailDriver);
        $this->line("From Address: " . config('mail.from.address'));
        $this->line("From Name: " . config('mail.from.name'));
        
        // Check for log driver-specific configuration
        if ($mailDriver === 'log') {
            $logChannel = config('mail.mailers.log.channel');
            $logPath = config('mail.mailers.log.path');
            
            $this->line("Log Channel: " . $logChannel);
            $this->line("Log Path: " . $logPath);
            
            // Check if log path exists
            if (File::exists($logPath)) {
                $this->info("✅ Log file exists: {$logPath}");
                $this->line("Size: " . round(File::size($logPath) / 1024, 2) . " KB");
                $this->line("Last modified: " . date('Y-m-d H:i:s', File::lastModified($logPath)));
            } else {
                $this->error("❌ Log file does not exist: {$logPath}");
                // Check if directory exists
                $logDir = dirname($logPath);
                if (!File::isDirectory($logDir)) {
                    $this->warn("Log directory does not exist: {$logDir}");
                    $this->line("Try running: mkdir -p {$logDir}");
                }
            }
            
            $this->info("\n📬 Mail Behavior with Log Driver:");
            $this->line("When using the 'log' driver, emails are NOT actually sent to recipients.");
            $this->line("Instead, the email content is written to the Laravel log file.");
            $this->line("To check if emails are being properly logged, look for entries with 'Message-ID', 'Subject', etc.");
            $this->line("To send real emails, change MAIL_MAILER in .env to 'smtp' and configure the SMTP settings.");
        }
        // Check for SMTP driver-specific configuration
        elseif ($mailDriver === 'smtp') {
            $this->line("Host: " . config('mail.mailers.smtp.host'));
            $this->line("Port: " . config('mail.mailers.smtp.port'));
            $this->line("Encryption: " . config('mail.mailers.smtp.encryption'));
            $this->line("Username: " . config('mail.mailers.smtp.username'));
            $this->line("Password: " . (empty(config('mail.mailers.smtp.password')) ? 'Not set' : '******'));
            
            $this->info("\n📬 Mail Behavior with SMTP Driver:");
            $this->line("The application is configured to send real emails via SMTP.");
            $this->line("If emails are not being received, check:");
            $this->line("1. SMTP server credentials and connectivity");
            $this->line("2. Firewall/network settings");
            $this->line("3. Email delivery logs on the SMTP server");
        }
        
        // Check mail directory structure
        $this->info("\n📁 Mail-related Files:");
        
        // Check for mail notification classes
        $notificationPath = app_path('Notifications');
        if (File::isDirectory($notificationPath)) {
            $notifications = collect(File::files($notificationPath))
                ->filter(function ($file) {
                    return $file->getExtension() === 'php';
                })
                ->map(function ($file) {
                    return $file->getFilename();
                });
            
            $this->line("Notification Classes: ");
            foreach ($notifications as $notification) {
                $this->line("- {$notification}");
            }
        } else {
            $this->warn("No Notifications directory found at {$notificationPath}");
        }
        
        // Check mail views
        $mailViewPath = resource_path('views/vendor/mail');
        if (File::isDirectory($mailViewPath)) {
            $this->info("✅ Custom mail views directory exists");
        } else {
            $this->line("No custom mail views directory (using Laravel defaults)");
        }
        
        // Check if welcome notification test command exists
        $welcomeTestPath = app_path('Console/Commands/TestWelcomeEmail.php');
        if (File::exists($welcomeTestPath)) {
            $this->info("✅ Welcome Email test command exists");
            $this->line("You can test welcome emails with: php artisan email:test-welcome [email] [name]");
        }
        
        // Check mail logs
        $this->info("\n📝 Recent Mail Logs:");
        
        $logFile = storage_path('logs/laravel.log');
        if (File::exists($logFile)) {
            // Get last 10 mail-related log entries
            $logContent = File::get($logFile);
            $matches = [];
            preg_match_all('/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\].*?(?:mail|Mail|email|Email|SMTP|smtp|Subject:|From:|To:|Welcome).*?$/m', $logContent, $matches);
            
            if (!empty($matches[0])) {
                $mailLogs = array_slice($matches[0], -10);
                foreach ($mailLogs as $log) {
                    $this->line($log);
                }
            } else {
                $this->warn("No recent mail-related log entries found");
            }
        } else {
            $this->error("❌ Laravel log file not found at {$logFile}");
        }
        
        $this->info("\n✅ Email diagnosis completed!");
        
        if ($mailDriver === 'log') {
            $this->info("\n📋 Recommendation:");
            $this->line("Your application is currently configured to log emails instead of sending them.");
            $this->line("To test if an email is being properly generated:");
            $this->line("1. Run: php artisan email:save-welcome test@example.com \"Test User\"");
            $this->line("2. This will save the email content to a file you can view.");
            $this->line("\nTo send real emails:");
            $this->line("1. Update your .env file with SMTP settings");
            $this->line("2. Set MAIL_MAILER=smtp");
            $this->line("3. Run php artisan config:clear");
        }
        
        return Command::SUCCESS;
    }
} 