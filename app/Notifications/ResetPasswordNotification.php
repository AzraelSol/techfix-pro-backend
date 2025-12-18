<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{

    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
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
        $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173'));
        $resetUrl = $frontendUrl . '/reset-password?' . http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('🔐 Reset Your Password - ' . config('app.name'))
            ->greeting('Hello, ' . $notifiable->first_name . '!')
            ->line('We received a request to reset the password for your TechFixPro account.')
            ->line('Click the button below to create a new password:')
            ->action('🔑 Reset Password', $resetUrl)
            ->line('**This password reset link will expire in 60 minutes.**')
            ->line('---')
            ->line('**Security Tips:**')
            ->line('• Choose a strong password with at least 8 characters')
            ->line('• Include a mix of letters, numbers, and symbols')
            ->line('• Never share your password with anyone')
            ->line('---')
            ->line('If you didn\'t request a password reset, you can safely ignore this email. Your password will remain unchanged.')
            ->salutation('Stay secure,  ' . "\n" . 'The TechFixPro Team');
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
