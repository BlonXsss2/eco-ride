#!/bin/bash
# au demarrage du conteneur, on seed la BDD si besoin
php /var/www/html/scripts/seed_prod.php || true
exec apache2-foreground
