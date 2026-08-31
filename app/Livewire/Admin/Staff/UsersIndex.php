<?php

namespace App\Livewire\Admin\Staff;

use App\Enums\StaffTitleCode;
use App\Models\StaffTitle;
use App\Models\User;
use App\Rules\StaffEmailDomain;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class UsersIndex extends Component
{
    public string $name = '';

    public string $email = '';

    public string $staffNumber = '';

    public string $phone = '';

    public string $titleCode = 'etc_issuer';

    public string $password = '';

    public ?string $message = null;

    /** @var array{name: string, email: string, role: string, password: string, login_url: string} */
    public array $loginDetails = [];

    public bool $showLoginDetails = false;

    public function createUser(): void
    {
        abort_unless(Auth::user()?->hasStaffTitle(StaffTitleCode::SystemAdministrator->value), 403);

        $this->name = trim($this->name);
        $this->email = strtolower(trim($this->email));
        $this->staffNumber = trim($this->staffNumber);
        $this->phone = trim($this->phone);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
                new StaffEmailDomain,
            ],
            'staffNumber' => ['nullable', 'string', 'max:50', Rule::unique('users', 'staff_number')],
            'phone' => ['nullable', 'string', 'max:50'],
            'titleCode' => ['required', Rule::in($this->provisionableTitleCodes())],
            'password' => ['required', 'string', 'min:12'],
        ]);

        [$user, $title] = DB::transaction(function () use ($validated) {
            $title = StaffTitle::query()
                ->where('code', $validated['titleCode'])
                ->where('active', true)
                ->firstOrFail();

            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'email_verified_at' => now(),
                'password' => Hash::make($validated['password']),
                'staff_number' => $validated['staffNumber'] ?: null,
                'job_title' => $title->name,
                'phone' => $validated['phone'] ?: null,
                'active' => true,
            ]);

            $user->staffTitles()->attach($title->id, [
                'assigned_by_user_id' => Auth::id(),
                'assigned_at' => now(),
                'is_primary' => true,
            ]);

            return [$user, $title];
        });

        $this->loginDetails = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $title->name,
            'password' => $validated['password'],
            'login_url' => route('login'),
        ];
        $this->showLoginDetails = true;

        $this->reset(['name', 'email', 'staffNumber', 'phone', 'password']);
        $this->titleCode = StaffTitleCode::EtcIssuer->value;
        $this->message = "{$title->name} user {$user->email} created.";
    }

    public function closeLoginDetails(): void
    {
        $this->showLoginDetails = false;
        $this->loginDetails = [];
    }

    public function render(): View
    {
        return view('livewire.admin.staff.users-index', [
            'users' => User::with(['staffTitles'])->latest()->get(),
            'provisionableTitles' => StaffTitle::query()
                ->whereIn('code', $this->provisionableTitleCodes())
                ->where('active', true)
                ->orderByRaw("case code when 'etc_issuer' then 1 when 'executive_observer' then 2 else 3 end")
                ->get(),
        ]);
    }

    private function provisionableTitleCodes(): array
    {
        return [
            StaffTitleCode::EtcIssuer->value,
            StaffTitleCode::ExecutiveObserver->value,
        ];
    }
}
