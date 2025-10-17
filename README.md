# blogshortcut
Mini plugin Grav ajoutant un bouton **Nouvel article** dans l'administration (y compris le tableau de bord) pour créer rapidement une page d'article (blueprint `item`) sous la page blog configurée. Le parent peut être défini via la configuration du plugin.

## Installation

1. Copiez le dossier `user/plugins/blogshortcut` de ce dépôt vers le dossier `user/plugins/` de votre installation Grav (le chemin final doit être `user/plugins/blogshortcut`).
2. Assurez-vous que le plugin est activé :
   - soit en ajoutant `enabled: true` dans `user/config/plugins/blogshortcut.yaml`,
   - soit via l'interface d'administration dans **Configuration > Plugins**.
3. Dans la configuration du plugin (fichier `user/config/plugins/blogshortcut.yaml` ou interface admin), définissez l'identifiant de la page parent `blog_route` si celui par défaut ne correspond pas à votre installation.

Une fois le plugin actif, le bouton **Nouvel article** apparaît dans la barre supérieure de l'administration et redirige directement vers la création d'un nouvel article sous la page blog choisie.
