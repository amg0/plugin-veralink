# Changelog plugin template

>**IMPORTANT**
>
>Pour rappel s'il n'y a pas d'information sur la mise à jour, c'est que celle-ci concerne uniquement de la mise à jour de documentation, de traduction ou de texte.

# 07/05/2023

- when saving the vera root Equipment, it will remove from Jeedom the equipments for VERA devices that do not exist anymore

# 30/08/2022

- Add Jeedom Info Command "Alarme mode" to security sensors ( motion, door, humidity ) to report the 'Armed' variable state from vera devices
- Add Jeedom Action Command ("Alarme Armée" / "Alarme Libérée") to set / unset the Armed variable state of the vera device
- Do not change in jeedom the parent of a vera's proxy equipment if it already existed in jeedom at the time of synchronisation with vera

# 22/03/2022

- Dimmable RGB light

# 20/03/2022

- Door Sensor support
- Qubino pilot wire support

# 01/03/2022

- Window Cover support ( up down stop and slider )

# 26/02/2022

- Humidity Sensor support
- Display Watts & KWH if variable exists in Vera

# 22/02/2022

- Battery command not visible by default
- DimmableLight support (ON OFF)

# 21/02/2022

- Optimization du stockage des infos de la vera (scenes in configuration)
- add Firmware command to vera root equipments
- add Watts command to binary lights equipments
- add Battery command to battery powered equiments

# 20/02/2022

- Optimization du stockage des infos de la vera (devices in configuration)
- Utilisation de configuration au lieu de Command pour les gros json

# 19/02/2022

- support des equipement de type LightSensor de la Vera
- support des equipement de type MotionSensor de la Vera
- support des equipement de type TemperatureSensor de la Vera
- configuration des types generiques sur les commandes
- design configurable/extensible pour les autres types de devices
- Bugfixes

# 14/02/2022

- support des equipement de type TemperatureSensor de la Vera

# 13/02/2022

- Decouverte des pieces, des scenes, et des equipements de type BinaryLight de la Vera
- Commande On Off et Etat fonctionelles
- Lancement de scene fonctionel
- deamon utilisant proprement les appels lu_status et user_data selon les guidelines vera 

# 06/02/2022

- Version Initiale.