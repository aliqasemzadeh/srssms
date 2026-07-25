<?php

namespace App\Http\Controllers;

use App\Enums\DepositStatusEnum;
use App\Models\Finance\Deposit;
use App\Support\PaymentGateways;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;
use Throwable;

class PaymentController extends Controller
{
    public function pay(Deposit $deposit): View|RedirectResponse
    {
        abort_unless(Auth::check() && $deposit->user_id === Auth::id(), 403);

        $driver = PaymentGateways::driverFromMethod((string) $deposit->method);

        if ($driver === null || ! in_array($driver, PaymentGateways::enabledIranianDrivers(), true)) {
            return redirect()
                ->route('panels.user.wallet.index')
                ->with('payment_status', 'failed')
                ->with('payment_message', __('general.no_enabled_gateways'));
        }

        if (! in_array($deposit->status, [
            DepositStatusEnum::Pending,
            DepositStatusEnum::Processing,
        ], true)) {
            return redirect()
                ->route('panels.user.wallet.index')
                ->with('payment_status', 'failed')
                ->with('payment_message', __('general.payment_already_processed'));
        }

        $deposit->loadMissing('wallet.currency');

        $gatewayAmount = (int) ($deposit->meta['gateway_amount'] ?? 0);

        if ($gatewayAmount < 1) {
            $gatewayAmount = PaymentGateways::toGatewayAmount(
                $deposit->amount,
                $driver,
                $deposit->wallet?->currency?->symbol
            );
        }

        try {
            $invoice = (new Invoice)->amount($gatewayAmount);

            $redirection = Payment::via($driver)
                ->callbackUrl(route('payment.callback', $deposit))
                ->purchase($invoice, function ($paymentDriver, string $transactionId) use ($deposit, $driver, $gatewayAmount): void {
                    $meta = is_array($deposit->meta) ? $deposit->meta : [];
                    $meta['driver'] = $driver;
                    $meta['gateway_amount'] = $gatewayAmount;
                    $meta['authority'] = $transactionId;

                    $deposit->forceFill([
                        'tracking_code' => $transactionId,
                        'status' => DepositStatusEnum::Processing,
                        'meta' => $meta,
                    ])->save();
                })
                ->pay();

            $action = $redirection->getAction();

            if (blank($action)) {
                return redirect()
                    ->route('panels.user.wallet.index')
                    ->with('payment_status', 'failed')
                    ->with('payment_message', __('general.payment_failed'));
            }

            return view('shetabitPayment::redirectForm', [
                'action' => $action,
                'inputs' => $redirection->getInputs(),
                'method' => strtoupper($redirection->getMethod() ?: 'GET'),
            ]);
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('panels.user.wallet.index')
                ->with('payment_status', 'failed')
                ->with('payment_message', $e->getMessage() ?: __('general.payment_failed'));
        }
    }

    public function callback(Request $request, Deposit $deposit): RedirectResponse
    {
        $driver = PaymentGateways::driverFromMethod((string) $deposit->method)
            ?? (is_array($deposit->meta) ? ($deposit->meta['driver'] ?? null) : null);

        if ($driver === null) {
            return $this->redirectWithStatus('failed', __('general.payment_failed'));
        }

        if ($deposit->status === DepositStatusEnum::Approved) {
            return $this->redirectWithStatus('success', __('general.payment_success'));
        }

        if (in_array($deposit->status, [
            DepositStatusEnum::Canceled,
            DepositStatusEnum::Rejected,
            DepositStatusEnum::Completed,
        ], true)) {
            return $this->redirectWithStatus('failed', __('general.payment_already_processed'));
        }

        $authority = $deposit->meta['authority']
            ?? $deposit->tracking_code
            ?? $request->input('Authority')
            ?? $request->input('authority')
            ?? $request->input('token');

        $gatewayAmount = (int) ($deposit->meta['gateway_amount'] ?? 0);

        if ($gatewayAmount < 1 || blank($authority)) {
            $this->markCanceled($deposit, __('general.payment_failed'));

            return $this->redirectWithStatus('failed', __('general.payment_failed'));
        }

        try {
            $receipt = Payment::via($driver)
                ->amount($gatewayAmount)
                ->transactionId((string) $authority)
                ->verify();

            DB::transaction(function () use ($deposit, $receipt, $driver, $gatewayAmount): void {
                $locked = Deposit::query()->lockForUpdate()->find($deposit->id);

                if (! $locked || $locked->status === DepositStatusEnum::Approved) {
                    return;
                }

                $meta = is_array($locked->meta) ? $locked->meta : [];
                $meta['driver'] = $driver;
                $meta['gateway_amount'] = $gatewayAmount;
                $meta['authority'] = $meta['authority'] ?? $locked->tracking_code;
                $meta['reference_id'] = $receipt->getReferenceId();
                $meta['verified_at'] = now()->toIso8601String();

                $locked->forceFill([
                    'tracking_code' => (string) $receipt->getReferenceId(),
                    'status' => DepositStatusEnum::Approved,
                    'meta' => $meta,
                ])->save();
            });

            return $this->redirectWithStatus('success', __('general.payment_success'));
        } catch (InvalidPaymentException $e) {
            $this->markCanceled($deposit, $e->getMessage());

            return $this->redirectWithStatus(
                'failed',
                $e->getMessage() ?: __('general.payment_failed')
            );
        } catch (Throwable $e) {
            report($e);
            $this->markCanceled($deposit, $e->getMessage());

            return $this->redirectWithStatus('failed', __('general.payment_failed'));
        }
    }

    protected function markCanceled(Deposit $deposit, ?string $message = null): void
    {
        if (in_array($deposit->status, [
            DepositStatusEnum::Approved,
            DepositStatusEnum::Canceled,
            DepositStatusEnum::Rejected,
        ], true)) {
            return;
        }

        $meta = is_array($deposit->meta) ? $deposit->meta : [];

        if (filled($message)) {
            $meta['verify_error'] = $message;
        }

        $deposit->forceFill([
            'status' => DepositStatusEnum::Canceled,
            'meta' => $meta,
        ])->save();
    }

    protected function redirectWithStatus(string $status, string $message): RedirectResponse
    {
        return redirect()
            ->route('panels.user.wallet.index')
            ->with('payment_status', $status)
            ->with('payment_message', $message);
    }
}
