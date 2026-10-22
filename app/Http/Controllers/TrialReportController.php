<?php

namespace App\Http\Controllers;

use App\Models\Progress;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrialReportController extends Controller
{
    public function show(Request $request, Progress $progress): View
    {
        abort_unless($progress->activity === 'Trial', 404, 'Laporan trial hanya tersedia untuk kegiatan Trial.');

        $project = $progress->project;

        // If tonage / qty_ok / qty_ng provided via request parameters, update model
        if ($request->filled('tonage') || $request->filled('qty_ok') || $request->filled('qty_ng')) {
            $progress->update([
                'tonage_raw' => $request->query('tonage', $progress->tonage_raw),
                'qty_ok_raw' => $request->query('qty_ok', $progress->qty_ok_raw),
                'qty_ng_raw' => $request->query('qty_ng', $progress->qty_ng_raw),
            ]);
        }

        $tonage = $progress->tonage_raw ?? '';
        $qtyOk = $progress->qty_ok_raw ?? '';
        $qtyNg = $progress->qty_ng_raw ?? '';
        $mcNo = $progress->machine ?? '';
        $dh = $progress->die_high_raw ?? '';
        $proses = $progress->process_trial ?: ($project?->process ?? '');

        $problems = $progress->problems()->with('correctiveActions')->get();
        $items = [];

        if ($problems->isNotEmpty()) {
            $rowNo = 1;
            foreach ($problems as $problem) {
                $cas = $problem->correctiveActions;
                if ($cas->isNotEmpty()) {
                    foreach ($cas as $ca) {
                        $items[] = [
                            'no' => $rowNo++,
                            'proses' => $proses,
                            'mc_no' => $mcNo,
                            'tonage' => $tonage,
                            'dh' => $dh,
                            'qty_ok' => $qtyOk,
                            'qty_ng' => $qtyNg,
                            'problem' => $problem->description ?? '',
                            'corrective_action' => $ca->description ?? '',
                            'pic' => $ca->pic ?? '',
                            'date' => $ca->actual_date ? $ca->actual_date->format('d/m/Y') : ($progress->activity_date ? $progress->activity_date->format('d/m/Y') : ''),
                            'keterangan' => $progress->remark ?: ($progress->event_note ?? ''),
                        ];
                    }
                } else {
                    $items[] = [
                        'no' => $rowNo++,
                        'proses' => $proses,
                        'mc_no' => $mcNo,
                        'tonage' => $tonage,
                        'dh' => $dh,
                        'qty_ok' => $qtyOk,
                        'qty_ng' => $qtyNg,
                        'problem' => $problem->description ?? '',
                        'corrective_action' => '',
                        'pic' => '',
                        'date' => $progress->activity_date ? $progress->activity_date->format('d/m/Y') : '',
                        'keterangan' => $progress->remark ?: ($progress->event_note ?? ''),
                    ];
                }
            }
        } else {
            $items[] = [
                'no' => 1,
                'proses' => $proses,
                'mc_no' => $mcNo,
                'tonage' => $tonage,
                'dh' => $dh,
                'qty_ok' => $qtyOk,
                'qty_ng' => $qtyNg,
                'problem' => '',
                'corrective_action' => '',
                'pic' => '',
                'date' => $progress->activity_date ? $progress->activity_date->format('d/m/Y') : '',
                'keterangan' => $progress->remark ?: ($progress->event_note ?? ''),
            ];
        }

        $report = (object) [
            'customer' => $project?->customer ?? '',
            'part_no' => $project?->part_no ?? '',
            'part_name' => $project?->part_name ?? '',
            'model' => $project?->model ?? '',
            'material' => $progress->material_actual_trial ?: ($project?->material ?? ''),
            'qty_material' => $progress->qty_material_raw ?: ($project?->qty ?? ''),
            'trial_date' => $progress->activity_date ? $progress->activity_date->format('d/m/Y') : '',
            'items' => $items,
        ];

        return view('print.trial-report', compact('report'));
    }
}
