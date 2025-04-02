<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class WelcomeCustomerNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The customer name.
     *
     * @var string
     */
    protected $name;

    /**
     * Create a new notification instance.
     */
    public function __construct($name)
    {
        $this->name = $name;
        Log::debug("WelcomeCustomerNotification: Constructed with name '{$name}'");
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        Log::debug("WelcomeCustomerNotification: Using 'mail' channel for " . $notifiable->email);
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        Log::debug("WelcomeCustomerNotification: Building email for " . $notifiable->email);
        
        $mailMessage = (new MailMessage)
            ->subject('Welcome to BakeHub!')
            ->greeting('Hello ' . $this->name . '!')
            ->line('Welcome to BakeHub! We are excited to have you as a member of our bakery community.')
            ->line('At BakeHub, we offer a wide variety of delicious bakery products made with the finest ingredients.')
            ->line('You can now place orders, track deliveries, and enjoy special promotions.')
            ->action('Browse Our Products', url('/products'))
            ->line('If you have any questions or need assistance, feel free to contact our customer support team.')
            ->line('Thank you for choosing BakeHub!')
            ->salutation('Warm regards,<br>The BakeHub Team');
        
        Log::debug("WelcomeCustomerNotification: Email built successfully");
        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'name' => $this->name
        ];
    }
}
