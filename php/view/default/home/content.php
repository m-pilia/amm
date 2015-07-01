<h2>Resource Booking Application - AMM Class Final Project</h2>
<div class="home-content">

    <p>
        <strong>Resource Booking Application</strong> è una applicazione web
        pensata per gestire la prenotazione di risorse condivise in un ambiente
        lavorativo (e.g. proiettori, sale conferenze o riunioni, aule etc.).
    </p>

    <h3>Utenti</h3>
    <p>
        Gli utenti possono registrarsi tramite
        l'<a href="registration">apposito form</a>, inserendo una serie di dati
        associati all'account e caricando una propria immagine personale
        (altrimenti una immagine predefinita viene utilizzata). Dopo la
        registrazione gli utenti possono eseguire il <a href="login">login</a>
        con il bottone in alto a destra nell'header. Esiste una funzionalità
        di reset della password via e-mail, che funziona solo
        se il server web è configurato per l'invio di posta e sulla macchina
        host è installato un server SMTP o un demone di posta funzionante.
        La password viene effettivamente resettata solo quando l'utente riceve
        la mail con la richiesta di reset e apre il link di reset (che provoca
        l'invio di una nuova password al suo indirizzo e-mail).
    </p>
    <p>
        I nuovi utenti registrati hanno
        privilegi ridotti (ruolo utente) e possono:
    </p>
    <ul>
        <li>
            vedere il calendario;
        </li>
        <li>
            creare nuovi eventi;
        </li>
        <li>
            modificare o cancellare i propri eventi che non
            siano già iniziati.
        </li>
    </ul>

    <h3>Amministratori</h3>
    <p>
        Gli utenti con il ruolo privilegiato di amministratore, oltre a
        disporre di tutte le funzionalità del ruolo utente, possono:
    </p>
    <ul>
        <li>
            modificare o cancellare gli eventi di chiunque, non solo i
            propri;
        </li>
        <li>
            assegnare il ruolo di amministratori ad altri utenti o
            rimuoverlo ad altri amministratori (non possono rimuoverlo
            alla propria utenza);
        </li>
        <li>
            cancellare le utenze altrui (non la propria); quando si
            cancella una utenza, tutti gli eventi ad essa associati
            vengono cancellati;
        </li>
        <li>
            creare nuove risorse prenotabili, rinominare o cancellare
            quelle esistenti; quando si cancella una risorsa, tutti
            gli eventi ad essa associati vengono cancellati).
        </li>
    </ul>

    <h3>Utilizzo</h3>
    <p>
        Nella homepage di ogni utente vengono visualizzati i suoi eventi
        prenotati per i prossimi sette giorni. Tramite la barra
        laterale è possibile aprire le altre pagine, che consentono
        di accedere alle varie funzionalità. Le principali sono le seguenti:
    </p>
    <ul>
        <li>
            calendario: consente di vedere gli eventi di tutti gli utenti
            e scegliere visualmente un orario per le prenotazioni; aprendo
            un evento, con il click del mouse, viene visualizzata una pagina
            con tutti i dettagli, dalla quale è anche possibile cancellare
            l'evento;
        </li>
        <li>
            crea evento: apre la pagina per la creazione di un evento;
        </li>
        <li>
            impostazioni: consente di cambiare la propria password.
        </li>
    </ul>

    <p>
        Oltre alle precedenti pagine, accessibili a tutti gli utenti, gli
        amministratori hanno accesso ad altre pagine che forniscono alcune
        funzionalità aggiuntive:
    </p>
    <ul>
        <li>
            gestione risorse: permette di aggiungere nuove risorse, rinominare
            e cancellare le risorse esistenti;
        </li>
        <li>
            gestione utenti: permette di abilitare altri utenti come
            amministratori, rimuovere l'abilitazione ad altri amministratori
            e cancellare altre utenze.
        </li>
    </ul>

    <h3>Requisiti</h3>
    <ul>
        <li>
            Utilizzo di HTML 5 e CSS 3;
        </li>
        <li>
            programmazione server side in PHP >= 5.5;
        </li>
        <li>
            dati permanenti memorizzati su database MySQL tramite
            l'API <tt>mysqli</tt>;
        </li>
        <li>
            applicazione strutturata con il pattern MVC;
        </li>
        <li>
            due ruoli implementati (utente, classe <tt>User</tt>, e
            amministratore, classe <tt>Admin</tt>);
        </li>
        <li>
            utilizzo di transazioni sul database:
            <ul>
                <li>
                    per la creazione di eventi (classe <tt>Event</tt>
                    metodo <tt>insertEvent()</tt>);
                </li>
                <li>
                    per l'aggiornamento delle risorse (classe <tt>Resource</tt>
                    metodo <tt>updateResources($add, $del, $upd)</tt>);
                </li>
                <li>
                    per la cancellazione degli utenti (classe <tt>User</tt>
                    metodo <tt>deleteUser()</tt>);
                </li>
            </ul>
        </li>
        <li>
            utilizzo di AJAX per la pre-validazione dei dati nel
            form di registrazione e nel form per il cambio della password
            (file <tt>js/registration_form_validation.js</tt>, funzione
            <tt>ajaxValidation(element)</tt>).
        </li>
    </ul>

    <h3>Credenziali di accesso</h3>
    <p>
        L'accesso all'applicazione avviene tramite il
        <a href="login">form di login</a>, raggiungibile anche tramite il
        bottone in alto a destra dell'header. Le credenziali di accesso sono
        le seguenti:
    </p>
    <ul>
        <li>
            Ruolo utente:
            <ul>
                <li>username: John</li>
                <li>password: password1A</li>
            </ul>
        </li>
        <li>
            Ruolo amministratore:
            <ul>
                <li>username: Cthulhu</li>
                <li>password: password1A</li>
            </ul>
        </li>
    </ul>

</div>
