<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Customers;

use App\Models\Customer;
use App\Models\Subscription;
use App\Orchid\Layouts\Customer\CustomerEditLayout;
use App\Orchid\Layouts\Customer\CustomerSubscriptionEditModalLayout;
use App\Orchid\Layouts\Customer\CustomerSubscriptionsLayout;
use App\Orchid\Layouts\Customer\CustomerVpnKeysLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class CustomerEditScreen extends Screen
{
    /**
     * @var Customer
     */
    public $customer;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Customer $customer): iterable
    {
        return [
            'customer' => $customer,
            'subscriptions' => $customer->exists ? $customer->subscriptions()->with('plan')->get() : collect(),
            'vpnKeys' => $customer->exists
                ? $customer->vpnKeys()->withTrashed()->with(['server', 'inbound'])->orderByDesc('id')->get()
                : collect(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return $this->customer->exists ? 'Edit Customer' : 'Create Customer';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Customer Information';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.customers',
        ];
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make(__('Delete'))
                ->icon('bs.trash3')
                ->confirm(__('Are you sure you want to delete this customer?'))
                ->method('remove')
                ->novalidate()
                ->canSee($this->customer->exists),

            Button::make(__('Save'))
                ->icon('bs.check-circle')
                ->method('save'),
        ];
    }

    /**
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::block(CustomerEditLayout::class)
                ->title(__('Customer Information'))
                ->description(__('Update customer information.'))
                ->commands(
                    Button::make(__('Save'))
                        ->type(Color::BASIC)
                        ->icon('bs.check-circle')
                        ->method('save')
                ),
            Layout::block(CustomerSubscriptionsLayout::class)
                ->title(__('Subscriptions'))
                ->description(__('List of customer subscriptions.'))
                ->canSee($this->customer->exists),

            Layout::block(CustomerVpnKeysLayout::class)
                ->title(__('VPN Keys'))
                ->description(__('List of customer VPN keys, including soft-deleted ones.'))
                ->canSee($this->customer->exists),

            Layout::modal('editSubscriptionModal', CustomerSubscriptionEditModalLayout::class)
                ->deferred('loadSubscriptionOnOpenModal')
                ->canSee($this->customer->exists),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function loadSubscriptionOnOpenModal(Subscription $subscription): iterable
    {
        if ($this->customer->exists && $subscription->customer_id !== $this->customer->id) {
            abort(404);
        }

        return [
            'subscription' => $subscription,
        ];
    }

    public function saveSubscription(Customer $customer, Request $request, Subscription $subscription): void
    {
        if ($subscription->customer_id !== $customer->id) {
            abort(404);
        }

        $validated = $request->validate([
            'subscription.date_start' => ['required', 'date'],
            'subscription.date_end' => ['required', 'date', 'after_or_equal:subscription.date_start'],
        ]);

        $subscription->fill([
            'date_start' => $validated['subscription']['date_start'],
            'date_end' => $validated['subscription']['date_end'],
            'expiry_reminder_sent_at' => null,
        ])->save();

        Toast::info(__('Subscription was updated.'));
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Customer $customer, Request $request)
    {
        $customer->fill($request->get('customer'))->save();

        Toast::info(__('Customer was saved.'));

        return redirect()->route('platform.customers');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(Customer $customer)
    {
        $customer->delete();

        Toast::info(__('Customer was removed'));

        return redirect()->route('platform.customers');
    }

    /**
     * @return void
     */
    public function deleteSubscription(Customer $customer, Request $request)
    {
        $subscription_id = (int) $request->input('subscription_id');
        $subscription = Subscription::query()
            ->where('id', $subscription_id)
            ->where('customer_id', $customer->id)
            ->first();

        if (! $subscription) {
            Toast::warning(__('Subscription not found.'));

            return;
        }

        $subscription->delete();
        Toast::info(__('Subscription was deleted.'));
    }
}
