<?php
// FILE: app/Enums/PurposeOfVisit.php

namespace App\Enums;

enum PurposeOfVisit: string
{
    case Tourism = 'tourism';
    case Business = 'business';
    case OfficialGovernment = 'official_government';
    case ConferenceMeeting = 'conference_meeting';
    case FamilyVisit = 'family_visit';
    case Transit = 'transit';
    case MedicalTreatment = 'medical_treatment';
    case ReligiousMission = 'religious_mission';
    case SportsCulturalEvent = 'sports_cultural_event';
    case MediaJournalism = 'media_journalism';
    case StudyTrainingShort = 'study_training_short';
    case Humanitarian = 'humanitarian';
    case CrewDuty = 'crew_duty';

    public function label(): string
    {
        return match ($this) {
            self::Tourism => 'Tourism',
            self::Business => 'Business',
            self::OfficialGovernment => 'Official / Government',
            self::ConferenceMeeting => 'Conference / Meeting',
            self::FamilyVisit => 'Family Visit',
            self::Transit => 'Transit',
            self::MedicalTreatment => 'Medical Treatment',
            self::ReligiousMission => 'Religious Mission',
            self::SportsCulturalEvent => 'Sports / Cultural Event',
            self::MediaJournalism => 'Media / Journalism',
            self::StudyTrainingShort => 'Study / Short Training',
            self::Humanitarian => 'Humanitarian',
            self::CrewDuty => 'Crew Duty',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn (self $case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases()
        );
    }
}