<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only('name', 'email', 'subject', 'message');

        try {
            Mail::raw(
                "Name: {$data['name']}\nEmail: {$data['email']}\n\nMessage:\n{$data['message']}",
                function ($message) use ($data) {
                    $message->to(config('mail.contact_email', 'info@ciogolfclassic.com'))
                        ->subject("Contact Form: {$data['subject']}")
                        ->replyTo($data['email'], $data['name']);
                }
            );

            return response()->json(['message' => 'Message sent successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to send message. Please try again later.'
            ], 500);
        }
    }
}
