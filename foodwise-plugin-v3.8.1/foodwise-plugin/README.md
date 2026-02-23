# FoodWise Quiz Manager - Plugin WordPress

Plugin WordPress completo per la gestione di quiz sulla riduzione dello spreco alimentare con sistema di autenticazione tramite codici, import CSV e visualizzazione report.

## Descrizione

**FoodWise** è un sistema completo per la gestione di questionari sulla valutazione dello spreco alimentare domestico. Il plugin offre due tipologie di quiz (valutazione con slider e KAP) con un sistema di accesso semplificato tramite codici univoci.

## Caratteristiche Principali

### Sistema di Autenticazione
- Accesso tramite codici univoci (senza login tradizionale)
- Due tipologie di accesso: **Quiz** e **Viewer**
- Gestione sessioni sicura con cookie persistenti

### Quiz Disponibili

#### Quiz 1 - Valutazione dello Spreco Domestico
- Slider touch-friendly per dispositivi mobile
- Range valori: 0-100 con primo decimale
- Visualizzazione: "Niente" → "Tutto"
- Una domanda per ogni categoria

#### Quiz 2 - KAP (Knowledge, Attitude, Practice)
- Domande a risposta multipla
- Domande Vero/Falso
- Domande Sì/No

### Gestione Anagrafica
- Import massivo da file CSV
- Campi standard: Test_Name, Day, Month, Year, Section_Name, Panelist_Code, ecc.
- Campi motivazionali dinamici salvati in JSON
- Export dati in formato CSV

### Pannello Amministrazione

#### Dashboard
- Statistiche generali (utenti, categorie, submissions)
- Grafici compliance per quiz
- Monitoraggio completamento quiz

#### Gestione Utenti
- Visualizzazione completa anagrafica
- Filtri e ricerca avanzata
- Dettaglio compliance per utente
- Generazione batch di codici accesso

#### Gestione Categorie
- CRUD completo per categorie quiz
- Campi: Codice, Nome, Blinding Code, Simple Type
- Import/Export CSV

#### Impostazioni Quiz
- Attivazione/Disattivazione quiz
- Controllo visibilità per utenti

#### Import CSV
- Upload file CSV con preview
- Validazione automatica dati
- Storico upload con log

#### Report
- Visualizzazione submissions
- Export dati in CSV
- Statistiche dettagliate

### Dashboard Visualizzatore
- Accesso con codice viewer
- Visualizzazione statistiche generali
- Elenco submissions Quiz 1 e Quiz 2
- Dati in tempo reale

## Requisiti

- WordPress 5.8 o superiore
- PHP 7.4 o superiore
- MySQL 5.7+ / MariaDB 10.3+
- Browser moderni (Chrome, Firefox, Safari, Edge)

## Installazione

1. Scarica il plugin come file ZIP
2. Accedi al pannello WordPress → Plugin → Aggiungi nuovo
3. Clicca su "Carica plugin" e seleziona il file ZIP
4. Clicca su "Installa ora"
5. Attiva il plugin

### Installazione Manuale

1. Estrai il contenuto del file ZIP
2. Carica la cartella `foodwise-plugin` in `/wp-content/plugins/`
3. Attiva il plugin dal menu Plugin di WordPress

## Configurazione Iniziale

### 1. Creazione Categorie
Vai su **FoodWise → Categorie** e crea le categorie per il Quiz 1:
- Codice categoria (univoco)
- Nome categoria
- Blinding Code (opzionale)
- Simple Type (opzionale)

### 2. Import Utenti
Vai su **FoodWise → Import CSV** e carica il file CSV con l'anagrafica utenti.

**Formato CSV richiesto:**
```csv
Panelist_Code,Test_Name,Day,Month,Year,Section_Name,Section_Number
USER001,Test Spreco,15,1,2026,Sezione A,1
USER002,Test Spreco,15,1,2026,Sezione B,2
```

### 3. Generazione Codici
Vai su **FoodWise → Utenti** e clicca su "Genera Codici" per creare codici di accesso:
- Numero di codici
- Tipo di accesso (Quiz o Viewer)
- Prefisso opzionale

### 4. Attivazione Quiz
Vai su **FoodWise → Impostazioni Quiz** e attiva i quiz desiderati.

## Utilizzo

### Accesso Utente
1. Gli utenti visitano la pagina di accesso (creata automaticamente)
2. Inseriscono il codice ricevuto
3. Vengono reindirizzati alla dashboard appropriata

### Compilazione Quiz
- Gli utenti con accesso "Quiz" vedono i quiz attivi
- Ogni quiz può essere compilato una sola volta
- Le risposte vengono salvate nel database

### Visualizzazione Report
- Gli utenti con accesso "Viewer" possono visualizzare:
  - Statistiche generali
  - Elenco submissions
  - Dati compliance

## Shortcodes

Il plugin crea automaticamente le seguenti pagine con shortcode:

- `[foodwise_access]` - Form di accesso
- `[foodwise_quiz_1]` - Quiz 1 (Slider)
- `[foodwise_quiz_2]` - Quiz 2 (KAP)
- `[foodwise_viewer]` - Dashboard Visualizzatore

Puoi utilizzare questi shortcode in qualsiasi pagina o post.

## Struttura Database

### Tabelle Create

- `wp_foodwise_users` - Anagrafica utenti e codici
- `wp_foodwise_categories` - Categorie quiz
- `wp_foodwise_quiz_types` - Tipologie quiz
- `wp_foodwise_quiz_1_questions` - Domande Quiz 1
- `wp_foodwise_quiz_2_questions` - Domande Quiz 2
- `wp_foodwise_submissions` - Storico compilazioni
- `wp_foodwise_csv_uploads` - Log upload CSV

## Sicurezza

Il plugin implementa le seguenti misure di sicurezza:

- Sanitizzazione di tutti gli input utente
- Prepared statements per query database
- Nonce verification per form admin
- Capability checks per operazioni amministrative
- Validazione file upload
- Gestione sicura delle sessioni
- XSS prevention con escape output

## Performance

- Caching con WordPress Transient API
- Lazy loading dei dati
- Indici database ottimizzati
- Asset minificati
- Caricamento AJAX asincrono

## Compatibilità

- Responsive design mobile-first
- Touch-friendly per tablet e smartphone
- Compatibile con i temi WordPress standard
- Testato con i principali page builder

## Supporto

Per supporto tecnico o segnalazione bug:
- Email: lucarusso@pec.it
- Website: https://www.lambdasoft.it

## Changelog

### Versione 1.0.0 (2026-02-18)
- Release iniziale
- Sistema autenticazione tramite codici
- Quiz 1 con slider touch-friendly
- Quiz 2 KAP (multipla, vero/falso, sì/no)
- Import/Export CSV
- Dashboard amministrazione completa
- Report e statistiche
- Dashboard visualizzatore

## Crediti

Sviluppato da **LambdaSoft di Russo Luca**
- Website: https://www.lambdasoft.it
- P.IVA: 06044290481

## Licenza

Questo plugin è rilasciato sotto licenza GPL-2.0+
