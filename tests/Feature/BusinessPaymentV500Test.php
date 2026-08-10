<?php

namespace Tests\Feature;

/** @phase 5.0 Business, Payment & Integrations */

use App\Models\BillingPlan;
use App\Models\BillingSubscription;
use App\Models\PaymentTransaction;
use App\Models\Role;
use App\Models\User;
use App\Services\BusinessBillingService;
use Database\Seeders\ProductionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class BusinessPaymentV500Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        putenv('INITIAL_ADMIN_PASSWORD=TestAdmin2026Secure');
        putenv('SEED_INITIAL_TPA_DATA=false');
        $this->seed(ProductionSeeder::class);
    }

    public function test_paid_presets_are_not_accidentally_activated_without_real_pricing(): void
    {
        $this->assertDatabaseHas('billing_plans', ['code' => 'personal-free', 'status' => 'active', 'price' => 0]);
        foreach (['personal-guided', 'ustadz-pro', 'institution-core'] as $code) {
            $this->assertDatabaseHas('billing_plans', ['code' => $code, 'status' => 'inactive', 'price' => 0]);
        }
    }

    public function test_personal_can_activate_free_plan_and_create_paid_invoice(): void
    {
        $personal = $this->registerPersonal('billing-personal');
        $free = BillingPlan::where('code', 'personal-free')->firstOrFail();
        $paid = BillingPlan::where('code', 'personal-guided')->firstOrFail();
        $paid->update(['status' => 'active', 'price' => 99000]);

        $this->actingAs($personal)->get(route('business.index'))
            ->assertOk()->assertSee('Paket dan layanan dalam satu tempat');

        $this->actingAs($personal)->post(route('business.subscribe', $free))->assertRedirect();
        $this->assertDatabaseHas('billing_subscriptions', [
            'user_id' => $personal->id, 'billing_plan_id' => $free->id, 'status' => 'active',
        ]);
        $this->assertDatabaseHas('billing_invoices', [
            'user_id' => $personal->id, 'billing_plan_id' => $free->id, 'status' => 'paid',
        ]);

        $this->actingAs($personal)->post(route('business.subscribe', $paid))->assertRedirect();
        $this->assertDatabaseHas('billing_invoices', [
            'user_id' => $personal->id, 'billing_plan_id' => $paid->id, 'status' => 'pending',
        ]);
        $this->assertDatabaseHas('payment_transactions', [
            'user_id' => $personal->id, 'purpose' => 'subscription', 'status' => 'pending',
        ]);
    }

    public function test_institution_admin_can_create_institution_subscription_invoice_from_shared_portal(): void
    {
        $plan = BillingPlan::where('code', 'institution-core')->firstOrFail();
        $plan->update(['status' => 'active', 'price' => 499000]);
        $admin = User::query()->whereHas('roles', fn ($query) => $query->where('roles.name', 'institution_admin'))->firstOrFail();
        $admin->update(['must_change_password' => false]);

        $this->actingAs($admin)->get(route('business.index'))->assertOk()->assertSee('Lembaga Core');
        $this->actingAs($admin)->post(route('business.subscribe', $plan))->assertRedirect();

        $this->assertDatabaseHas('billing_subscriptions', [
            'institution_id' => $admin->institution_id,
            'user_id' => $admin->id,
            'billing_plan_id' => $plan->id,
            'scope_type' => 'institution',
            'status' => 'pending',
        ]);
    }

    public function test_two_admins_in_same_workspace_reuse_one_pending_institution_invoice(): void
    {
        $plan = BillingPlan::where('code', 'institution-core')->firstOrFail();
        $plan->update(['status' => 'active', 'price' => 499000]);
        $firstAdmin = User::query()->whereHas('roles', fn ($query) => $query->where('roles.name', 'institution_admin'))->firstOrFail();
        $role = Role::query()->where('name', 'institution_admin')->firstOrFail();
        $secondAdmin = User::create([
            'institution_id' => $firstAdmin->institution_id,
            'name' => 'Admin Kedua',
            'email' => 'admin-kedua@example.test',
            'password' => 'RahasiaAman123',
            'status' => 'active',
            'must_change_password' => false,
        ]);
        $secondAdmin->roles()->attach($role->id, [
            'institution_id' => $firstAdmin->institution_id,
            'status' => 'active',
        ]);

        $billing = app(BusinessBillingService::class);
        $firstInvoice = $billing->createSubscriptionInvoice($firstAdmin, $plan);
        $secondInvoice = $billing->createSubscriptionInvoice($secondAdmin, $plan);

        $this->assertSame($firstInvoice->id, $secondInvoice->id);
        $this->assertSame(1, BillingSubscription::query()
            ->where('institution_id', $firstAdmin->institution_id)
            ->where('billing_plan_id', $plan->id)
            ->where('scope_type', 'institution')
            ->whereIn('status', ['pending', 'active'])
            ->count());
    }

    public function test_superadmin_can_verify_cross_workspace_payment_and_activate_subscription(): void
    {
        $personal = $this->registerPersonal('billing-cross-workspace');
        $paid = BillingPlan::where('code', 'personal-guided')->firstOrFail();
        $paid->update(['status' => 'active', 'price' => 99000]);
        $this->actingAs($personal)->post(route('business.subscribe', $paid))->assertRedirect();
        $transaction = PaymentTransaction::query()->where('user_id', $personal->id)->where('status', 'pending')->firstOrFail();

        $superadmin = User::query()->whereHas('roles', fn ($query) => $query->where('roles.name', 'institution_admin'))->firstOrFail();
        $superRole = Role::query()->where('name', 'superadmin')->firstOrFail();
        $superadmin->roles()->syncWithoutDetaching([
            $superRole->id => ['institution_id' => $superadmin->institution_id, 'status' => 'active'],
        ]);
        $superadmin->update(['must_change_password' => false]);
        $this->actingAs($superadmin)->put(route('admin.business.payments.update', $transaction), ['decision' => 'paid'])->assertRedirect();

        $this->assertSame('paid', $transaction->fresh()->status);
        $this->assertSame('paid', $transaction->fresh()->billingInvoice->status);
        $this->assertSame('active', BillingSubscription::query()->where('user_id', $personal->id)->where('billing_plan_id', $paid->id)->firstOrFail()->status);
    }

    private function registerPersonal(string $suffix): User
    {
        Auth::logout();
        $this->post(route('personal.register.store'), [
            'name' => 'Personal '.ucfirst($suffix),
            'email' => $suffix.'@example.test',
            'password' => 'RahasiaAman123',
            'password_confirmation' => 'RahasiaAman123',
            'age_group' => 'adult',
            'learning_mode' => 'self',
            'terms' => '1',
        ])->assertRedirect(route('personal.dashboard'));

        return User::query()->where('email', $suffix.'@example.test')->firstOrFail();
    }
}
