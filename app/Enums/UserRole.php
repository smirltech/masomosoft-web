<?php

namespace App\Enums;

enum UserRole: string
{


    case admin = 'admin';
    case promoteur = 'promoteur';
    case coordonnateur = 'coordonnateur';
    case financier = 'financier';
    case eleve = 'eleve';
    case parent = 'parent';
    case enseignant = 'enseignant';

    public function permissions(): array
    {
        // return permissions based on user role
        return match ($this) {
            self::promoteur, self::coordonnateur => [
                RolePermission::users_all->value,
                RolePermission::roles_all->value,
                RolePermission::permissions_all->value,
                RolePermission::annees_all->value,
                RolePermission::tags_all->value,
                RolePermission::depenses_all->value,
            ],
            self::admin => [
                RolePermission::schools_all->value,
                RolePermission::annees_all->value,
                RolePermission::operations_all->value,
                RolePermission::cessions_all->value,
                RolePermission::cotisations_all->value,
                RolePermission::materiel_categories_all->value,
                RolePermission::materiels_all->value,
                RolePermission::cours_all->value,
                RolePermission::enseignants_all->value,
                RolePermission::inscriptions_all->value,
                RolePermission::options_all->value,
                RolePermission::filieres_all->value,
                RolePermission::permissions_all->value,
                RolePermission::roles_all->value,
                RolePermission::units_all->value,
                RolePermission::tags_all->value,
                RolePermission::auteurs_all->value,
                RolePermission::classes_all->value,
                RolePermission::devoirs_all->value,
                RolePermission::responsables_all->value,
                RolePermission::sections_all->value,
                RolePermission::users_all->value,
                RolePermission::rayons_all->value,
                RolePermission::consommables_all->value,
                RolePermission::ouvrages_all->value,
                RolePermission::depenses_all->value,
                RolePermission::frais_all->value,
                RolePermission::mouvements_all->value,
                RolePermission::perceptions_all->value,
                RolePermission::revenus_all->value,
                RolePermission::depense_types_all->value,
                RolePermission::eleves_all->value,
                RolePermission::presences_all->value,
            ],
            self::enseignant => [
                RolePermission::devoirs_all->value,
            ],
            self::financier => [
                RolePermission::annees_all->value,
                RolePermission::enseignants_all->value,
                RolePermission::inscriptions_all->value,
                RolePermission::options_all->value,
                RolePermission::classes_all->value,
                RolePermission::responsables_all->value,
                RolePermission::sections_all->value,
                RolePermission::depenses_all->value,
                RolePermission::frais_all->value,
                RolePermission::perceptions_create->value,
                RolePermission::perceptions_view->value,
                RolePermission::revenus_all->value,
                RolePermission::depense_types_all->value,
                RolePermission::eleves_all->value,
                RolePermission::presences_all->value,
            ],
            self::parent => [
                RolePermission::eleves_view->value,
                RolePermission::devoirs_view->value,
                RolePermission::frais_view->value,
            ],
            self::eleve => [
                RolePermission::devoirs_view->value,
                RolePermission::frais_all->value,
                RolePermission::enseignants_view->value,
            ],
        };

    }


}
