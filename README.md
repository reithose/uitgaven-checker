# Uitgaven checker
PHP script genereert op basis van de ING Bank csv export en een JSON configuratie bestand een eenvoudig financieel overzicht.


## Configuratie
In het JSON bevat drie velden
- Naam van een uitgaven/inkomsten post
- Veld in het csv bestand waaraan de post herkend kan worden
- Waarde die het veld in het veld moet hebben om voor de betreffende post te gelden.

Als de waarde voorkomt in het veld van het CSV bestand, wordt het bedrag bij de post opgeteld of afgetrokken. Het is mogelijk om aan 1 post meerdere velden en waarden te hangen. De naam van de post wordt dan meerdere keren gebruikt.

Je kan meerdere JSON bestanden aanmaken in de configs map om meerdere overzichten te maken. In de map configs staat een voorbeeld.
