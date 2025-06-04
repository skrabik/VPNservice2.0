<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Customers;

use App\Models\Customer;
use App\Orchid\Layouts\Customer\CustomerListLayout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;

class CustomerListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'customers' => Customer::defaultSort('id', 'desc')->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Customers';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'List of all customers';
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
            Link::make(__('Add'))
                ->icon('bs.plus-circle')
                ->route('platform.customers.create'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            CustomerListLayout::class,
        ];
    }
}
