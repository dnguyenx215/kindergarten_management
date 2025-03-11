<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\VonageMessage; // Sửa lại từ NexmoMessage sang VonageMessage
use Carbon\Carbon;

class TuitionFeeReminder extends Notification implements ShouldQueue
{
    use Queueable;

    protected $tuitionFee;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\TuitionFee  $tuitionFee
     */
    public function __construct($tuitionFee)
    {
        $this->tuitionFee = $tuitionFee;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // Kiểm tra nếu có email thì gửi qua mail, có số điện thoại thì gửi qua SMS
        $channels = [];

        if ($notifiable->email) {
            $channels[] = 'mail';
        }
        if ($notifiable->phone) {
            $channels[] = 'vonage';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Nhắc nhở thanh toán học phí')
                    ->line('Số tiền học phí của bạn: ' . number_format($this->tuitionFee->amount, 2) . ' VND')
                    ->line('Ngày đến hạn: ' . Carbon::parse($this->tuitionFee->due_date)->format('d/m/Y'))
                    ->action('Thanh toán ngay', url('/payment'))
                    ->line('Vui lòng thanh toán trước ngày đến hạn để tránh phí trễ hạn.');
    }

    /**
     * Get the SMS representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\VonageMessage
     */
    public function toVonage($notifiable)
    {
        return (new VonageMessage)
                    ->content('Nhắc nhở: Học phí ' . number_format($this->tuitionFee->amount, 2) . ' VND sẽ đến hạn vào ' . Carbon::parse($this->tuitionFee->due_date)->format('d/m/Y') . '. Vui lòng thanh toán kịp thời.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'tuition_fee_id' => $this->tuitionFee->id,
            'amount'         => $this->tuitionFee->amount,
            'due_date'       => Carbon::parse($this->tuitionFee->due_date)->format('Y-m-d'),
        ];
    }
}
