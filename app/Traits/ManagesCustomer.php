<?php

namespace App\Traits;

use App\Models\User;
use Binkode\Paystack\Support\Customer;
use Filament\Notifications\Notification;

trait ManagesCustomer
{
    /**
     * Determine if the customer has a Paystack customer ID.
     */
    public function hasPaystackId(): bool
    {
        return ! is_null($this->paystackId());
    }

    /**
     * Get the customer's Paystack code.
     */
    public function paystackId()
    {
        return $this->customer_code;
    }

    /**
     * Get the first name that should be synced to Paystack.
     */
    public function paystackFirstName()
    {
        return explode(' ', $this->name)[0] ?? $this->name;
    }

    /**
     * Get the middle name that should be synced to Paystack.
     */
    public function paystackMiddleName()
    {
        $parts = explode(' ', $this->name);

        return count($parts) > 2 ? implode(' ', array_slice($parts, 1, -1)) : '';
    }

    /**
     * Get the last name that should be synced to Paystack.
     */
    public function paystackLastName()
    {
        $parts = explode(' ', $this->name);

        return $parts[count($parts) - 1] ?? '';
    }

    /**
     * Get the email that should be synced to Paystack.
     */
    public function paystackEmail()
    {
        return $this->email ?? null;
    }

    /**
     * Get the phone that should be synced to Paystack.
     */
    public function paystackPhone()
    {
        return $this->phone ?? null;
    }

    /**
     * Check if the user has a valid Paystack customer code.
     */
    public function assertCustomerExists(): bool
    {
        return $this->hasPaystackId();
    }

    /**
     * Check if the user has a valid Paystack auth code.
     */
    public function isAuthorized(): bool
    {
        return ! is_null($this->auth['authorization_code'] ?? null);
    }

    /**
     * Get the valid Paystack auth code.
     */
    public function getAuth()
    {
        return $this->auth['authorization_code'];
    }

    /**
     * Create a customer in Paystack if none exists.
     */
    public function createAsPaystackCustomer(): ?User
    {
        if ($this->assertCustomerExists()) {
            return $this;
        }

        $options = [
            'email' => $this->paystackEmail(),
            'first_name' => $this->paystackFirstName(),
            'last_name' => $this->paystackLastName(),
            'phone' => $this->paystackPhone(),
        ];

        $res = rescue(
            fn () => Customer::create($options),
            ['status' => false]
        );

        if ($res['status']) {
            $this->update(['customer_code' => $res['data']['customer_code']]);

            return $this;
        }

        Notification::make('error')
            ->title('An Error Occurred')
            ->body('Failed to create customer.')
            ->danger()
            ->send();

        return null;
    }

    /**
     * Create or sync the customer's details with Paystack.
     */
    public function syncPaystackCustomerDetails(): ?User
    {
        // Ensure the customer has a valid Paystack customer code
        $this->createAsPaystackCustomer();

        // Prepare the payload with updated customer information
        $options = [
            'email' => $this->paystackEmail(),
            'first_name' => $this->paystackFirstName(),
            'last_name' => $this->paystackLastName(),
            'phone' => $this->paystackPhone(),
        ];

        // Update the customer on Paystack
        $res = rescue(
            fn () => Customer::update($this->customer_code, $options),
            ['status' => false]
        );

        if (! $res['status']) {
            Notification::make('error')
                ->title('Error Occurred')
                ->body('Failed to sync customer details with Paystack.')
                ->danger()
                ->send();

            return null;
        }

        return $this;
    }
}
