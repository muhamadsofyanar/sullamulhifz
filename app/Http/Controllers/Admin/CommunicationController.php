<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CommunicationDelivery;
use App\Models\CommunicationTemplate;
use App\Models\IntegrationConnection;
use App\Services\Communication\CommunicationDriverFactory;
use App\Services\Communication\CommunicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class CommunicationController extends Controller
{
    public function __construct(
        private readonly CommunicationService $communications,
        private readonly CommunicationDriverFactory $drivers,
    ) {
    }

    public function index(Request $request): View
    {
        $institutionId = (int) $request->user()->institution_id;
        $connections = IntegrationConnection::query()
            ->where('institution_id', $institutionId)
            ->whereIn('provider', ['whatsapp', 'email'])
            ->orderBy('provider')
            ->get();

        $filters = $request->validate([
            'channel' => ['nullable', Rule::in(['whatsapp', 'email'])],
            'direction' => ['nullable', Rule::in(['outbound', 'inbound'])],
            'status' => ['nullable', Rule::in(['queued', 'sending', 'sent', 'delivered', 'received', 'failed'])],
        ]);

        $deliveries = CommunicationDelivery::query()
            ->with(['recipient:id,name'])
            ->where('institution_id', $institutionId)
            ->when($filters['channel'] ?? null, fn ($query, $value) => $query->where('channel', $value))
            ->when($filters['direction'] ?? null, fn ($query, $value) => $query->where('direction', $value))
            ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('status', $value))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $templates = CommunicationTemplate::query()
            ->where('institution_id', $institutionId)
            ->orderBy('event_key')
            ->orderBy('channel')
            ->get();

        $connectionState = $connections->mapWithKeys(fn (IntegrationConnection $connection): array => [
            $connection->provider => $this->communications->readiness($connection),
        ]);

        $stats = CommunicationDelivery::query()
            ->where('institution_id', $institutionId)
            ->where('created_at', '>=', now()->startOfMonth())
            ->selectRaw("SUM(CASE WHEN status IN ('sent','delivered') THEN 1 ELSE 0 END) AS successful")
            ->selectRaw("SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) AS failed")
            ->selectRaw("SUM(CASE WHEN direction = 'inbound' THEN 1 ELSE 0 END) AS inbound")
            ->first();

        return view('admin.communications.index', [
            'connections' => $connections->keyBy('provider'),
            'connectionState' => $connectionState,
            'driverCatalog' => [
                'whatsapp' => $this->drivers->catalog('whatsapp'),
                'email' => $this->drivers->catalog('email'),
            ],
            'deliveries' => $deliveries,
            'templates' => $templates,
            'stats' => $stats,
            'filters' => $filters,
        ]);
    }

    public function updateConnection(Request $request, IntegrationConnection $connection): RedirectResponse
    {
        $this->authorizeConnection($request, $connection);
        abort_unless(in_array($connection->provider, ['whatsapp', 'email'], true), 404);

        $catalog = array_keys($this->drivers->catalog($connection->provider));
        $data = $request->validate([
            'driver' => ['required', Rule::in($catalog)],
            'enabled' => ['nullable', 'boolean'],
            'event_liaison' => ['nullable', 'boolean'],
            'event_account_invitation' => ['nullable', 'boolean'],
            'event_password_reset' => ['nullable', 'boolean'],
        ]);

        $enabled = (bool) ($data['enabled'] ?? false);
        $configuration = array_merge($connection->configuration ?: [], [
            'driver' => $data['driver'],
            'events' => [
                'liaison' => (bool) ($data['event_liaison'] ?? false),
                'account_invitation' => (bool) ($data['event_account_invitation'] ?? false),
                'password_reset' => $connection->provider === 'email' && (bool) ($data['event_password_reset'] ?? false),
            ],
            'configured_at' => now()->toIso8601String(),
        ]);

        $connection->update([
            'status' => $enabled ? 'active' : 'disabled',
            'configuration' => $configuration,
            'activated_at' => $enabled ? ($connection->activated_at ?: now()) : null,
            'last_error' => null,
        ]);

        $state = $this->communications->readiness($connection->fresh());
        $this->log($request, $connection, 'communication_connection_updated', [
            'channel' => $connection->provider,
            'driver' => $data['driver'],
            'enabled' => $enabled,
            'ready' => $state['ready'],
        ]);

        $message = $enabled
            ? ($state['ready'] ? 'Integrasi diaktifkan dan konfigurasi dasar terdeteksi.' : 'Integrasi disimpan, tetapi Environment Variables belum lengkap: '.$state['message'])
            : 'Integrasi dinonaktifkan tanpa menghapus riwayat pengiriman.';

        return back()->with($state['ready'] || ! $enabled ? 'success' : 'error', $message);
    }

    public function test(Request $request, IntegrationConnection $connection): RedirectResponse
    {
        $this->authorizeConnection($request, $connection);
        $rules = $connection->provider === 'email'
            ? ['required', 'email:rfc', 'max:190']
            : ['required', 'string', 'max:40', 'regex:/^[+0-9() .-]+$/'];
        $data = $request->validate([
            'recipient' => $rules,
            'subject' => ['nullable', 'string', 'max:190'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $delivery = $this->communications->send(
                (int) $request->user()->institution_id,
                $connection->provider,
                $data['recipient'],
                $connection->provider === 'email' ? nl2br(e($data['message'])) : $data['message'],
                [
                    'event_key' => 'connection_test',
                    'subject' => $data['subject'] ?: 'Tes integrasi Sullamul Ḥifẓ',
                    'recipient_name' => 'Penerima tes',
                    'created_by_user_id' => $request->user()->id,
                    'metadata' => ['source' => 'admin_connection_test'],
                ],
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Tes pengiriman gagal. Periksa detail aman pada riwayat dan pastikan Environment Variables provider benar.');
        }

        return back()->with('success', 'Tes pengiriman diproses dengan status: '.$delivery->status.'.');
    }

    public function updateTemplate(Request $request, CommunicationTemplate $template): RedirectResponse
    {
        abort_unless((int) $template->institution_id === (int) $request->user()->institution_id, 404);
        $data = $request->validate([
            'subject' => ['nullable', 'string', 'max:190'],
            'content' => ['required', 'string', 'max:10000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $template->update([
            'subject' => $template->channel === 'email' ? ($data['subject'] ?? null) : null,
            'content' => $data['content'],
            'is_active' => (bool) ($data['is_active'] ?? false),
            'updated_by_user_id' => $request->user()->id,
        ]);

        $this->log($request, $template, 'communication_template_updated', [
            'channel' => $template->channel,
            'event_key' => $template->event_key,
            'is_active' => $template->is_active,
        ]);

        return back()->with('success', 'Template komunikasi diperbarui.');
    }

    public function retry(Request $request, CommunicationDelivery $delivery): RedirectResponse
    {
        abort_unless((int) $delivery->institution_id === (int) $request->user()->institution_id, 404);

        try {
            $delivery = $this->communications->retry($delivery);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Pengiriman ulang gagal. Periksa detail aman pada riwayat.');
        }

        return back()->with('success', 'Pengiriman ulang diproses dengan status: '.$delivery->status.'.');
    }

    private function authorizeConnection(Request $request, IntegrationConnection $connection): void
    {
        abort_unless((int) $connection->institution_id === (int) $request->user()->institution_id, 404);
    }

    private function log(Request $request, object $subject, string $action, array $newValues): void
    {
        ActivityLog::create([
            'institution_id' => $request->user()->institution_id,
            'user_id' => $request->user()->id,
            'action' => $action,
            'subject_type' => $subject::class,
            'subject_id' => $subject->id,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
    }
}
