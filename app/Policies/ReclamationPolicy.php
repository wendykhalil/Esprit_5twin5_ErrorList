<?php

namespace App\Policies;

use App\Models\Reclamation;
use App\Models\User;

class ReclamationPolicy
{
    /**
     * Determine whether the user can view any reclamations.
     */
    public function viewAny(User $user): bool
    {
        // Les clients et admins peuvent voir les réclamations
        return $user->isClient() || $user->isAdmin();
    }

    /**
     * Determine whether the user can view the reclamation.
     */
    public function view(User $user, Reclamation $reclamation): bool
    {
        // L'admin peut voir toutes les réclamations
        if ($user->isAdmin()) {
            return true;
        }

        // Un client ne peut voir que ses propres réclamations
        return $reclamation->user_id === $user->id;
    }

    /**
     * Determine whether the user can create reclamations.
     */
    public function create(User $user): bool
    {
        // Seul un client authentifié peut créer une réclamation
        return $user->isClient();
    }

    /**
     * Determine whether the user can approve the reclamation.
     */
    public function approve(User $user, Reclamation $reclamation): bool
    {
        // Seul un administrateur peut approuver une réclamation
        if (!$user->isAdmin()) {
            return false;
        }

        // La réclamation ne doit pas déjà être approuvée ou rejetée
        if ($reclamation->status === 'approuvee') {
            return false;
        }

        if ($reclamation->status === 'rejetee') {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can reject the reclamation.
     */
    public function reject(User $user, Reclamation $reclamation): bool
    {
        // Seul un administrateur peut refuser une réclamation
        if (!$user->isAdmin()) {
            return false;
        }

        // La réclamation ne doit pas déjà être approuvée ou rejetée
        if ($reclamation->status === 'approuvee') {
            return false;
        }

        if ($reclamation->status === 'rejetee') {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can update the reclamation.
     */
    public function update(User $user, Reclamation $reclamation): bool
    {
        // Un client ne peut mettre à jour que ses propres réclamations et seulement en attente
        if ($user->isClient()) {
            return $reclamation->user_id === $user->id && $reclamation->status === 'en_attente';
        }

        // L'admin peut mettre à jour les réclamations en cours
        return $user->isAdmin() && in_array($reclamation->status, ['en_attente', 'en_cours']);
    }

    /**
     * Determine whether the user can delete the reclamation.
     */
    public function delete(User $user, Reclamation $reclamation): bool
    {
        // Seul l'admin peut supprimer une réclamation et seulement si elle est fermée ou en attente
        if (!$user->isAdmin()) {
            return false;
        }

        return in_array($reclamation->status, ['en_attente', 'fermee']);
    }
}
