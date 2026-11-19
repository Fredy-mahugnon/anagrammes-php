# Vérificateur d'anagrammes (TP PHP)

Application PHP/MySQL de vérification d'anagrammes, reconstruite à partir de zéro pour
correspondre aux huit captures de référence fournies.

## Fonctionnalités

- Saisie de deux chaînes puis vérification : `Les deux chaînes sont des anagrammes.` /
  `Les deux chaînes ne sont pas des anagrammes.`
- Validation : lettres, espaces, apostrophes et tirets uniquement (les chiffres sont refusés).
- Normalisation (minuscules, accents supprimés, espaces/apostrophes/tirets ignorés).
- Inscription et connexion avec mots de passe hachés (`password_hash` / `password_verify`).
- Historique des vérifications pour l'utilisateur connecté.
- Les vérifications des visiteurs sont rattachées à leur compte lors de la connexion.
- Protection CSRF sur tous les formulaires ; la déconnexion ne supprime aucun compte.

## Installation (XAMPP)

1. Copier ce dossier dans `C:\xampp\htdocs\ANANOGRAMME`.
2. Démarrer Apache et MySQL depuis le panneau de contrôle XAMPP.
3. Importer le schéma et les données de démonstration :

   ```powershell
   Get-Content -Raw -Encoding UTF8 'sql\schema.sql' | & 'C:\xampp\mysql\bin\mysql.exe' -u root --default-character-set=utf8mb4
   ```

   Le script crée la base `anagrammes_db` et deux comptes de démonstration
   (mot de passe : `password123`) : `O lyrhes` et `testuser`.

4. Configuration de la base : `config/database.php` (par défaut `root` / mot de passe vide,
   base `anagrammes_db`, port `3306`).
5. Ouvrir <http://127.0.0.1:8080/index.php> (ou l'URL correspondant à votre installation Apache).

## Structure

```text
config/database.php     Connexion PDO
includes/               Bootstrap, sécurité, fonctions métier, en-tête et pied de page
auth/                   Inscription, connexion, déconnexion
sql/schema.sql          Schéma, comptes de démonstration et historique de référence
assets/css/style.css    Styles (palette et proportions des captures)
screenshots/            Les 8 captures de référence régénérées
index.php               Formulaire, résultat et historique
```

## Captures

Les huit captures sont disponibles dans [`screenshots/`](screenshots/README.md) :
accueil, inscription, connexion, résultat positif, résultat négatif, historique,
erreurs de saisie et champs remplis.

## Remarque

Les captures ont été reproduites à partir des images de référence ; la police utilisée
par le navigateur peut produire de légères différences de largeur de texte.
