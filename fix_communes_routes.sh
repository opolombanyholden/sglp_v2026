#!/bin/bash

# Script de correction des routes communes -> communes-villes
# Date : 03 Novembre 2025 - Version 2 (corrigée)

echo "🔧 Correction des routes geolocalisation.communes -> communes-villes..."
echo ""

# Fichiers à corriger
declare -a FILES=(
    "resources/views/admin/geolocalisation/arrondissements/index.blade.php"
    "resources/views/admin/geolocalisation/arrondissements/show.blade.php"
    "resources/views/admin/geolocalisation/communes_villes/index.blade.php"
    "resources/views/admin/geolocalisation/communes_villes/edit.blade.php"
    "resources/views/admin/geolocalisation/communes_villes/create.blade.php"
    "resources/views/admin/geolocalisation/communes_villes/show.blade.php"
)

# Compteur
TOTAL=0

for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        # Compter les occurrences avant
        BEFORE=$(grep -o "geolocalisation\.communes\." "$file" 2>/dev/null | wc -l | tr -d ' ')
        
        # Faire le remplacement (compatible macOS)
        sed -i '' 's/geolocalisation\.communes\./geolocalisation.communes-villes./g' "$file"
        
        # Compter les occurrences après
        AFTER=$(grep -o "geolocalisation\.communes\." "$file" 2>/dev/null | wc -l | tr -d ' ')
        
        # Calculer les corrections
        if [ "$BEFORE" -gt "$AFTER" ]; then
            FIXED=$((BEFORE - AFTER))
            TOTAL=$((TOTAL + FIXED))
            echo "✅ $(basename "$file") : $FIXED correction(s)"
        elif [ "$BEFORE" -gt 0 ]; then
            echo "⚠️  $(basename "$file") : Aucune correction (vérifiez manuellement)"
        fi
    else
        echo "❌ Fichier non trouvé : $file"
    fi
done

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🎉 Total : $TOTAL correction(s) effectuée(s)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Vérification finale
echo "Vérification finale..."
REMAINING=$(grep -r "geolocalisation\.communes\." resources/views/admin/geolocalisation/ 2>/dev/null | grep -v "communes-villes" | wc -l | tr -d ' ')

if [ "$REMAINING" = "0" ]; then
    echo "✅ ✅ ✅ SUCCÈS : Toutes les occurrences ont été corrigées !"
    echo ""
    echo "Prochaine étape :"
    echo "  1. php artisan view:clear"
    echo "  2. php artisan config:clear"
    echo "  3. Testez : http://localhost:8888/admin/geolocalisation/provinces"
else
    echo "⚠️  Il reste $REMAINING occurrence(s)"
    echo ""
    echo "Détails :"
    grep -r "geolocalisation\.communes\." resources/views/admin/geolocalisation/ 2>/dev/null | grep -v "communes-villes"
fi

echo ""