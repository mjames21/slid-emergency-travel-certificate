<?php

namespace App\Enums;

enum StaffTitleCode: string
{
    case SystemAdministrator = 'system_administrator';
    case EtcIssuer = 'etc_issuer';
    case HqAdministrator = 'hq_administrator';
    case AirportManager = 'airport_manager';
    case ShiftSupervisor = 'shift_supervisor';
    case VisaProcessingOfficer = 'visa_processing_officer';
    case PaymentOfficer = 'payment_officer';
    case ComplianceAuditor = 'compliance_auditor';
    case ExecutiveObserver = 'executive_observer';
}
