<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MemberExportController extends Controller
{
    public function __invoke(Request $request, Team $team): StreamedResponse
    {
        $members = $team->memberships()
            ->with('plan')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->value();
                $query->where(function ($q) use ($search) {
                    $q->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status')->value());
            })
            ->when($request->filled('plan'), function ($query) use ($request) {
                $query->where('membership_plan_id', $request->integer('plan'));
            })
            ->latest();

        return response()->streamDownload(function () use ($members) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Name',
                'Email',
                'Phone',
                'Plan',
                'Status',
                'Start Date',
                'End Date',
                'Access Code',
                'Joined Date',
            ]);

            $members->chunk(200, function ($chunk) use ($handle) {
                foreach ($chunk as $membership) {
                    fputcsv($handle, [
                        $membership->customer_name,
                        $membership->email,
                        $membership->customer_phone,
                        $membership->plan?->name,
                        $membership->status->value,
                        $membership->starts_at?->toDateString(),
                        $membership->ends_at?->toDateString(),
                        $membership->access_code,
                        $membership->created_at->toDateString(),
                    ]);
                }
            });

            fclose($handle);
        }, 'members-export.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
