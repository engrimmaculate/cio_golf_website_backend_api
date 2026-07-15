<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Player;
use App\Models\WebhookLog;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    protected PaystackService $paystack;

    public function __construct(PaystackService $paystack)
    {
        $this->paystack = $paystack;
    }

    // Player initializes payment from dashboard (authenticated)
    public function initialize(Request $request)
    {
        $request->validate([
            'player_id' => 'required|exists:players,id',
        ]);

        $player = Player::with('tournament')->findOrFail($request->player_id);
        $amount = $player->tournament->registration_fee ?? config('services.paystack.registration_fee', 50000);

        // Check for existing completed payment
        $existing = Payment::where('player_id', $player->id)
            ->where('status', 'completed')
            ->first();
        if ($existing) {
            return response()->json(['message' => 'Payment already completed.', 'payment' => $existing], 400);
        }

        // Find or create pending payment
        $payment = Payment::where('player_id', $player->id)
            ->where('status', 'pending')
            ->where('token_expires_at', '>', now())
            ->first();

        if (!$payment) {
            $payment = Payment::create([
                'player_id' => $player->id,
                'user_id' => $player->user_id,
                'reference' => Payment::generateReference(),
                'amount' => $amount,
                'currency' => 'NGN',
                'status' => 'pending',
                'token' => Payment::generateToken(),
                'token_expires_at' => now()->addDays(7),
                'description' => 'CIO International Golf Classic 7th Edition - Registration Fee',
            ]);
        }

        $email = $player->email;
        $callbackUrl = config('services.paystack.callback_url', 'http://localhost:3000/payment/verify');

        $result = $this->paystack->initializeTransaction([
            'email' => $email,
            'amount' => $payment->amount * 100, // Paystack uses kobo
            'reference' => $payment->reference,
            'callback_url' => $callbackUrl . '?ref=' . $payment->reference,
            'metadata' => [
                'player_id' => $player->id,
                'payment_id' => $payment->id,
                'player_name' => $player->full_name,
                'edition' => '7th Edition',
            ],
        ]);

        if (isset($result['status']) && $result['status'] === true) {
            $payment->update([
                'paystack_reference' => $result['data']['reference'] ?? null,
                'metadata' => $result['data'] ?? null,
            ]);

            return response()->json([
                'message' => 'Payment initialized.',
                'authorization_url' => $result['data']['authorization_url'] ?? null,
                'reference' => $payment->reference,
                'payment' => $payment,
            ]);
        }

        return response()->json(['message' => 'Failed to initialize payment.', 'error' => $result], 400);
    }

    // Public payment initialization via token link
    public function initializeByToken(string $token)
    {
        $payment = Payment::with('player.tournament')->where('token', $token)->first();

        if (!$payment || !$payment->isTokenValid()) {
            return response()->json(['message' => 'Invalid or expired payment link.'], 404);
        }

        if ($payment->status === 'completed') {
            return response()->json(['message' => 'Payment already completed.', 'payment' => $payment]);
        }

        if (!$payment->player) {
            return response()->json(['message' => 'Player not found for this payment.'], 400);
        }

        // Use tournament fee if available, otherwise fall back to config
        $amount = ($payment->player->tournament->registration_fee ?? null)
            ?? config('services.paystack.registration_fee', 50000);

        if (!$amount || $amount <= 0) {
            return response()->json(['message' => 'Invalid registration fee.'], 400);
        }

        // Update payment amount in case it changed
        if ($payment->amount != $amount) {
            $payment->update(['amount' => $amount]);
        }

        $callbackUrl = config('services.paystack.callback_url', 'http://localhost:3000/payment/verify');

        $result = $this->paystack->initializeTransaction([
            'email' => $payment->player->email ?? 'player@ciogolf.com',
            'amount' => $payment->amount * 100,
            'reference' => $payment->reference,
            'callback_url' => $callbackUrl . '?ref=' . $payment->reference,
            'metadata' => [
                'player_id' => $payment->player_id,
                'payment_id' => $payment->id,
                'player_name' => $payment->player->full_name ?? '',
                'edition' => '7th Edition',
            ],
        ]);

        if (isset($result['status']) && $result['status'] === true) {
            $payment->update([
                'paystack_reference' => $result['data']['reference'] ?? null,
                'metadata' => $result['data'] ?? null,
            ]);

            return response()->json([
                'message' => 'Payment initialized.',
                'authorization_url' => $result['data']['authorization_url'] ?? null,
                'reference' => $payment->reference,
                'payment' => $payment,
                'player' => $payment->player,
                'edition' => '7th Edition',
                'tournament' => $payment->player->tournament,
            ]);
        }

        Log::error('Payment initialize failed', [
            'payment_id' => $payment->id,
            'token' => $token,
            'result' => $result,
            'amount' => $payment->amount,
            'email' => $payment->player->email ?? null,
        ]);

        return response()->json(['message' => 'Failed to initialize payment.', 'details' => $result['message'] ?? 'Unknown error'], 400);
    }

    // Verify payment after redirect
    public function verify(Request $request)
    {
        $request->validate(['reference' => 'required|string']);
        $reference = $request->reference;

        $result = $this->paystack->verifyTransaction($reference);

        $payment = Payment::where('reference', $reference)->first();
        if (!$payment) {
            return response()->json(['message' => 'Payment not found.'], 404);
        }

        if (isset($result['status']) && $result['status'] === true && $result['data']['status'] === 'success') {
            $payment->update([
                'status' => 'completed',
                'payment_method' => $result['data']['authorization']['card_type'] ?? null,
                'payment_channel' => $result['data']['authorization']['channel'] ?? null,
                'paystack_response' => $result['data'],
                'paid_at' => now(),
            ]);

            // Update player status
            if ($payment->player) {
                $payment->player->update(['status' => 'approved']);
            }

            return response()->json([
                'message' => 'Payment verified successfully.',
                'payment' => $payment->fresh(['player.tournament']),
            ]);
        }

        $payment->update([
            'status' => 'failed',
            'paystack_response' => $result['data'] ?? $result,
        ]);

        return response()->json(['message' => 'Payment verification failed.', 'payment' => $payment], 400);
    }

    // Get payment details by reference (public for receipt)
    public function getByReference(string $reference)
    {
        $payment = Payment::with(['player.tournament', 'user'])
            ->where('reference', $reference)
            ->first();

        if (!$payment) {
            return response()->json(['message' => 'Payment not found.'], 404);
        }

        return response()->json(['data' => $payment]);
    }

    // Player payment history
    public function myPayments(Request $request)
    {
        $player = $request->user()->player;
        if (!$player) {
            return response()->json(['data' => []]);
        }

        $payments = Payment::where('player_id', $player->id)
            ->with('player.tournament')
            ->latest()
            ->paginate($request->get('per_page', 15));

        return response()->json($payments);
    }

    // Admin: list all payments
    public function adminPayments(Request $request)
    {
        $query = Payment::with(['player', 'player.tournament', 'user']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhereHas('player', function ($q2) use ($search) {
                        $q2->where('full_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $payments = $query->latest()->paginate($request->get('per_page', 20));

        return response()->json($payments);
    }

    // Admin: payment stats
    public function adminStats()
    {
        $totalRevenue = Payment::where('status', 'completed')->sum('amount');
        $totalPayments = Payment::count();
        $completedPayments = Payment::where('status', 'completed')->count();
        $pendingPayments = Payment::where('status', 'pending')->count();

        return response()->json([
            'data' => [
                'total_revenue' => $totalRevenue,
                'total_payments' => $totalPayments,
                'completed_payments' => $completedPayments,
                'pending_payments' => $pendingPayments,
            ],
        ]);
    }

    // Admin: webhook logs
    public function webhookLogs(Request $request)
    {
        $query = WebhookLog::with('payment');

        if ($request->has('event')) {
            $query->where('event', $request->event);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('sample_only') && $request->boolean('sample_only')) {
            $query->where('is_sample', true);
        }

        if ($request->has('reference')) {
            $query->where('reference', $request->reference);
        }

        $logs = $query->latest()->paginate($request->get('per_page', 50));

        return response()->json($logs);
    }

    // Webhook handler
    public function webhook(Request $request)
    {
        $payload = $request->all();
        $event = $payload['event'] ?? 'unknown';
        $reference = $payload['data']['reference'] ?? null;

        $logData = [
            'event' => $event,
            'reference' => $reference,
            'payload' => $payload,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        if ($event === 'charge.success' && $reference) {
            $payment = Payment::where('reference', $reference)->first();

            if ($payment && $payment->status !== 'completed') {
                $payment->update([
                    'status' => 'completed',
                    'payment_method' => $payload['data']['authorization']['card_type'] ?? null,
                    'payment_channel' => $payload['data']['authorization']['channel'] ?? null,
                    'paystack_response' => $payload['data'],
                    'paid_at' => now(),
                ]);

                if ($payment->player) {
                    $payment->player->update(['status' => 'approved']);
                }

                // Log 1 out of every 50 successful transactions with full detail
                $totalCompleted = Payment::where('status', 'completed')->count();
                $isSample = ($totalCompleted % 50 === 0);

                WebhookLog::create(array_merge($logData, [
                    'payment_id' => $payment->id,
                    'status' => 'processed',
                    'message' => $isSample
                        ? "Sample log (#{$totalCompleted}): Full payload captured"
                        : "Payment completed for reference {$reference}",
                    'is_sample' => $isSample,
                ]));

                Log::info("Webhook processed: {$event} for {$reference}", [
                    'payment_id' => $payment->id,
                    'total_completed' => $totalCompleted,
                    'is_sample' => $isSample,
                ]);

                return response()->json(['status' => 'ok']);
            }

            // Already completed — still log it
            WebhookLog::create(array_merge($logData, [
                'payment_id' => $payment?->id,
                'status' => 'skipped',
                'message' => "Payment already completed or not found: {$reference}",
            ]));

            return response()->json(['status' => 'ok']);
        }

        // Non-success events or missing reference — log and accept
        WebhookLog::create(array_merge($logData, [
            'status' => 'received',
            'message' => "Event received: {$event}",
        ]));

        return response()->json(['status' => 'ok']);
    }
}
