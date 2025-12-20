<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Illuminate\Support\Facades\Session;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, string>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $fullName = trim($input['name']);
            $parts = preg_split('/\s+/', $fullName);
            $nome = $parts ? array_shift($parts) : '';
            $sobrenome = $parts ? implode(' ', $parts) : '';

            $user->forceFill([
                'nome' => $nome,
                'sobrenome' => $sobrenome,
                'email' => $input['email'],
            ])->save();

            Session::flash('success', 'Perfil atualizado com sucesso!');
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $fullName = trim($input['name']);
        $parts = preg_split('/\s+/', $fullName);
        $nome = $parts ? array_shift($parts) : '';
        $sobrenome = $parts ? implode(' ', $parts) : '';

        $user->forceFill([
            'nome' => $nome,
            'sobrenome' => $sobrenome,
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
