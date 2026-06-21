<?php

return [
    'evidence_register' => [
        [
            'area' => 'ICAO Doc 9303 MRZ QA',
            'status' => 'engineering_ready',
            'owner' => 'HQ Operations and Technical Team',
            'evidence' => 'docs/certification/icao-doc-9303-mrz-qa.md',
            'next_gate' => 'Run sample-document acceptance testing with SLID-approved passport and visa specimens.',
        ],
        [
            'area' => 'Penetration Test Readiness',
            'status' => 'ready_for_external_test',
            'owner' => 'Security Lead',
            'evidence' => 'docs/certification/security-penetration-test-readiness.md',
            'next_gate' => 'Commission accredited penetration test and close findings before production launch.',
        ],
        [
            'area' => 'Disaster Recovery',
            'status' => 'runbook_ready',
            'owner' => 'Infrastructure Lead',
            'evidence' => 'docs/certification/disaster-recovery-runbook.md',
            'next_gate' => 'Perform restore drill against a non-production environment.',
        ],
        [
            'area' => 'Production Monitoring',
            'status' => 'runbook_ready',
            'owner' => 'Operations Lead',
            'evidence' => 'docs/certification/production-monitoring-runbook.md',
            'next_gate' => 'Connect production log, queue, uptime, SMTP, and payment alerts.',
        ],
        [
            'area' => 'Data Protection Review',
            'status' => 'policy_review_required',
            'owner' => 'SLID Legal and Data Protection Officer',
            'evidence' => 'docs/certification/data-protection-review.md',
            'next_gate' => 'Approve retention, access, consent, sharing, and incident notification policies.',
        ],
    ],
];
