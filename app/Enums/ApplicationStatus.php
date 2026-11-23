<?php

namespace App\Enums;

/**
 * What state an application is in. This is separate from its current stage
 * (where it is), which comes from the workflow_stages table.
 *
 * Status changes go only through WorkflowService (feature 4), driven by the
 * workflow_transitions table. Nothing else may write current_status, except
 * initial registration (Submitted) and legacy capture.
 */
enum ApplicationStatus: string
{
    case Submitted = 'submitted';
    case UnderDocumentVerification = 'under_document_verification';
    case AssignedForReview = 'assigned_for_review';
    case UnderTechnicalReview = 'under_technical_review';
    case CorrectionsRequired = 'corrections_required';
    case AwaitingResubmission = 'awaiting_resubmission';
    case UnderSiteInspection = 'under_site_inspection';
    case RecommendedForApproval = 'recommended_for_approval';
    case Approved = 'approved';
    case Deferred = 'deferred';
    case Rejected = 'rejected';
    case AwaitingPayment = 'awaiting_payment';
    case PermitIssued = 'permit_issued';
    case Withdrawn = 'withdrawn';
    case ClosedNoResponse = 'closed_no_response';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::UnderDocumentVerification => 'Under document verification',
            self::AssignedForReview => 'Assigned for review',
            self::UnderTechnicalReview => 'Under technical review',
            self::CorrectionsRequired => 'Corrections required',
            self::AwaitingResubmission => 'Awaiting resubmission',
            self::UnderSiteInspection => 'Under site inspection',
            self::RecommendedForApproval => 'Recommended for approval',
            self::Approved => 'Approved',
            self::Deferred => 'Deferred',
            self::Rejected => 'Rejected',
            self::AwaitingPayment => 'Awaiting payment',
            self::PermitIssued => 'Permit issued',
            self::Withdrawn => 'Withdrawn',
            self::ClosedNoResponse => 'Closed (no response)',
        };
    }

    /** Final states: the application is no longer being processed. */
    public function isClosed(): bool
    {
        return in_array($this, [self::Rejected, self::PermitIssued, self::Withdrawn, self::ClosedNoResponse], true);
    }

    public function isOpen(): bool
    {
        return ! $this->isClosed();
    }

    /** @return list<string> */
    public static function openValues(): array
    {
        return array_values(array_map(
            fn (self $s) => $s->value,
            array_filter(self::cases(), fn (self $s) => $s->isOpen()),
        ));
    }

    /** Filament badge colour. */
    public function color(): string
    {
        return match ($this) {
            self::CorrectionsRequired, self::AwaitingResubmission, self::Deferred, self::AwaitingPayment => 'warning',
            self::Approved, self::PermitIssued => 'success',
            self::Rejected, self::ClosedNoResponse => 'danger',
            self::Withdrawn => 'gray',
            default => 'info',
        };
    }
}
