<?php

namespace App\Enums;

use EmreYarligan\EnumConcern\EnumConcern;

enum RolePermission: string
{
    use EnumConcern;

    // ------------------School ---------------- //
    case schools_all = 'schools.*';
    //------------------ Mouvements ---------------- //
    case mouvements_all = 'mouvements.*';
    case mouvements_view = 'mouvements.view.*';
    case mouvements_create = 'mouvements.create';
    case mouvements_update_all = 'mouvements.update.*';
    case mouvements_delete = 'mouvements.delete.*';
    //------------------ ./Mouvements ---------------- //
    //------------------ Materiels ---------------- //
    case materiels_all = 'materiels.*';
    case materiels_view = 'materiels.view.*';
    case materiels_create = 'materiels.create';
    case materiels_update_all = 'materiels.update.*';
    case materiels_delete = 'materiels.delete.*';
    //------------------ ./Materiels ---------------- //
    //------------------ MaterielCategories ---------------- //
    case materiel_categories_all = 'materiel-categories.*';
    case materiel_categories_view = 'materiel-categories.view.*';
    case materiel_categories_create = 'materiel-categories.create';
    case materiel_categories_update_all = 'materiel-categories.update.*';
    case materiel_categories_delete = 'materiel-categories.delete.*';
    //------------------ ./MaterielCategories ---------------- //
    //------------------ Consommables ---------------- //
    case consommables_all = 'consommables.*';
    case consommables_view = 'consommables.view.*';
    case consommables_create = 'consommables.create';
    case consommables_update_all = 'consommables.update.*';
    case consommables_delete = 'consommables.delete.*';
    //------------------ ./Consommables ---------------- //
    //------------------ Units ---------------- //
    case units_all = 'units.*';
    case units_view = 'units.view.*';
    case units_create = 'units.create';
    case units_update_all = 'units.update.*';
    case units_delete = 'units.delete.*';
    //------------------ ./Units ---------------- //
    //------------------ Ouvrages ---------------- //
    case ouvrages_all = 'ouvrages.*';
    case ouvrages_view = 'ouvrages.view.*';
    case ouvrages_create = 'ouvrages.create';
    case ouvrages_update_all = 'ouvrages.update.*';
    case ouvrages_delete = 'ouvrages.delete.*';
    //------------------ ./Ouvrages ---------------- //
    //------------------ Rayon ---------------- //
    case rayons_all = 'rayons.*';
    case rayons_view = 'rayons.view.*';
    case rayons_create = 'rayons.create';
    case rayons_update_all = 'rayons.update.*';
    case rayons_delete = 'rayons.delete.*';
    //------------------ ./Rayon ---------------- //
    //------------------ Etiquettes ---------------- //
    case tags_all = 'tags.*';
    case tags_view = 'tags.view.*';
    case tags_create = 'tags.create';
    case tags_update_all = 'tags.update.*';
    case tags_delete = 'tags.delete.*';
    //------------------ ./Etiquettes ---------------- //
    //------------------ Auteurs ---------------- //
    case auteurs_all = 'auteurs.*';
    case auteurs_view = 'auteurs.view.*';
    case auteurs_create = 'auteurs.create';
    case auteurs_update_all = 'auteurs.update.*';
    case auteurs_delete = 'auteurs.delete.*';
    //------------------ ./Auteurs ---------------- //
    //------------------ Depenses ---------------- //
    case depenses_all = 'depenses.*';
    case depenses_view = 'depenses.view';
    case depenses_create = 'depenses.create';
    case depenses_update_all = 'depenses.update';
    case depenses_delete = 'depenses.delete';
    //------------------ ./Depenses ---------------- //
    //------------------ DepenseTypes ---------------- //
    case depense_types_all = 'depense-types.*';
    case depense_types_view = 'depense-types.view.*';
    case depense_types_create = 'depense-types.create';
    case depense_types_update_all = 'depense-types.update.*';
    case depense_types_delete = 'depense-types.delete.*';
    //------------------ ./DepenseTypes ---------------- //
    //------------------ Revenu ---------------- //
    case revenus_all = 'revenus.*';
    case revenus_view = 'revenus.view.*';
    case revenus_create = 'revenus.create';
    case revenus_update_all = 'revenus.update.*';
    case revenus_delete = 'revenus.delete.*';
    //------------------ ./Revenu ---------------- //
    //------------------ Frais ---------------- //
    case frais_all = 'frais.*';
    case frais_view = 'frais.view.*';
    case frais_create = 'frais.create';
    case frais_update_all = 'frais.update.*';
    case frais_delete = 'frais.delete.*';
    //------------------ ./Frais ---------------- //
    //------------------ Perceptions ---------------- //
    case perceptions_all = 'perceptions.*';
    case perceptions_view = 'perceptions.view';
    case perceptions_create = 'perceptions.create';
    case perceptions_update = 'perceptions.update';
    case perceptions_delete = 'perceptions.delete';
    //------------------ ./Perceptions ---------------- //
    //------------------ Classes ---------------- //
    case classes_all = 'classes.*';
    case classes_view = 'classes.view.*';
    case classes_create = 'classes.create';
    case classes_update_all = 'classes.update.*';
    case classes_delete = 'classes.delete.*';
    //------------------ ./Classes ---------------- //
    //------------------ Cours ---------------- //
    case cours_all = 'cours.*';
    case cours_view = 'cours.view.*';
    case cours_create = 'cours.create';
    case cours_update_all = 'cours.update.*';
    case cours_delete = 'cours.delete.*';
    //------------------ ./Cours ---------------- //
    /*//------------------ Factures ---------------- //
    case factures_all = 'factures.*';
    case factures_view = 'factures.view.*';
    case factures_create = 'factures.create';
    case factures_update_all = 'factures.update.*';
    case factures_delete = 'factures.delete.*';
    //------------------ ./Factures ---------------- //*/

    //------------------ Permissions ---------------- //
    case permissions_all = 'permissions.*';
    case permissions_view = 'permissions.view.*';
    case permissions_create = 'permissions.create';
    case permissions_update = 'permissions.update';
    case permissions_delete = 'permissions.delete.*';
    //------------------ ./Permissions ---------------- //
    //------------------ Roles ---------------- //
    case roles_all = 'roles.*';
    case roles_view = 'roles.view.*';
    case roles_create = 'roles.create';
    case roles_update_all = 'roles.update.*';
    case roles_delete = 'roles.delete.*';
    //------------------ ./Roles ---------------- //

    //------------------ Devoirs ---------------- //
    case devoirs_all = 'devoirs.*';
    case devoirs_view = 'devoirs.view.*';
    case devoirs_create = 'devoirs.create';
    case devoirs_update_all = 'devoirs.update.*';
    case devoirs_delete = 'devoirs.delete.*';
    //------------------ ./Devoirs ---------------- //

    //------------------ Responsable ---------------- //
    case responsables_all = 'responsables.*';
    case responsables_view = 'responsables.view.*';
    case responsables_create = 'responsables.create';
    case responsables_update_all = 'responsables.update.*';
    case responsables_delete = 'responsables.delete.*';
    //------------------ ./Responsable ---------------- //

    //------------------ Eleve ---------------- //
    case eleves_all = 'eleves.*';
    case eleves_view = 'eleves.view.*';
    case eleves_create = 'eleves.create';
    case eleves_update_all = 'eleves.update.*';
    case eleves_delete = 'eleves.delete.*';
    //------------------ ./Eleve ---------------- //

    //------------------ Inscription ---------------- //
    case inscriptions_all = 'inscriptions.*';
    case inscriptions_view = 'inscriptions.view.*';
    case inscriptions_create = 'inscriptions.create';
    case inscriptions_update_all = 'inscriptions.update.*';
    case inscriptions_delete = 'inscriptions.delete.*';
    //------------------ ./Inscription ---------------- //
    //------------------ Filière ---------------- //
    case filieres_all = 'filieres.*';
    case filieres_view = 'filieres.view.*';
    case filieres_create = 'filieres.create';
    case filieres_update_all = 'filieres.update.*';
    case filieres_delete = 'filieres.delete.*';
    //------------------ ./Filière ---------------- //
    //------------------ Option ---------------- //
    case options_all = 'options.*';
    case options_view = 'options.view.*';
    case options_create = 'options.create';
    case options_update_all = 'options.update.*';
    case options_delete = 'options.delete.*';
    //------------------ ./Option ---------------- //
    //------------------ Section ---------------- //
    case sections_all = 'sections.*';
    case sections_view = 'sections.view.*';
    case sections_create = 'sections.create';
    case sections_update_all = 'sections.update.*';
    case sections_delete = 'sections.delete.*';
    //------------------ ./Section ---------------- //
    //------------------ Enseignant ---------------- //
    case enseignants_all = 'enseignants.*';
    case enseignants_view = 'enseignants.view.*';
    case enseignants_create = 'enseignants.create';
    case enseignants_update_all = 'enseignants.update.*';
    case enseignants_delete = 'enseignants.delete.*';

    //------------------ ./Enseignant ---------------- //


    //------------------ Année ---------------- //
    case annees_all = 'annees.*';
    case annees_view = 'annees.view.*';
    case annees_create = 'annees.create';
    case annees_update_all = 'annees.update.*';
    case annees_delete = 'annees.delete.*';
    //------------------ ./Année ---------------- //

    //------------------ Users ---------------- //
    case users_all = 'users.*';
    case users_view = 'users.view.*';
    case users_create = 'users.create';
    case users_update_all = 'users.update.*';
    case users_delete = 'users.delete.*';

    //------------------ ./Users ---------------- //

    //------------------ Presences ---------------- //
    case presences_all = 'presences.*';

    //------------------ Cotisations ---------------- //
    case cotisations_all = 'cotisations.*';

    //------------------ Operations ---------------- //
    case operations_all = 'operations.*';
    case cessions_all = 'cessions.*';

    public function label(): string
    {
        return match ($this) {
            default => $this->value,
            self::schools_all => 'Gérer les paramètres de l\'école',
            // Users
            self::users_create => 'Créer un utilisateur',
            self::users_delete => 'Supprimer un utilisateur',
            self::users_all => 'Gérer les utilisateurs',
            self::users_update_all => 'Modifier un utilisateur',
            self::users_view => 'Voir un utilisateur',
            // Roles
            self::roles_all => 'Gérer les rôles',
            self::roles_create => 'Créer un rôle',
            self::roles_delete => 'Supprimer un rôle',
            self::roles_update_all => 'Modifier un rôle',
            self::roles_view => 'Voir un rôle',
            // Permissions
            self::permissions_all => 'Gérer les permissions',
            self::permissions_create => 'Créer une permission',
            self::permissions_delete => 'Supprimer une permission',
            self::permissions_update => 'Modifier une permission',
            self::permissions_view => 'Voir une permission',
            // Années
            self::annees_all => 'Gérer les années',
            self::annees_create => 'Créer une année',
            self::annees_delete => 'Supprimer une année',
            self::annees_update_all => 'Modifier une année',
            self::annees_view => 'Voir une année',
            // Eleves
            self::eleves_all => 'Gérer les élèves',
            self::eleves_create => 'Créer un élève',
            self::eleves_delete => 'Supprimer un élève',
            self::eleves_update_all => 'Modifier un élève',
            self::eleves_view => 'Voir un élève',
            // Responsables
            self::responsables_all => 'Gérer les responsables',
            self::responsables_create => 'Créer un responsable',
            self::responsables_delete => 'Supprimer un responsable',
            self::responsables_update_all => 'Modifier un responsable',
            self::responsables_view => 'Voir un responsable',
            // Enseignants
            self::enseignants_all => 'Gérer les enseignants',
            self::enseignants_create => 'Créer un enseignant',
            self::enseignants_delete => 'Supprimer un enseignant',
            self::enseignants_update_all => 'Modifier un enseignant',
            self::enseignants_view => 'Voir un enseignant',
            // Sections
            self::sections_all => 'Gérer les sections',
            self::sections_create => 'Créer une section',
            self::sections_delete => 'Supprimer une section',
            self::sections_update_all => 'Modifier une section',
            self::sections_view => 'Voir une section',
            // Options
            self::options_all => 'Gérer les options',
            self::options_create => 'Créer une option',
            self::options_delete => 'Supprimer une option',
            self::options_update_all => 'Modifier une option',
            self::options_view => 'Voir une option',
            // Filières
            self::filieres_all => 'Gérer les filières',
            self::filieres_create => 'Créer une filière',
            self::filieres_delete => 'Supprimer une filière',
            self::filieres_update_all => 'Modifier une filière',
            self::filieres_view => 'Voir une filière',
            // Inscriptions
            self::inscriptions_all => 'Gérer les inscriptions',
            self::inscriptions_create => 'Créer une inscription',
            self::inscriptions_delete => 'Supprimer une inscription',
            self::inscriptions_update_all => 'Modifier une inscription',
            self::inscriptions_view => 'Voir une inscription',
            // Devoirs
            self::devoirs_all => 'Gérer les devoirs',
            self::devoirs_create => 'Créer un devoir',
            self::devoirs_delete => 'Supprimer un devoir',
            self::devoirs_update_all => 'Modifier un devoir',
            self::devoirs_view => 'Voir un devoir',
            /*   // Factures
               self::factures_all => 'Gérer les factures',
               self::factures_create => 'Créer une facture',
               self::factures_delete => 'Supprimer une facture',
               self::factures_update_all => 'Modifier une facture',
               self::factures_view => 'Voir une facture',*/
            // Cours
            self::cours_all => 'Gérer les cours',
            self::cours_create => 'Créer un cours',
            self::cours_delete => 'Supprimer un cours',
            self::cours_update_all => 'Modifier un cours',
            self::cours_view => 'Voir un cours',
            // Classes
            self::classes_all => 'Gérer les classes',
            self::classes_create => 'Créer une classe',
            self::classes_delete => 'Supprimer une classe',
            self::classes_update_all => 'Modifier une classe',
            self::classes_view => 'Voir une classe',
            // Perceptions
            self::perceptions_all => 'Gérer les perceptions',
            self::perceptions_create => 'Créer une perception',
            self::perceptions_delete => 'Supprimer une perception',
            self::perceptions_update => 'Modifier une perception',
            self::perceptions_view => 'Voir une perception',
            // Frais
            self::frais_all => 'Gérer les frais',
            self::frais_create => 'Créer un frais',
            self::frais_delete => 'Supprimer un frais',
            self::frais_update_all => 'Modifier un frais',
            self::frais_view => 'Voir un frais',
            // Revenus
            self::revenus_all => 'Gérer les revenus',
            self::revenus_create => 'Créer un revenu',
            self::revenus_delete => 'Supprimer un revenu',
            self::revenus_update_all => 'Modifier un revenu',
            self::revenus_view => 'Voir un revenu',
            // DepenseTypes
            self::depense_types_all => 'Gérer les types de dépenses',
            self::depense_types_create => 'Créer un type de dépense',
            self::depense_types_delete => 'Supprimer un type de dépense',
            self::depense_types_update_all => 'Modifier un type de dépense',
            self::depense_types_view => 'Voir un type de dépense',
            // Depenses
            self::depenses_all => 'Gérer les dépenses',
            self::depenses_create => 'Créer une dépense',
            self::depenses_delete => 'Supprimer une dépense',
            self::depenses_update_all => 'Modifier une dépense',
            self::depenses_view => 'Voir une dépense',
            // Auteurs
            self::auteurs_all => 'Gérer les auteurs',
            self::auteurs_create => 'Créer un auteur',
            self::auteurs_delete => 'Supprimer un auteur',
            self::auteurs_update_all => 'Modifier un auteur',
            self::auteurs_view => 'Voir un auteur',
            // Etiquettes
            self::tags_all => 'Gérer les étiquettes',
            self::tags_create => 'Créer une étiquette',
            self::tags_delete => 'Supprimer une étiquette',
            self::tags_update_all => 'Modifier une étiquette',
            self::tags_view => 'Voir une étiquette',
            // OuvrageCategories
            self::rayons_all => 'Gérer les catégories d\'ouvrages',
            self::rayons_create => 'Créer une catégorie d\'ouvrage',
            self::rayons_delete => 'Supprimer une catégorie d\'ouvrage',
            self::rayons_update_all => 'Modifier une catégorie d\'ouvrage',
            self::rayons_view => 'Voir une catégorie d\'ouvrage',
            // Ouvrages
            self::ouvrages_all => 'Gérer les ouvrages',
            self::ouvrages_create => 'Créer un ouvrage',
            self::ouvrages_delete => 'Supprimer un ouvrage',
            self::ouvrages_update_all => 'Modifier un ouvrage',
            self::ouvrages_view => 'Voir un ouvrage',
            // Units
            self::units_all => 'Gérer les unités',
            self::units_create => 'Créer une unité',
            self::units_delete => 'Supprimer une unité',
            self::units_update_all => 'Modifier une unité',
            self::units_view => 'Voir une unité',
            // Consommables
            self::consommables_all => 'Gérer les consommables',
            self::consommables_create => 'Créer un consommable',
            self::consommables_delete => 'Supprimer un consommable',
            self::consommables_update_all => 'Modifier un consommable',
            self::consommables_view => 'Voir un consommable',
            // MaterielCategories
            self::materiel_categories_all => 'Gérer les catégories de matériels',
            self::materiel_categories_create => 'Créer une catégorie de matériels',
            self::materiel_categories_delete => 'Supprimer une catégorie de matériels',
            self::materiel_categories_update_all => 'Modifier une catégorie de matériels',
            self::materiel_categories_view => 'Voir une catégorie de matériels',
            // Materiels
            self::materiels_all => 'Gérer les matériels',
            self::materiels_create => 'Créer un matériel',
            self::materiels_delete => 'Supprimer un matériel',
            self::materiels_update_all => 'Modifier un matériel',
            self::materiels_view => 'Voir un matériel',
            // Mouvements
            self::mouvements_all => 'Gérer les mouvements des materiels',
            self::mouvements_create => 'Créer un mouvement de materiel',
            self::mouvements_delete => 'Supprimer un mouvement de materiel',
            self::mouvements_update_all => 'Modifier un mouvement de materiel',
            self::mouvements_view => 'Voir un mouvement de materiel',
        };
    }

}
