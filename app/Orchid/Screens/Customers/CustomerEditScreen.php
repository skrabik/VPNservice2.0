<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Customers;

use App\Models\Customer;
use App\Orchid\Layouts\Customer\CustomerEditLayout;
use App\Orchid\Layouts\Customer\CustomerSubscriptionsLayout;
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
        ];
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
}
