<?php

namespace App\Services;

use App\Models\RegisterDirectly;
use App\Models\Registration;
use Carbon\Carbon;

class RegistrationService
{
    public function createRegistrationDirectly(Registration $registration): void
    {
        $startDate = Carbon::parse($registration->start_date, 'Asia/Ho_Chi_Minh');
        $endDate = Carbon::parse($registration->end_date, 'Asia/Ho_Chi_Minh');

        $customers = $registration->customers;

        $currentDate = $startDate->copy()->startOfDay();

        while ($currentDate->lte($endDate->endOfDay())) {
            $startOfDay = $currentDate->isSameDay($startDate) ? $startDate->copy() : $currentDate->copy()->startOfDay();
            $endOfDay = $currentDate->isSameDay($endDate) ? $endDate->copy() : $currentDate->copy()->endOfDay();

            if ($customers->count() == 0) {
                RegisterDirectly::create([
                    'name' => $registration->name,
                    'papers' => '',
                    'address' => '',
                    'bks' => $registration->bks ?? '',
                    'contact_person' => '',
                    'job' => $registration->purpose,
                    'start_date' => $startOfDay,
                    'end_date' => $endOfDay,
                    'type' => 'passenger',
                    'areas' => '',
                    'status' => 'none',
                ]);
            } elseif ($customers->count() == 1) {
                $customer = $customers->first();
                RegisterDirectly::create([
                    'name' => $customer->name . '|' . $registration->name,
                    'papers' => $customer->papers,
                    'address' => '',
                    'bks' => $customer->license_plate ?: ($registration->bks ?? ''),
                    'contact_person' => '',
                    'job' => $registration->purpose,
                    'start_date' => $startOfDay,
                    'end_date' => $endOfDay,
                    'type' => 'passenger',
                    'areas' => $customer->areas,
                    'status' => 'none',
                ]);
            } else {
                foreach ($customers as $customer) {
                    RegisterDirectly::create([
                        'name' => $customer->name . '|' . $registration->name,
                        'papers' => $customer->papers,
                        'address' => '',
                        'bks' => $customer->license_plate ?: ($registration->bks ?? ''),
                        'contact_person' => '',
                        'job' => $registration->purpose,
                        'start_date' => $startOfDay,
                        'end_date' => $endOfDay,
                        'type' => 'passenger',
                        'areas' => $customer->areas,
                        'status' => 'none',
                    ]);
                }
            }

            $currentDate->addDay()->startOfDay();
        }
    }
}
