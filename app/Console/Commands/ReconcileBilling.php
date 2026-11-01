<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\BillingPaymentService;
use App\Services\MidtransGateway;
use Illuminate\Console\Command;

class ReconcileBilling extends Command
{
    protected $signature = 'growpos:reconcile-billing {--limit=100}';

    protected $description = 'Reconcile existing subscription invoices, including paid invoices missing activation';

    public function handle(MidtransGateway $gateway, BillingPaymentService $payments): int
    {
        $invoices = Invoice::where(fn ($query) => $query->where('status', 'pending')
            ->orWhere(fn ($query) => $query->where('status', 'paid')->whereNull('subscription_id')))
            ->where('updated_at', '<', now()->subMinute())->oldest('updated_at')
            ->limit(max(1, min(500, (int) $this->option('limit'))))->get();
        $errors = 0;
        foreach ($invoices as $invoice) {
            try {
                $payments->apply($invoice->id, $gateway->status($invoice->invoice_number));
            } catch (\Throwable $exception) {
                $errors++;
                report($exception);
            }
            $invoice->touch();
        }
        $this->line('Checked '.$invoices->count().' billing references; '.$errors.' require another check.');

        return $errors ? self::FAILURE : self::SUCCESS;
    }
}
