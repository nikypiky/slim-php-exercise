# REST API za pomoci PHP slim frameworku.
Tento projekt demonstruje základní funkcionality REST API pro správu uživatelů v PHP. Uživatel může vytvořit účet a upravovat své údaje. Účty s admin oprávněním můžou číst, aktualizovat a mazat všechny účty.

## Endpointy:
### / (Úvodní stránka)
- HTTP metoda: GET
- Chování:
- Pokud je uživatel přihlášen: Přesměruje na /user-table se stavovým kódem 302 Found.
- Pokud není uživatel přihlášen: Vyrenderuje šablonu login-page.
