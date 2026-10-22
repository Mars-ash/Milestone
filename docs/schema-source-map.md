# Review pemetaan 65 kolom sumber

PRD tidak diubah. Pemetaan ini mencatat kesiapan schema Fase 2 dan pengecualian instruksi terbaru.

## Project Master — 25 kolom

| Kolom sumber | Target/verifikasi Fase 2 |
|---|---|
| ID | import_records.source_id — Fase 6; schema impor belum dibuat |
| JO No | projects.jo_no |
| Project User | Dihapus dari aplikasi; raw source/staging Fase 6 saja |
| Date Receive | projects.date_receive |
| Part No | projects.part_no |
| Part Name | projects.part_name |
| Customer | projects.customer |
| Model | projects.model |
| No Doc | projects.no_doc |
| Material | projects.material |
| Spec | projects.spec — tersedia di DB, tidak ditampilkan pada form Project |
| Material Thickness | projects.material_thickness_raw |
| Material Width | projects.material_width_raw |
| Process | projects.process |
| T0 Target | projects.t0_target |
| T0 Actual | projects.t0_actual |
| Plan Start | projects.plan_start |
| Plan Finish | projects.plan_finish |
| Actual Start | projects.actual_start |
| Actual Finish | projects.actual_finish |
| PP1 | projects.event_1 |
| PP2 | projects.event_2 |
| MP | projects.event_3 |
| Remark Project list | projects.remark |
| Status Project | Dihapus dari aplikasi; raw source/staging Fase 6 saja |

## Progress — 40 kolom

| Kolom sumber | Target/verifikasi Fase 2 |
|---|---|
| ID | import_records.source_id — Fase 6; schema impor belum dibuat |
| Part No | projects.part_no melalui project_id |
| JO No | progresses.project_id |
| Date | progresses.activity_date |
| Activity | progresses.activity |
| Activity Description | progresses.description |
| PIC Progress | Tidak menjadi field operasional — keputusan terbaru; raw source/staging Fase 6 saja |
| PIC Customer | progresses.pic_customer |
| Process Trial | progresses.process_trial |
| Mesin | progresses.machine |
| DH | progresses.die_high_raw |
| Material Actual trial | progresses.material_actual_trial |
| Qty Material | progresses.qty_material_raw |
| Qty Process | progresses.qty_process_raw |
| Problem | problems.description |
| Foto Problem | attachments pada problem/progress |
| Corrective Action | corrective_actions.description |
| Next Action | next_actions.description |
| PIC Corrective Action | corrective_actions.pic |
| Date Corrective Action | corrective_actions.actual_date |
| PIC Next Action | next_actions.pic |
| Date Next Action | next_actions.actual_date |
| Qty Sample | progresses.qty_sample_raw |
| Ket Event | progresses.event_note |
| Date Feedback Customer | feedbacks.feedback_date |
| Feedback Customer | feedbacks.content |
| Feedback Customer Doc | legacy_document_metadata — metadata saja, tanpa storage_key/download |
| PIC Feedback Customer | feedbacks.pic |
| Doc1 | legacy_document_metadata — metadata saja, tanpa storage_key/download |
| Doc2 | legacy_document_metadata — metadata saja, tanpa storage_key/download |
| Doc3 | legacy_document_metadata — metadata saja, tanpa storage_key/download |
| Doc4 | legacy_document_metadata — metadata saja, tanpa storage_key/download |
| Doc5 | legacy_document_metadata — metadata saja, tanpa storage_key/download |
| Ket Doc1 | legacy_document_metadata.description — metadata saja, tanpa storage_key/download |
| Ket Doc2 | legacy_document_metadata.description — metadata saja, tanpa storage_key/download |
| Ket Doc3 | legacy_document_metadata.description — metadata saja, tanpa storage_key/download |
| Ket Doc4 | legacy_document_metadata.description — metadata saja, tanpa storage_key/download |
| Ket Doc5 | legacy_document_metadata.description — metadata saja, tanpa storage_key/download |
| Progress Close | progresses.status |
| Remark Progress | progresses.remark |

25 + 40 = 65 kolom tercatat. Corrective action di bawah problem; Next Action mandiri pada progress; DATE/raw text dipertahankan. Importer khusus Fase 6 belum dibuat.
