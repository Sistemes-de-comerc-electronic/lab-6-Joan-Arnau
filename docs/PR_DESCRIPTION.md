Títol
-----
Afegir Stripe Checkout + webhook i correu de "gràcies"

Resum
-----
Aquesta branca (feature/stripe-payment) afegeix una integració bàsica amb Stripe Checkout que permet cobrar un import fix de 20€ i, quan Stripe confirma el pagament (checkout.session.completed), el servidor envia automàticament un correu de "gràcies" mitjançant Symfony Mailer.

Canvis principals
-----------------
- src/Service/StripeService.php: servei per crear Checkout Sessions i validar webhooks
- src/Controller/PaymentController.php: endpoints
  - POST /payment/create-checkout-session
  - POST /payment/webhook (valida i envia correu a checkout.session.completed)
  - GET /payment/checkout (pàgina amb botó Pagar 20€)
  - GET /payment/success (pàgina success)
- templates/payment/checkout.html.twig: pàgina frontend amb Stripe.js
- .env.example: placeholders per CLAUS (NO commitejar .env.local)
- docs/STRIPE-SETUP.md: instruccions de prova i depuració
- composer.json: afegida dependència stripe/stripe-php (composer.lock no actualitzat)

Notes importants i tasques pendents
-----------------------------------
- composer.lock no inclou stripe a causa de conflictes de plataforma (ext-mbstring / path repo local). Per fer proves locals completes, executar localment:
  1) composer update stripe/stripe-php --with-dependencies
  2) composer install
  (assegurar que ext-mbstring està instal·lat i la vostra versió de PHP és compatible)

- Afegir a .env.local (no commitejar):
  STRIPE_SECRET_KEY, STRIPE_PUBLISHABLE_KEY, STRIPE_WEBHOOK_SECRET, MAILER_DSN

Proves recomanades
------------------
1) Configurar .env.local amb claus de test i MAILER_DSN (Mailtrap o Mailhog).
2) composer install (després d'actualitzar localment el lock si cal).
3) symfony server:start
4) stripe listen --forward-to localhost:8000/payment/webhook
5) Obrir http://localhost:8000/payment/checkout i pagar amb targeta de test 4242 4242 4242 4242
6) Confirmar que arriba el correu de "Gràcies" i que el webhook processa l'event correctament.

Notes per a la revisió
----------------------
- El webhook valida la firma amb STRIPE_WEBHOOK_SECRET; si proveu localment feu servir Stripe CLI (`stripe listen`) per forwardear events.
- L'enviament de correu es fa des del webhook i no des de la pàgina success, per fiabilitat.

Enllaç per crear PR manualment
-----------------------------
https://github.com/Sistemes-de-comerc-electronic/lab-6-Joan-Arnau/pull/new/feature/stripe-payment

Autor
-----
Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>
