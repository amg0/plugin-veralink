# Plugin Jeedom pour controler une Vera ( Ezlo, anciennement Micasaverde )

Ce plugin permet de detecter les pieces et les scenes d'un controlleur **Vera** et de rendre accessible depuis **Jeedom**. il permet de lancer l'execution des scenes a distance sur la vera.

## Utilisation
Le plugin permet de declarer une vera par son addresse IP ce qui creer un equipement de type 'racine'.
- Les pieces de la vera seront detectees et proposees comme un autre equipement dans jeedom. l'utilisateur pourra choisir quels equipements ( les pieces ) il active et rend visibles dans jeedom.

- Si la piece de la vera comporte une scene,  une action est cree sur l'equipement piece. cette action permet de declencher la scene sur la vera.

Les equipements de type pieces, suivront toujours l'equipement racine ( la vera ). 
* Si l'equipement vera est detruit, les pieces associees seronts detruites.
* si l'equipement vera est deplacé dans un autre objet parent jeedom, les pieces seront deplacees dans le meme objet parent.
* la liste des equipements restera synchronisé avec la listes des equipements sur la vera.


- les equipementes de type binaryLight & TemperatureSensor sur la vera sont crées comme des equipements sous Jeedom et l'ETAT ainsi que les actions ON OFF sont disponibles. les types supportés sont:
  - binaryLight
  - TemperatureSensor
  - LightSensor
  - MotionSensor

## Change Log
[Change Log](changelog.md)

## Installation

au debut il faut commencer par ajouter le plugin. pour le moment a travers github avec la branche master ou beta
![ajouter plugin](../images/ajouterplugin.png)

puis il faut l'activer dans jeedom
![ajouter plugin](../images/activerplugin.png)

dans la configuration du plugin, il faut configurer la frequence du refresh des donnees de la vera. 10s semble etre une bonne valeur.
![ajouter plugin](../images/configuration.png)
![ajouter plugin](../images/configurerrefresh.png)

il faut creer un nouvelle equipement qui represente votre vera. pour cela on creer un equipement, on choisi un objet parent auquel le rattacher, puis on renseigne l'addresse IP de la vera
![ajouter plugin](../images/ipaddress.png)

a la sauvegarde, il faut etre patient. le plugin communique avec la vera et va creer des equipements pour les pieces de la vera avec des commandes pour lancer les scenes qui sont dans ces pieces. le plugin va aussi creer des equipements pour les objets de type BinaryLight avec des commandes ETAT, ON et OFF. tout cela prend un petit de temps, mais si tout ce passe bien vous obtiendrer une liste d'equipement comme ceci. c'est alors votre choix de decider quel equipement rendre actif et visible pour qu'il soit fonctionel et visible sur le dashboard de jeedom
![ajouter plugin](../images/equipements.png)

Une fois que tout est fait, le dashboard de la vera ressemblera, par defaut a ceci.
![dashboard](../images/veradashboard.png)

