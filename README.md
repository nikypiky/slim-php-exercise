# REST API za pomoci PHP slim frameworku.
Tento projekt demonstruje základní funkcionality REST API pro správu uživatelů v PHP. Uživatel může vytvořit účet a upravovat své údaje. Účty s admin oprávněním můžou číst, aktualizovat a mazat všechny účty.

## Endpointy:
- **/ (Úvodní stránka)**
  - HTTP metoda: GET
  - **Chování:**
    - Pokud je uživatel přihlášen: Přesměruje na /user-table se stavovým kódem 302.
    - Pokud není uživatel přihlášen: Vyrenderuje šablonu login-page se stavovým kódem 200.

- **/login (Zpracování přihlášení)**
  - HTTP metoda: POST
  - **Parametry**
	- username
	- password
  - **Chování:**
	- Zkontroluje, zda jsou uživatelské údaje v databázi.
	- Pokud jsou data nesprávná: Vyrendruje šablonu login-page s upozorněním o špatně zadaných datech a stavovým kódem 401.
	- Pokud nastala chyba v hledání v databázi vyrendruje šablonu login-page s upozorněním o server erroru a stavovým kódem 500.
	- Pokud jsou data správná: Přesměruje na /user-table se stavovým kódem 302.

- **/register-page (Registrační stránka)**
  - HTTP metoda: GET
  - **Chování:**
	- Vyrendruje šablonu register-page se stavovým kódem 200.

- **/register (Zpracování registrace)**
  - HTTP metoda: POST
  - **Parametry**
	- username
	- email
	- password
	- password_confirmation
  - **Chování:**
	- Zkontroluje, zda zadaná data jsou ve správném formátu, password a password_confirmation jsou identická a že username a email již nejsou v databázi uživatelů.
	- Pokud jsou data nesprávná: Vyrendruje šablonu register-page s upozorněním o špatně zadaných datech a stavovým kódem 401.
	- Pokud nastala chyba v hledání v databázi vyrendruje šablonu register-page s upozorněním o server erroru a stavovým kódem 500.
	- Pokud jsou data správná: Uživatel je vytvořený a je přesměrován na /user-table se stavovým kódem 302.

- **/user-table (Zobrazí tabulku uživatelů)**
  - HTTP metoda: GET
  - **Chování:**
	- Vyrendruje šablonu user-table se stavovým kódem 200, kde uživatel může vidět data na základě svého oprávnění.

- **/del-user (Mazání uživatelů)**
  - HTTP metoda: POST
  - **Parametry**
	- id
  - **Chování:**
	- Smaže uživatele se zadanou user id.
	- Pokud nastala chyba v hledání v databázi vyrendruje šablonu user-table s upozorněním o server erroru a stavovým kódem 500.
	- Pokud jsou data správná: Uživatel je smazán a je přesměrován na /user-table se stavovým kódem 302.

- **/edit-user-page (Zobrazí stránku úprav uživatelů)**
  - HTTP metoda: GET
  - **Chování:**
	- Vyrendruje šablonu edit-user-page se stavovým kódem 200, kde uživatel může upravovat data.

- **/edit-user (Úprava uživatelů)**
  - HTTP metoda: POST
  - **Parametry**
	- id
	- field
	- new_data
  - **Chování:**
	- Zkontroluje, zda jsou new_data ve správném formátu a že se neopakují  databázi uživatelů.
	- Upraví položku specifikovanou parametrem field na new_data uživateli se zadanou user id.
	- Pokud nastala chyba v databázi vyrendruje šablonu edit-user-page s upozorněním o server erroru a stavovým kódem 500.
	- Pokud jsou data správná: Uživatel je smazán a je přesměrován na /user-table se stavovým kódem 302.

## Struktura databáze:
- **Users**
	- id, username, email jsou specifikované jako UNIQUE.
	- id, username, email a password jsou specifikované jako NOT NULL.

		| Field    | Type         | Null | Key | Default | Extra          |
		|----------|--------------|------|-----|---------|----------------|
		| id       | int(11)      | NO   | PRI | NULL    | auto_increment |
		| username | varchar(255) | NO   | UNI | NULL    |                |
		| email    | varchar(255) | NO   | UNI | NULL    |                |
		| password | varchar(255) | NO   | UNI | NULL    |                |

