# BPTMS: Phase 2 backlog

These are out of scope for Phase 1. Phase 1 is designed so they can be added without refactoring.

| Item | Phase 1 hook already in place |
|---|---|
| SMS notifications (Africa's Talking) | `SmsChannel` + `SmsGateway` interface; `AfricasTalkingGateway` is a stub |
| Online payments (mobile money, bank, URA/IRAS) | `payments.method` and manual receipt recording; add a gateway driver |
| Offline inspection form | PWA manifest/service worker exist (installability only) |
| Roles: structural_engineer, surveyor, finance_officer, inspector | Roles are data; add rows to the role seeder and `workflow_transition_roles` |
| Construction-stage inspections | `inspections` table has a `type` column |
| Occupation permits | New linked application type via `parent_application_id` |
| BIMS integration | `applications.bims_reference` stored |
| GPS maps of plots | `properties` can gain `lat`/`lng` columns |
