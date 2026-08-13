<?php

namespace Tests\Feature;

use App\Contracts\MrzExtractor;
use App\Models\Country;
use App\Models\Nationality;
use App\Models\Passenger;
use App\Models\StaffTitle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OfficeEvisaApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['features.emergency_travel_certificate' => true]);
    }

    #[Test]
    public function public_user_cannot_open_office_entry_page(): void
    {
        $this->get('/emergency-travel-certificate/apply')
            ->assertRedirect('/login');
    }

    #[Test]
    public function office_entry_page_explains_passport_upload_and_manual_editing(): void
    {
        $this->actingAsStaffUserWithTitle('etc_issuer', 'ETC Issuer');

        Nationality::query()->create([
            'name' => 'Sierra Leone',
            'code' => 'SLE',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Country::query()->create([
            'name' => 'Sierra Leone',
            'iso2' => 'SL',
            'iso3' => 'SLE',
            'nationality' => 'Sierra Leonean',
            'active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->get('/emergency-travel-certificate/apply');
        $content = $response->getContent();

        $response->assertOk()
            ->assertSee('Emergency Travel Certificate Office Entry')
            ->assertSee('Traveler Type and Evidence')
            ->assertSee('Adult')
            ->assertSee('Child')
            ->assertSee('ECOWAS')
            ->assertSee('Non-ECOWAS')
            ->assertSee('Passport / NIN evidence')
            ->assertSee('Read passport and continue')
            ->assertSee('Image unclear? Type MRZ lines instead')
            ->assertSee('Save and continue manually')
            ->assertSee('Staff login required')
            ->assertSee('Entry sections')
            ->assertSee('Traveler Photo')
            ->assertSee('Personal Details')
            ->assertSee('Passport / NIN No.')
            ->assertSee('Place of Birth')
            ->assertSee('Marital Status')
            ->assertSee('Address, Contact, and Guardian')
            ->assertSee('Parent / Guardian Details')
            ->assertSee('Destination and Purpose of Travel')
            ->assertSee('Purpose of Traveling')
            ->assertSee('Carrier / Transport, if known')
            ->assertSee('Reference, if known')
            ->assertSee('Route Details')
            ->assertSee('Images over 2 MB are compressed before upload.')
            ->assertSee('data-compress-image', false)
            ->assertSee('Official use and payment')
            ->assertSee('Official Use Only')
            ->assertSee('Payment')
            ->assertSee('WanGov/GovPay fee confirmation')
            ->assertSee('Officer certification')
            ->assertSee('Save and continue')
            ->assertSee('Submit office entry and continue to payment')
            ->assertSee('Sierra Leone - SLE')
            ->assertSee('Family emergency');

        $this->assertStringNotContainsString('Flight Number, if known', $content);
        $this->assertMatchesRegularExpression('/id="guardian-section"[^>]*class="hidden /', $content);
    }

    #[Test]
    public function officer_can_read_passport_mrz_before_completing_form(): void
    {
        $this->actingAsStaffUserWithTitle('etc_issuer', 'ETC Issuer');

        $this->app->bind(MrzExtractor::class, fn () => new class implements MrzExtractor
        {
            public function extract(string $absoluteImagePath): array
            {
                return [
                    'text' => implode("\n", [
                        'P<SLEJAMES<<MOHAMED<<<<<<<<<<<<<<<<<<<<<<<<',
                        'SLR0923770SLE8604217M2903124<<<<<<<<<<<<06',
                    ]),
                    'confidence' => null,
                ];
            }
        });

        $response = $this->postJson('/emergency-travel-certificate/read-passport', [
            'passport_biodata_image' => UploadedFile::fake()->image('passport.jpg', 1400, 900),
        ]);

        $response->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('parsed.surname', 'JAMES')
            ->assertJsonPath('parsed.given_names', 'MOHAMED')
            ->assertJsonPath('parsed.passport_number', 'SLR092377')
            ->assertJsonPath('parsed.nationality_code', 'SLE');
    }

    #[Test]
    public function officer_can_read_typed_mrz_lines_without_image_ocr(): void
    {
        $this->actingAsStaffUserWithTitle('etc_issuer', 'ETC Issuer');

        $response = $this->postJson('/emergency-travel-certificate/read-passport', [
            'mrz_line_1' => 'P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<',
            'mrz_line_2' => 'L898902C36UTO7408122F1204159ZE184226B<<<<<10',
        ]);

        $response->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('parsed.surname', 'ERIKSSON')
            ->assertJsonPath('parsed.given_names', 'ANNA MARIA')
            ->assertJsonPath('parsed.passport_number', 'L898902C3')
            ->assertJsonPath('parsed.nationality_code', 'UTO');
    }

    #[Test]
    public function officer_can_submit_office_entry_with_a_passport_biodata_upload(): void
    {
        Storage::fake('local');

        $issuer = $this->actingAsStaffUserWithTitle('etc_issuer', 'ETC Issuer');

        Country::query()->create([
            'name' => 'Sierra Leone',
            'iso2' => 'SL',
            'iso3' => 'SLE',
            'nationality' => 'Sierra Leonean',
            'active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->post('/emergency-travel-certificate/apply', [
            'applicant_category' => 'adult',
            'regional_category' => 'ecowas',
            'identity_document_type' => 'passport',
            'surname' => 'JAMES',
            'given_names' => 'MOHAMED',
            'nationality' => 'Sierra Leone',
            'nationality_code' => 'SLE',
            'passport_number' => 'SLR092377',
            'passport_biodata_image' => UploadedFile::fake()->image('passport.jpg', 1400, 900),
            'applicant_photo' => UploadedFile::fake()->image('photo.jpg', 600, 600),
            'passport_expiry_year' => now()->addYears(3)->format('Y'),
            'passport_expiry_month' => now()->addYears(3)->format('m'),
            'passport_expiry_day' => now()->addYears(3)->format('d'),
            'sex' => 'M',
            'date_of_birth_year' => '1986',
            'date_of_birth_month' => '04',
            'date_of_birth_day' => '21',
            'place_of_birth' => 'Kenema',
            'country_of_birth' => 'Sierra Leone',
            'country_of_residence' => 'Sierra Leone',
            'applicant_address' => '15 Sumaila Town',
            'occupation' => 'Consultant',
            'marital_status' => 'married',
            'email' => 'traveler@example.test',
            'phone' => '+232700000000',
            'point_of_entry' => 'Emergency Travel Certificate Desk',
            'purpose_of_visit' => 'Family emergency',
            'period_of_stay_days' => 30,
            'arrival_date' => now()->addWeek()->toDateString(),
            'destination_country' => 'Guinea',
            'flight_carrier' => 'Private vehicle',
            'flight_number' => 'ABJ-123',
            'flight_details' => 'Freetown to Conakry by road via Gbalamuya',
            'destination_address' => 'Conakry',
            'remarks' => 'Lost passport replacement travel.',
            'guardian_name' => 'Stale Guardian',
            'guardian_relationship' => 'Parent',
            'guardian_address' => 'Old Draft Address',
            'guardian_phone' => '+232700000004',
            'applicant_certification' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $passenger = Passenger::query()->where('passport_number', 'SLR092377')->firstOrFail();

        $this->assertNotNull($passenger->passport_biodata_image_path);
        $this->assertSame('office-assisted-capture', $passenger->passport_biodata_capture_device);
        Storage::disk('local')->assertExists($passenger->passport_biodata_image_path);

        $application = $passenger->visaApplications()->firstOrFail();

        $this->assertSame($issuer->id, $application->created_by);
        $this->assertSame($issuer->id, $application->submitted_by);
        $this->assertSame($issuer->id, $application->latestInvoice->created_by);
        $this->assertSame('adult', $application->applicant_category);
        $this->assertSame('ecowas', $application->regional_category);
        $this->assertSame('passport', $application->identity_document_type);
        $this->assertSame('SLR092377', $application->identity_document_number);
        $this->assertSame('Kenema', $application->place_of_birth);
        $this->assertSame('married', $application->marital_status);
        $this->assertSame('15 Sumaila Town', $application->applicant_address);
        $this->assertSame('Guinea', $application->destination_country);
        $this->assertSame('Family emergency', $application->purpose_of_visit);
        $this->assertSame('Lost passport replacement travel.', $application->remarks);
        $this->assertNull($application->guardian_name);
        $this->assertNull($application->guardian_relationship);
        $this->assertNull($application->guardian_address);
        $this->assertNull($application->guardian_phone);
        $this->assertSame('ecowas', $application->travel_history['regional_category']);
        $this->assertSame('Guinea', $application->travel_history['destination_country']);
        $this->assertNull($application->accommodation_type);
        $this->assertNotNull($application->applicant_photo_path);
        $this->assertNotNull($application->applicant_certified_at);
        Storage::disk('local')->assertExists($application->applicant_photo_path);
    }

    #[Test]
    public function child_etc_applications_require_guardian_details(): void
    {
        Storage::fake('local');

        $this->actingAsStaffUserWithTitle('etc_issuer', 'ETC Issuer');

        Country::query()->create([
            'name' => 'Sierra Leone',
            'iso2' => 'SL',
            'iso3' => 'SLE',
            'nationality' => 'Sierra Leonean',
            'active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->post('/emergency-travel-certificate/apply', [
            'applicant_category' => 'child',
            'regional_category' => 'ecowas',
            'identity_document_type' => 'passport',
            'surname' => 'JAMES',
            'given_names' => 'AMINATA',
            'nationality' => 'Sierra Leone',
            'nationality_code' => 'SLE',
            'passport_number' => 'ETCCHILD01',
            'passport_biodata_image' => UploadedFile::fake()->image('passport.jpg', 1400, 900),
            'applicant_photo' => UploadedFile::fake()->image('photo.jpg', 600, 600),
            'sex' => 'F',
            'date_of_birth_year' => now()->subYears(8)->format('Y'),
            'date_of_birth_month' => now()->subYears(8)->format('m'),
            'date_of_birth_day' => now()->subYears(8)->format('d'),
            'place_of_birth' => 'Bo',
            'country_of_birth' => 'Sierra Leone',
            'applicant_address' => 'Bo',
            'occupation' => 'Student',
            'email' => 'guardian@example.test',
            'phone' => '+232700000003',
            'point_of_entry' => 'Emergency Travel Certificate Desk',
            'purpose_of_visit' => 'Family emergency',
            'destination_country' => 'Guinea',
            'applicant_certification' => '1',
        ]);

        $response->assertSessionHasErrors([
            'guardian_name',
            'guardian_relationship',
            'guardian_address',
            'guardian_phone',
        ]);
    }

    private function actingAsStaffUserWithTitle(string $code, string $name): User
    {
        $title = StaffTitle::query()->firstOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'description' => "{$name} test role",
                'active' => true,
            ]
        );

        $user = User::factory()->create(['active' => true]);

        $user->staffTitles()->attach($title->id, [
            'assigned_at' => now(),
            'is_primary' => true,
        ]);

        $this->actingAs($user);

        return $user->fresh(['staffTitles']);
    }
}
