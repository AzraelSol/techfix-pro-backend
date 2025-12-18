<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends Notification
{

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('🔧 Verify Your Email - ' . config('app.name'))
            ->greeting('Welcome to TechFixPro, ' . $notifiable->first_name . '! 👋')
            ->line('Thank you for creating an account with us. We\'re excited to help you with all your hardware repair needs!')
            ->line('To get started, please verify your email address by clicking the button below:')
            ->action('✓ Verify Email Address', $verificationUrl)
            ->line('**This verification link will expire in 60 minutes.**')
            ->line('Once verified, you\'ll be able to:')
            ->line('• Book hardware repair appointments')
            ->line('• Track your repair status in real-time')
            ->line('• Access your repair history')
            ->line('---')
            ->line('If you did not create an account, please ignore this email.')
            ->salutation('Best regards,  ' . "\n" . 'The TechFixPro Team');
    }

    /**
     * Get the verification URL for the given notifiable.
     */
    protected function verificationUrl(object $notifiable): string
    {
        $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173'));
        
        // Create a signed URL that expires in 60 minutes
        $temporarySignedUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        // Extract the signature and expiration from the backend URL
        $parsedUrl = parse_url($temporarySignedUrl);
        parse_str($parsedUrl['query'] ?? '', $queryParams);

        // Build the frontend verification URL
        return $frontendUrl . '/verify-email?' . http_build_query([
            'id' => $notifiable->getKey(),
            'hash' => sha1($notifiable->getEmailForVerification()),
            'expires' => $queryParams['expires'] ?? '',
            'signature' => $queryParams['signature'] ?? '',
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
