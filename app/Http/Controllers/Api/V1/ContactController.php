<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Mail\ContactAdminMail;
use Exception;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Handle the incoming contact message.
     */
    public function sendMessage(ContactRequest $request)
    {
        try {
            $data = $request->validated();

            // Get admin email from .env, or fallback to a default
            $adminEmail = env('ADMIN_EMAIL', 'kaziomar.bdcalling@gmail.com');

            // Queue the email so the API response is not delayed
            Mail::to($adminEmail)->queue(new ContactAdminMail($data));

            return response_success('Message sent successfully. Our team will contact you shortly.', [], 200);
        } catch (Exception $e) {
            return response_error('Failed to send message.', ['error' => $e->getMessage()], 500);
        }
    }
}
