<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailService
{

    /**
     * Gửi email thông thường
     *
     * @param string $to Email người nhận
     * @param string $subject Tiêu đề email
     * @param string $content Nội dung email
     * @param array $attachments Danh sách file đính kèm
     * @return bool
     */
    public function sendMail(string $to, string $subject, string $content, array $attachments = []): bool
    {
        try {
            Mail::send([], [], function ($message) use ($to, $subject, $content, $attachments) {
                $message->to($to)
                    ->subject($subject)
                    ->html($content);

                // Thêm file đính kèm nếu có
                if (!empty($attachments)) {
                    foreach ($attachments as $attachment) {
                        $message->attach($attachment);
                    }
                }
            });

            return true;
        } catch (Exception $e) {
            Log::error('Error sending email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gửi email với template blade
     *
     * @param string $to Email người nhận
     * @param string $subject Tiêu đề email
     * @param string $template Tên template blade
     * @param array $data Dữ liệu truyền vào template
     * @param array $attachments Danh sách file đính kèm
     * @return bool
     */
    public function sendMailWithTemplate(string $to, string $subject, string $template, array $data = [], array $attachments = []): bool
    {
        try {
            Mail::send($template, $data, function ($message) use ($to, $subject, $attachments) {
                $message->to($to)
                    ->subject($subject);

                // Thêm file đính kèm nếu có
                if (!empty($attachments)) {
                    foreach ($attachments as $attachment) {
                        $message->attach($attachment);
                    }
                }
            });

            return true;
        } catch (Exception $e) {
            Log::error('Error sending email with template: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gửi email với nhiều người nhận
     *
     * @param array $recipients Danh sách email người nhận
     * @param string $subject Tiêu đề email
     * @param string $content Nội dung email
     * @param array $attachments Danh sách file đính kèm
     * @return bool
     */
    public function sendBulkMail(array $recipients, string $subject, string $content, array $attachments = []): bool
    {
        try {
            foreach ($recipients as $recipient) {
                Mail::send([], [], function ($message) use ($recipient, $subject, $content, $attachments) {
                    $message->to($recipient)
                        ->subject($subject)
                        ->html($content);

                    if (!empty($attachments)) {
                        foreach ($attachments as $attachment) {
                            $message->attach($attachment);
                        }
                    }
                });
            }

            return true;
        } catch (Exception $e) {
            Log::error('Error sending bulk email: ' . $e->getMessage());
            return false;
        }
    }
}