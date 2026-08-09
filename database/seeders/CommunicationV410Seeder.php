<?php

namespace Database\Seeders;

use App\Models\CommunicationTemplate;
use App\Models\Institution;
use App\Models\IntegrationConnection;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class CommunicationV410Seeder extends Seeder
{
    public function run(): void
    {
        $permission = Permission::updateOrCreate(
            ['name' => 'integrations.manage'],
            ['display_name' => 'Mengelola integrasi eksternal'],
        );

        foreach (Role::query()->whereIn('name', ['superadmin', 'institution_admin'])->get() as $role) {
            $role->permissions()->syncWithoutDetaching([$permission->id]);
        }

        if (! Schema::hasTable('communication_templates')) {
            return;
        }

        foreach (Institution::query()->where('status', 'active')->get() as $institution) {
            $this->connections((int) $institution->id);
            $this->templates((int) $institution->id);
        }
    }

    private function connections(int $institutionId): void
    {
        $defaults = [
            'whatsapp' => ['display_name' => 'WhatsApp Notifikasi Keluarga', 'driver' => 'starsender'],
            'email' => ['display_name' => 'Email Transaksional', 'driver' => 'smtp'],
        ];

        foreach ($defaults as $channel => $default) {
            $connection = IntegrationConnection::firstOrCreate(
                ['institution_id' => $institutionId, 'provider' => $channel],
                ['display_name' => $default['display_name'], 'status' => 'disabled', 'configuration' => []],
            );

            $configuration = $connection->configuration ?: [];
            if (! data_get($configuration, 'driver')) {
                $configuration['driver'] = $default['driver'];
            }
            if (! array_key_exists('events', $configuration)) {
                $configuration['events'] = ['liaison' => true, 'account_invitation' => true, 'password_reset' => $channel === 'email'];
            }
            $connection->update([
                'display_name' => $default['display_name'],
                'configuration' => $configuration,
            ]);
        }
    }

    private function templates(int $institutionId): void
    {
        $templates = [
            ['whatsapp', 'liaison', 'Notifikasi Buku Penghubung', null,
                "Assalamu’alaikum, {{recipient_name}}.\n\nAda pesan baru dari {{sender_name}} pada Buku Penghubung {{student_name}} tentang “{{subject}}”. Demi privasi, isi pesan tidak ditampilkan di WhatsApp.\n\nBuka portal: {{portal_url}}\n\nSullamul Ḥifẓ"],
            ['email', 'liaison', 'Notifikasi Buku Penghubung', 'Pesan baru Buku Penghubung — {{student_name}}',
                '<p>Assalamu’alaikum, {{recipient_name}}.</p><p>Ada pesan baru dari <strong>{{sender_name}}</strong> pada Buku Penghubung {{student_name}} tentang “{{subject}}”. Demi privasi, isi pesan tidak disalin ke email.</p><p><a href="{{portal_url}}">Buka Buku Penghubung</a></p><p>Sullamul Ḥifẓ</p>'],
            ['whatsapp', 'account_invitation', 'Aktivasi Akun', null,
                "Assalamu’alaikum, {{recipient_name}}.\n\nAkun Sullamul Ḥifẓ Anda telah disiapkan. Aktifkan dalam {{expires_hours}} jam melalui tautan pribadi berikut:\n{{activation_url}}\n\nJangan teruskan tautan ini kepada orang lain."],
            ['email', 'account_invitation', 'Aktivasi Akun', 'Aktivasi akun Sullamul Ḥifẓ',
                '<p>Assalamu’alaikum, {{recipient_name}}.</p><p>Akun Sullamul Ḥifẓ Anda telah disiapkan. Tautan berikut berlaku selama {{expires_hours}} jam.</p><p><a href="{{activation_url}}">Aktifkan akun</a></p><p>Jangan teruskan tautan ini kepada orang lain.</p>'],
            ['email', 'password_reset', 'Atur Ulang Kata Sandi', 'Atur ulang kata sandi Sullamul Ḥifẓ',
                '<p>Assalamu’alaikum, {{recipient_name}}.</p><p>Kami menerima permintaan pengaturan ulang kata sandi akun Anda. Tautan ini berlaku selama {{expires_minutes}} menit.</p><p><a href="{{reset_url}}">Atur ulang kata sandi</a></p><p>Abaikan email ini jika Anda tidak membuat permintaan tersebut.</p>'],
        ];

        foreach ($templates as [$channel, $event, $name, $subject, $content]) {
            CommunicationTemplate::firstOrCreate(
                ['institution_id' => $institutionId, 'channel' => $channel, 'event_key' => $event],
                [
                    'name' => $name,
                    'subject' => $subject,
                    'content' => $content,
                    'available_variables' => match ($event) {
                        'liaison' => ['recipient_name', 'student_name', 'sender_name', 'subject', 'portal_url'],
                        'password_reset' => ['recipient_name', 'reset_url', 'expires_minutes'],
                        default => ['recipient_name', 'activation_url', 'expires_hours'],
                    },
                    'is_active' => true,
                ],
            );
        }
    }
}
