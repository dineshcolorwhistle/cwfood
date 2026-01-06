<?php

namespace App\Observers;

use App\Models\Client_contact;
class ClientContactObserver
{
    public function updating(Client_contact $contact): void
    {
        // Skip flipping if we’re inside the Xero sync pipeline
        if (app()->bound('xero.syncing') && app('xero.syncing') === true) {
            return;
        }

        // If this row originated from Xero and user changes key fields, mark as Batchbase
        if ($contact->getOriginal('source') === 'Xero') {
            if ($contact->isDirty([
                'first_name', 'last_name', 'email', 'phone',
                'notes', 'contact_category', 'contact_tags', 'company'
            ])) {
                $contact->source = 'Batchbase';
            }
        }
    }
}