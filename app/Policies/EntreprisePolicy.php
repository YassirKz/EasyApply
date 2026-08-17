<?php

namespace App\Policies;

use App\Models\Entreprise;
use App\Models\User;

class EntreprisePolicy
{
    public function view(User $user, Entreprise $entreprise): bool { return $entreprise->user_id === $user->id; }
    public function update(User $user, Entreprise $entreprise): bool { return $this->view($user, $entreprise); }
    public function delete(User $user, Entreprise $entreprise): bool { return $this->view($user, $entreprise); }
}
