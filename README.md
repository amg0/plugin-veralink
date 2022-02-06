# Plugin Jeedom pour controler une Vera ( Ezlo, anciennement Micasaverde )

Ce plugin permet de detecter les pieces et les scenes d'un controlleur **Vera** et de rendre accessible a **Jeedom**.


Le plugin permet de declarer une vera par son addresse IP. 
Les pieces de la vera seront detectees et proposees comme un equipement dans jeedom. l'utilisateur pourra choisir quels equipements ( les pieces ) il active et rend visibles dans jeedom.
Si la piece de la vera comporte une scene,  une action est cree sur l'equipement piece. cette action permet de declencher la scene sur la vera.


Les equiepements de type pieces, suivront toujours l'equipemetn racine ( la vera ). 
* Si l'equipement vera les pieces seronts detruites
* si l'equiepeemnt vera est deplacé dans un autre objet, les pieces seront deplacees aussi
* la liste des equipements de type piece restera synchronisé avec la listes des pieces sur la vera




